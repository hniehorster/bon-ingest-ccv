<?php

namespace App\Jobs\Webhooks\Orders;

use App\Classes\AuthenticationHelper;
use App\Classes\CarrierFinderHelper;
use App\Classes\CCVApi\CCVApi;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Jobs\Job;
use App\Models\Handshake;
use App\Transformers\Transformer;
use BonSDK\ApiIngest\BonIngestAPI;
use BonSDK\Classes\BonSDKGID;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OrderTrackAndTraceJob extends Job implements ShouldQueue
{
    public $tries = 100;

    public $orderData;
    public $externalOrderId;
    public $externalIdentifier;
    public $queueName;
    public $reRelease = false;

    public function __construct(string $externalOrderId, string $externalIdentifier, array $orderData = null)
    {
        $this->externalOrderId    = $externalOrderId;
        $this->externalIdentifier = $externalIdentifier;
        $this->orderData          = $orderData;
    }

    public function handle() {
        Log::info(' ---- STARTING ' . static::class . ' ON QUEUE ' . $this->queueName . ' ------- ');

        $apiUser        = Handshake::where('external_identifier', $this->externalIdentifier)->first();
        $bonApi         = new BonIngestAPI(env('BON_SERVER'), $apiUser->internal_api_key, $apiUser->internal_api_secret, $apiUser->language);

        $ccvClient      = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);
        $orderDetails   = $ccvClient->orders->get($this->externalOrderId);

        $orderGID    = (new BonSDKGID())->encode(env('PLATFORM_TEXT'), 'order', $apiUser->business_uuid, $this->externalOrderId)->getGID();
        $shipmentGID = (new BonSDKGID())->encode(env('PLATFORM_TEXT'), 'order', $apiUser->business_uuid, $this->externalOrderId)->getGID();

        $bonOrderCheck      = $bonApi->orders->get(null, ['gid' => $orderGID, 'business_uuid' => $apiUser->business_uuid]);

        if($bonOrderCheck->meta->count > 0) {

            $bonShipmentCheck   = $bonApi->shipments->get(null, ['object_type' => 'order', 'object_uuid' => $bonOrderCheck->data[0]->uuid]);

            if($bonShipmentCheck->meta->count > 0) {

                $bonShipmentTrackingCheck = $bonApi->shipmentTrackings->get(null, ['shipment_uuid' => $bonShipmentCheck->data[0]->uuid]);

                $trackingEnabled = false;

                if(!empty($orderDetails->track_and_trace_code) && !empty($orderDetails->track_and_trace_carrier)){
                    $trackingEnabled = true;
                }

                $bonShipmentTrackingData = [
                    'shipment_uuid'     => $bonShipmentCheck->data[0]->uuid,
                    'tracking_code'     => $orderDetails->track_and_trace_code,
                    'tracking_enabled'  => $trackingEnabled,
                    'carrier'           => CarrierFinderHelper::getBonCarrier($orderDetails->track_and_trace_carrier),
                    'shop_created_at'   => Carbon::now()->format('Y-m-d H:i:s')
                ];

                if ($bonShipmentTrackingCheck->meta->count == 0) {
                    $bonShipment = $bonApi->shipmentTrackings->update($bonShipmentTrackingCheck->data[0]->uuid, $bonShipmentTrackingData);
                } else {
                    $bonShipment = $bonApi->shipmentTrackings->create($bonShipmentTrackingData);
                }

            }else{
                Log::info('Shipment not found, we have to add the order first');
                $this->release(QueueHelperClass::getNearestTimeRoundedUp(5, true));
            }

        }else{
            Log::info('Order not found, we have to add the order first');
            $this->release(QueueHelperClass::getNearestTimeRoundedUp(5, true));
        }

        Log::info(' ---- ENDING ' . static::class . ' ON QUEUE ' . $this->queueName . ' ------- ');
    }
}

/**
 * {
"triggered_at": "2022-11-18T09:21:57Z",
"href": "https://bonapp1.ccvshop.nl/api/rest/v1/orders/340377975",
"id": 340377975,
"order_number": 25,
"ordernumber_prefix": null,
"ordernumber_full": "25",
"total_price": 616.67,
"track_and_trace_code": "12341234666",
"track_and_trace_carrier": "MyParcel",
"customer_email": "h.niehorster@hjalding.nl",
"customer_mobile": ""
}
 */
