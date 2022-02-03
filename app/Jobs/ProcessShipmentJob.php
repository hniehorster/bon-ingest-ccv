<?php

namespace App\Jobs;

use App\Classes\AuthenticationHelper;
use App\Classes\CarrierFinderHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Exceptions\ObjectDoesNotExistException;
use App\Transformers\Transformer;
use BonSDK\ApiClient\BonGID;
use BonSDK\ApiIngest\BonIngestAPI;
use BonSDK\Classes\BonSDKGID;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class ProcessShipmentJob extends Job implements ShouldQueue
{

    public $tries = 30;

    public $shipmentData;
    public $externalShipmentId;
    public $externalIdentifier;

    public function __construct(string $externalShipmentId, string $externalIdentifier, array $shipmentData = null)
    {
        $this->externalShipmentId    = $externalShipmentId;
        $this->externalIdentifier    = $externalIdentifier;
        $this->shipmentData          = $shipmentData;
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

            $transformedShipment = (new Transformer($apiCredentials->businessUUID, $this->shipmentData, $apiCredentials->defaults))->shipment->transform();

            $bonApi = new BonIngestAPI(env('BON_SERVER'), $apiCredentials->internalApiKey, $apiCredentials->internalApiSecret, $apiCredentials->language);

            $orderGID = (new BonSDKGID)->encode(env('PLATFORM_TEXT'), $apiCredentials->businessUUID, 'order', $transformedShipment['external_order_id']);

            $bonOrderCheck = $bonApi->orders->get(null, ['gid' => $orderGID]);

            if ($bonOrderCheck->meta->count > 0) {

                $transformedShipment['object_type'] = 'order';
                $transformedShipment['object_uuid'] = $bonOrderCheck->data[0]->uuid;

                $bonShipmentsCheck = $bonApi->shipments->get(null, ['gid' => $transformedShipment['gid']]);

                if ($bonShipmentsCheck->meta->count > 0) {
                    $bonShipment = $bonApi->shipments->update($bonShipmentsCheck->data[0]->uuid, $transformedShipment);
                } else {
                    $bonShipment = $bonApi->shipments->create($transformedShipment);
                }

                //Shipment Tracking
                $orderData = $webshopAppClient->orders->get($this->shipmentData['order']['resource']['id']);

                $bonShipmentTrackingCheck = $bonApi->shipmentTrackings->get(null, ['shipment_uuid' => $bonShipment->uuid]);

                //Let's find the carrier
                $carrierData = new CarrierFinderHelper();
                $carrierData = $carrierData->obtainCarrierDetails($this->shipmentData, $orderData);


                $shipmentTracking = [
                    'shipment_uuid'     => $bonShipment->uuid,
                    'tracking_code'     => $carrierData['tracking_code'],
                    'tracking_enabled'  => $carrierData['tracking_enabled'],
                    'carrier'           => $carrierData['carrier']
                ];

                if ($bonShipmentTrackingCheck->meta->count > 0) {

                    $bonShipmentTracking = $bonApi->shipmentTrackings->update($bonShipmentsCheck->data[0]->uuid, $transformedShipment);
                } else {
                    $shipmentTracking['status'] = 'NEW';
                    $bonShipmentTracking = $bonApi->shipmentTrackings->create($transformedShipment);
                }

                //Shipment Line Items
                $shipmentProducts = $webshopAppClient->shipmentsProducts->get($this->shipmentData->id, null, ['limit' => 250]);

                Log::info("External ShipmentProducts " . json_encode($shipmentProducts, JSON_PRETTY_PRINT));

                foreach($shipmentProducts as $shipmentProduct) {
                    $transformedShipmentProduct = (new Transformer($apiCredentials->businessUUID, $shipmentProduct, $apiCredentials->defaults))->shipmentProduct->transform();

                    $shipmentLineItemCheck = $bonApi->shipmentLineItems->get(null, ['external_id' => $transformedShipmentProduct['external_id']]);

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
            if ($e->getCode() == 429) {
                Queue::later(QueueHelperClass::getNearestTimeRoundedUp(), new ProcessShipmentJob($this->externalShipmentId, $this->externalIdentifier, $this->shipmentData));
            }else{
                //release back to the queue if failed
                $this->release(QueueHelperClass::getNearestTimeRoundedUp());
            }
        }
    }
}
