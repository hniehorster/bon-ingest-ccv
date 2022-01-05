<?php

namespace App\Jobs;

use App\Classes\AuthenticationHelper;
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
                $bonShipmentTrackingCheck = $bonApi->shipmentTrackings->get(null, ['shipment_uuid' => $bonShipment->uuid]);

                $shipmentTracking = [
                    'shipment_uuid'     => $bonShipment->uuid,
                    'tracking_code'     => $transformedShipment['tracking_code'],
                    'tracking_enabled'  => '',
                    'carrier'           => '', //TODO find the carrier
                ];

                if ($bonShipmentTrackingCheck->meta->count > 0) {


                    $bonShipment = $bonApi->shipmentTrackings->update($bonShipmentsCheck->data[0]->uuid, $transformedShipment);
                } else {

                    $shipmentTracking['status'] = 'NEW';
                    $bonShipment = $bonApi->shipmentTrackings->create($transformedShipment);
                }

                //Shipment Line Items



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
