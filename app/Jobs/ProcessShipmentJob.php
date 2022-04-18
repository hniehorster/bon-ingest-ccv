<?php

namespace App\Jobs;

use App\Classes\AuthenticationHelper;
use App\Classes\CarrierFinderHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Exceptions\ObjectDoesNotExistException;
use App\Transformers\Transformer;
use BonSDK\ApiIngest\BonIngestAPI;
use BonSDK\Classes\BonSDKGID;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Sentry\Util\JSON;

class ProcessShipmentJob extends Job implements ShouldQueue
{

    public $tries = 30;

    public $shipmentData;
    public $orderData;
    public $externalShipmentId;
    public $externalIdentifier;

    public function __construct(string $externalShipmentId, string $externalIdentifier, array $shipmentData = null, array $orderData = null)
    {
        $this->externalShipmentId    = $externalShipmentId;
        $this->externalIdentifier    = $externalIdentifier;
        $this->shipmentData          = $shipmentData;
        $this->orderData             = $orderData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('Processing shipment: ' . $this->externalShipmentId);

            $apiCredentials = AuthenticationHelper::getAPICredentials($this->externalIdentifier);

            $webshopAppClient = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);

            if (is_null($this->shipmentData)) {
                $this->shipmentData = $webshopAppClient->shipments->get($this->externalShipmentId);
            }

            if (is_null($this->orderData)) {
                $this->orderData = $webshopAppClient->orders->get($this->shipmentData['order']['resource']['id']);
            }

            Log::info('[BON - SHIPMENT] ' . json_encode($this->shipmentData, JSON_PRETTY_PRINT));

            $transformedShipment = (new Transformer($apiCredentials->businessUUID, $this->shipmentData, $apiCredentials->defaults))->shipment->transform();

            //Log::info('Transformed Shipment: ' . json_encode($transformedShipment, JSON_PRETTY_PRINT));

            $bonApi = new BonIngestAPI(env('BON_SERVER'), $apiCredentials->internalApiKey, $apiCredentials->internalApiSecret, $apiCredentials->language);

            $orderGID = (new BonSDKGID)->encode(env('PLATFORM_TEXT'), 'order', $apiCredentials->businessUUID, $transformedShipment['external_order_id']);

            //Log::info('GID: ' . $orderGID->getGID());

            $bonOrderCheck = $bonApi->orders->get(null, [ 'gid' => $orderGID ->getGID() ]);

            if ($bonOrderCheck->meta->count > 0) {

                $transformedShipment['object_type'] = 'order';
                $transformedShipment['object_uuid'] = $bonOrderCheck->data[0]->uuid;

                $bonShipmentsCheck = $bonApi->shipments->get(null, ['gid' => $transformedShipment['gid']]);

                if ($bonShipmentsCheck->meta->count > 0) {
                    $bonShipment = $bonApi->shipments->update($bonShipmentsCheck->data[0]->uuid, $transformedShipment);
                } else {
                    $bonShipment = $bonApi->shipments->create($transformedShipment);
                }

                $bonShipmentTrackingCheck = $bonApi->shipmentTrackings->get(null, ['shipment_uuid' => $bonShipment->uuid]);

                Log::info('BonShipmentTrackings: ' . json_encode($bonShipmentTrackingCheck, JSON_PRETTY_PRINT));

                //Let's find the carrier
                $carrierData = new CarrierFinderHelper();
                $carrierData = $carrierData->obtainCarrierDetails($this->shipmentData, $this->orderData);

                $shipmentTracking = [
                    'shipment_uuid'     => $bonShipment->uuid,
                    'tracking_code'     => $carrierData['tracking_code'],
                    'tracking_enabled'  => $carrierData['tracking_enabled'],
                    'carrier'           => $carrierData['carrier'],
                    'shop_created_at'   => Carbon::now()->format('Y-m-d H:i:s')
                ];

                if ($bonShipmentTrackingCheck->meta->count > 0) {

                    $bonShipmentTracking = $bonApi->shipmentTrackings->update($bonShipmentTrackingCheck->data[0]->uuid, $shipmentTracking);
                } else {
                    $shipmentTracking['status'] = 'NEW';
                    $bonShipmentTracking = $bonApi->shipmentTrackings->create($shipmentTracking);
                }

                Log::info('[BON] Shipment Tracking ' .  json_encode($bonShipmentTrackingCheck, JSON_PRETTY_PRINT));

                //Shipment Line Items
                $shipmentProducts = $webshopAppClient->shipmentsProducts->get($this->externalShipmentId, null, ['limit' => 250]);

                Log::info("External ShipmentProducts " . json_encode($shipmentProducts, JSON_PRETTY_PRINT));

                foreach($shipmentProducts as $shipmentProduct) {
                    $transformedShipmentProduct = (new Transformer($apiCredentials->businessUUID, $shipmentProduct, $apiCredentials->defaults))->shipmentProduct->transform();

                    $transformedShipmentProduct['shipment_uuid'] = $bonShipment->uuid;

                    $shipmentLineItemCheck = $bonApi->shipmentLineItems->get($bonShipmentsCheck->data[0]->uuid, null, ['external_id' => $transformedShipmentProduct['external_id']]);

                    Log::info("Shipment LineItems found: " . $shipmentLineItemCheck->meta->count);

                    if($shipmentLineItemCheck->meta->count > 0){

                        Log::info("Shipment LineItems UPDATED");
                        $bonApi->shipmentLineItems->update($bonShipmentsCheck->data[0]->uuid, $shipmentLineItemCheck->data[0]->uuid, $transformedShipmentProduct);

                    }else{

                        Log::info("Shipment LineItems CREATED");
                        $bonApi->shipmentLineItems->create($bonShipmentsCheck->data[0]->uuid, $transformedShipmentProduct);

                    }
                }
            }else{
                throw new ObjectDoesNotExistException();
            }


        }
        catch (Exception $e) {

            Log::info('ERROR');
            Log::info('File: ' . $e->getFile());
            Log::info('Line: ' . $e->getLine());
            Log::info('Code:'. $e->getCode());
            Log::info('Trace: ' . $e->getTraceAsString());

            if ($e->getCode() == 429) {
                Queue::later(QueueHelperClass::getNearestTimeRoundedUp(), new ProcessShipmentJob($this->externalShipmentId, $this->externalIdentifier, $this->shipmentData));
            }else{
                //release back to the queue if failed
                $this->release(QueueHelperClass::getNearestTimeRoundedUp());
            }
        }
    }
}
