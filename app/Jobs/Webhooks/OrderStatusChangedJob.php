<?php

namespace App\Jobs\Webhooks;

use App\Classes\AuthenticationHelper;
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

class OrderStatusChangedJob extends Job implements ShouldQueue
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

        $bonOrderCheck = $bonApi->orders->get(null, ['gid' => $orderGID]);
        Log::info('[BONAPI] GET order ' . $orderGID);

        if ($bonOrderCheck->meta->count > 0) {

            if($this->orderData['status'] == 5) {
                //Something changed and the order seems to be shipped
                //check for a shipment
                $bonShipmentCheck = $bonApi->shipments->get(null, ['gid' => $shipmentGID]);
                if ($bonShipmentCheck->meta->count == 0) {
                    //Create the shipment

                    $ccvClient = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);
                    $orderDetails = $ccvClient->orders->get($this->externalOrderId);

                    $transformedShipment = (new Transformer($apiUser->business_uuid, json_decode(json_encode($orderDetails), true), $apiUser->defaults))->shipment->transform();

                    $transformedShipment['object_type'] = 'order';
                    $transformedShipment['object_uuid'] = $bonOrderCheck->data[0]->uuid;

                    $bonShipmentsCheck = $bonApi->shipments->get(null, ['gid' => $transformedShipment['gid']]);

                    if ($bonShipmentsCheck->meta->count > 0) {
                        $bonShipment = $bonApi->shipments->update($bonShipmentsCheck->data[0]->uuid, $transformedShipment);
                    } else {
                        $bonShipment = $bonApi->shipments->create($transformedShipment);
                    }

                    //Check if line items have been shipped.

                    $bonOrderLineItems = $bonApi->orderLineItems->get(null, ['business_uuid' => $apiUser->business_uuid, 'order_uuid' => $bonOrderCheck->data[0]->uuid ]);

                    if ($bonOrderLineItems->meta->count > 0) {

                        foreach($bonOrderLineItems->data as $orderLineItem){

                            //check if there is a corresponding shipment item
                            $bonShipmentLineItem = $bonApi->shipmentLineItems->get($bonShipment->uuid);

                            if ($bonShipmentLineItem->meta->count == 0) {
                                //Shipment Line Item doens't exist, create it.

                                $bonShipmentLineItemData = [
                                    'shipment_uuid' => $bonShipment->uuid,
                                    'business_uuid' => $apiUser->business_uuid,
                                    'variant_id'    => $orderLineItem->variant_id,
                                    'variant_gid'   => $orderLineItem->variant_gid,
                                    'variant_title' => $orderLineItem->variant_title,
                                    'product_id'    => $orderLineItem->product_id,
                                    'product_gid'   => $orderLineItem->product_gid,
                                    'product_title' => $orderLineItem->product_title,
                                    'ean'           => $orderLineItem->ean,
                                    'sku'           => $orderLineItem->sku,
                                    'article_code'  => $orderLineItem->article_code,
                                    'quantity'      => $orderLineItem->quantity
                                ];

                                $bonShipment = $bonApi->shipmentLineItem->create($bonShipmentLineItemData);
                            }
                        }
                    }

                    //check for shipment tracking
                }else{
                    $bonShipment = $bonShipmentCheck->data[0];
                }

                //Check for shipment Tracking
                $bonShipmentTrackingCheck = $bonApi->shipmentTrackings->get(null, ['shipment_uuid' => $bonShipment->uuid]);

                $trackingEnabled = false;

                if(!empty($orderDetails['track_and_trace_code']) && !empty($orderDetails['track_and_trace_carrier'])){
                    $trackingEnabled = true;
                }

                $bonShipmentTrackingData = [
                    'shipment_uuid'     => $bonShipment->uuid,
                    'tracking_code'     => $orderDetails['track_and_trace_code'],
                    'tracking_enabled'  => $trackingEnabled,
                    'carrier'           => $orderDetails['track_and_trace_carrier'],
                    'shop_created_at'   => Carbon::now()->format('Y-m-d H:i:s')
                ];

                if ($bonShipmentTrackingCheck->meta->count == 0) {
                    $bonShipment = $bonApi->shipmentTrackings->update($bonShipmentTrackingCheck->data[0]->uuid, $bonShipmentTrackingData);
                } else {
                    $bonShipment = $bonApi->shipmentTrackings->create($bonShipmentTrackingData);
                }
            }

        } else{
            Log::info('Order not found, we have to add the order first');
            $this->release(QueueHelperClass::getNearestTimeRoundedUp(5, true));
        }

        Log::info(' ---- ENDING ' . static::class . ' ON QUEUE ' . $this->queueName . ' ------- ');
    }
}

/***
 * DO NOT REMOVE
 * Documentation Notes
 *
 * - CCV doesn't have the principle of seperated orders and shipmetns
 * - So we are mimicing this here, adding an shipment has the same external_id as an order
 */

/* Webhook Payload
{
  "triggered_at": "2022-11-09T16:17:30Z",
  "href": "https://bonapp1.ccvshop.nl/api/rest/v1/orders/339493812",
  "id": 339493812,
  "order_number": 3,
  "ordernumber_prefix": null,
  "ordernumber_full": "3",
  "total_price": 1175,
  "previous_status": 1,
  "status": 5,
  "customer_email": "h.niehorster@hjalding.nl",
  "customer_mobile": ""
}
 */

/* Status Explanations
1. New
2. In process
3. Wait for manufacturer
4. Wait for payment
5. Sent
6. Delivered
7. Completed
8. Cancelled
9. Wait for supplier
10. Is being packaged
11. Ready to be collected
12. Is being assembled
13. Backorder
14. Reserved
*/
