<?php

namespace App\Jobs\Webhooks\Orders;

use App\Classes\CCVApi\CCVApi;
use App\Classes\QueueHelperClass;
use App\Jobs\Job;
use App\Models\Handshake;
use App\Transformers\Transformer;
use BonSDK\ApiIngest\BonIngestAPI;
use BonSDK\Classes\BonSDKGID;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class OrderCreatedJob extends Job implements ShouldQueue
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

        try {

            $apiUser = Handshake::where('external_identifier', $this->externalIdentifier)->first();

            $ccvClient = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);

            $orderDetails = $ccvClient->orders->get($this->externalOrderId);

            $transformedOrder = (new Transformer($apiUser->business_uuid, json_decode(json_encode($orderDetails), true), $apiUser->defaults))->order->transform();

            $transformedOrder['payment_status'] = $orderDetails->paid ? 'paid' : 'not_paid';
            $transformedOrder['shipment_status'] = 'not_shipped';

            if (in_array($orderDetails->status, [5, 6, 7])) {
                $transformedOrder['shipment_status'] = 'shipped';
            }

            $bonApi = new BonIngestAPI(env('BON_SERVER'), $apiUser->internal_api_key, $apiUser->internal_api_secret, $apiUser->language);

            $bonOrderCheck = $bonApi->orders->get(null, ['gid' => $transformedOrder['gid'], 'business_uuid' => $apiUser->business_uuid]);
            Log::info('[BONAPI] GET order ' . $transformedOrder['gid']);

            if ($bonOrderCheck->meta->count > 0) {
                //Update the order

                Log::info('Order Found');

                $bonOrder = $bonApi->orders->update($bonOrderCheck->data[0]->uuid, $transformedOrder);

                Log::info('Order Updated');
            } else {

                Log::info('Order Found');

                $bonOrder = $bonApi->orders->create($transformedOrder);

                Log::info('Order Created');

            }

            $orderRowsPages = true;
            $orderRowPageNumber = 0;

            //Let's create the order Line Items
            do {

                Log::info('Lets do the order pages');

                try {
                    $ccvClient->setPageNumber($orderRowPageNumber);
                    $orderRows = $ccvClient->orderRows->get($this->externalOrderId,);

                    //Let's create the row items
                    foreach ($orderRows->items as $orderRowDetails) {

                        $transformedOrderRow = (new Transformer($bonOrder->business_uuid, json_decode(json_encode($orderRowDetails), true), $apiUser->defaults))->orderRow->transform();
                        $transformedOrderRow['order_uuid'] = $bonOrder->uuid;

                        Log::info('Working on ' . $transformedOrderRow['line_item_id']);

                        $countAttributes = count($orderRowDetails->attributes);
                        $variantTitle = "";
                        $variantId = null;

                        if ($countAttributes > 0) {
                            //There are variant titles
                            foreach ($orderRowDetails->attributes as $attribute) {
                                $variantTitle .= $attribute->option_name . ' ' . $attribute->value_name;
                                $variantId = $attribute->id;
                            }
                        }

                        $transformedOrderRow['variant_title'] = $variantTitle;
                        $transformedOrderRow['variant_id'] = $variantId;

                        if (!is_null($variantId)) {
                            $transformedOrderRow['variant_gid'] = (new BonSDKGID())->encode(env('PLATFORM_TEXT'), 'variant', $bonOrder->business_uuid, $variantId)->getGID();
                        } else {
                            $transformedOrderRow['variant_gid'] = null;
                        }

                        $bonLineItemCheck = $bonApi->orderLineItems->get(null, ['order_uuid' => $bonOrder->uuid, 'line_item_id' => $transformedOrderRow['line_item_id'], 'business_uuid' => $apiUser->business_uuid]);

                        if ($bonLineItemCheck->meta->count > 0) {

                            $bonLineItem = $bonApi->orderLineItems->update($bonLineItemCheck->data[0]->uuid, $transformedOrderRow);

                            Log::info($transformedOrderRow['line_item_id'] . ' UPDATED');
                        } else {

                            $bonLineItem = $bonApi->orderLineItems->create($transformedOrderRow);

                            Log::info($transformedOrderRow['line_item_id'] . 'CREATED');
                        }

                        $orderCreatedAt = new Carbon($transformedOrder['shop_created_at']);

                        if ($orderCreatedAt->diff(Carbon::now())->days < 15) {

                            //Let's add the product image
                            try{
                                $orderRowImages = $ccvClient->productPhotos->get($transformedOrderRow['product_id']);

                                foreach ($orderRowImages->items as $productPhotos) {

                                    $productPhotos = (new Transformer($bonOrder->business_uuid, json_decode(json_encode($productPhotos), true), $apiUser->defaults))->productPhoto->transform();

                                    $bonLineItemImage = $bonApi->orderLineItemImages->create($bonLineItem->uuid, ['external_url' => $productPhotos['image']]);

                                    Log::info($transformedOrderRow['id'] . ' IMAGE GRABBED');

                                }
                            } catch (Exception $e) {
                                if ($e->getCode() == 429) {
                                    Log::info('[CCVAPI] Rate Limit hit for order ' . $this->externalOrderId . ' with store ' . $apiUser->businessUUID);
                                    $this->release(QueueHelperClass::getNearestTimeRoundedUp(1, true));
                                    Log::info('RELEASED BACK TO QUEUE');
                                }else{
                                    //release back to the queue if failed
                                    Log::info('Releasing back to queue for other reason');
                                    Log::info('Reason: ' . $e->getMessage() . ' online ' . $e->getLine());
                                    Log::info('Trace: ' . $e->getTraceAsString());
                                    $this->release(QueueHelperClass::getNearestTimeRoundedUp(1, true));
                                    Log::info('RELEASED BACK TO QUEUE');
                                }
                            }
                        }
                    }

                } catch (Exception $e) {

                    if ($e->getCode() == 429) {
                        Log::info('[CCVAPI] Rate Limit hit for order ' . $this->externalOrderId . ' with store ' . $apiUser->businessUUID);
                        $this->release(QueueHelperClass::getNearestTimeRoundedUp(1, true));
                        Log::info('RELEASED BACK TO QUEUE');
                    }else{
                        //release back to the queue if failed
                        Log::info('Releasing back to queue for other reason ' . $e->getMessage());
                        Log::info('Reason: ' . $e->getMessage() . ' file ' . $e->getFile() . ' online ' . $e->getLine());
                        Log::info('Trace ' . $e->getTraceAsString());
                        $this->release(QueueHelperClass::getNearestTimeRoundedUp(1, true));
                        Log::info('RELEASED BACK TO QUEUE');
                    }
                }

                Log::info('Let\'s do the next page: ' .$ccvClient->hasNextPage());
                $orderRowPageNumber++;

            } while ($ccvClient->hasNextPage());

            if($transformedOrder['shipment_status'] == 'shipped') {

                $shopCreatedAt = Carbon::parse($transformedOrder['shop_created_at']);

                if($shopCreatedAt->diff(Carbon::now())->days < 15){
                    //Post Shipment Request

                    $transformedOrder['previous_status'] = 1;
                    $transformedOrder['status'] = 5;
                    Queue::later(QueueHelperClass::getNearestTimeRoundedUp(5, true), new OrderStatusChangedJob($this->externalOrderId, $this->externalIdentifier, $transformedOrder), null, $this->queueName);
                }
            }

        } catch (Exception $e) {

            Log::info(' ---- JOB FAILED ------ ');
            Log::info( ' Message: ' . $e->getMessage());
            Log::info( ' File: ' . $e->getFile());
            Log::info( ' Trace: ' . $e->getTraceAsString());
            Log::info(' ---- FAILED JOB ------ ');

            if ($e->getCode() == 429) {
                Log::info('[CCVAPI] Rate Limit hit for order ' . $this->externalOrderId . ' with store ' . $apiUser->businessUUID);
                $this->release(QueueHelperClass::getNearestTimeRoundedUp(1, true));
                Log::info('RELEASED BACK TO QUEUE');
            }else{
                //release back to the queue if failed
                Log::info('Releasing back to queue for other reason');
                $this->release(QueueHelperClass::getNearestTimeRoundedUp(1, true));
                Log::info('RELEASED BACK TO QUEUE');
            }
        }

        Log::info(' ---- ENDING ' . static::class . ' ON QUEUE ' . $this->queueName . ' ------- ');
    }

    /*****
     * SAMPLE PAYLOAD
    {
    "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orders\/339323562\/",
    "id": 339323562,
    "ordernumber_prefix": null,
    "ordernumber": 1,
    "ordernumber_full": "1",
    "invoicenumber": null,
    "transaction_id": null,
    "create_date": "2022-11-07T21:51:00Z",
    "deliver_method": "shipping",
    "deliver_date": null,
    "take_out_window": {
    "start": null,
    "end": null
    },
    "is_platform_sale": false,
    "orderedinlng": "nl",
    "status": 1,
    "is_completed": true,
    "paid": true,
    "safety_deposit_returned": false,
    "paymethod_id": 3,
    "paymethod": "BankTransfer",
    "paymethod_label": "Vooraf overmaken",
    "taxes_included": true,
    "order_row_taxes_included": true,
    "shipping_taxes_included": true,
    "shipping_tax_percentage": 21,
    "is_intra_community_order": false,
    "total_orderrow_price": 590,
    "total_shipping": 5,
    "total_discounts": 0,
    "total_price": 590,
    "currency": "EUR",
    "total_tax": 102.4,
    "total_weight": 0,
    "extra_payment_option": "",
    "extra_payment_option_price": 0,
    "extra_payment_option_no_sentprice": false,
    "extra_payment_option_pay_on_pickup": false,
    "extra_price": 0,
    "paymethod_costs": 0,
    "credit_point_discount": 0,
    "extra_costs": 0,
    "extra_costs_description": "",
    "track_and_trace_code": "",
    "track_and_trace_carrier": null,
    "track_and_trace_deeplink": "",
    "reservationnumber": "",
    "delivery_option": null,
    "user": {
    "id": "24464274",
    "discount_percentage": 0,
    "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/users\/24464274"
    },
    "discountcoupon": {
    "code": "",
    "discount": 0,
    "type": "",
    "enddate": null,
    "onetimeuse": false,
    "minimumprice": 0,
    "givesfreeshipping": false
    },
    "customer": {
    "billingaddress": {
    "gender": null,
    "initials": "",
    "first_name": "Hjalte",
    "last_name": "Niehorster",
    "full_name": "Hjalte Niehorster",
    "company": "",
    "address_line_1": "Lepelstraat 3",
    "address_line_2": "",
    "street": "Lepelstraat",
    "housenumber": 3,
    "housenumber_suffix": "",
    "zipcode": "1018xk",
    "city": "Amsterdam",
    "province": "",
    "telephone": "620679767",
    "fax": "",
    "mobile": "",
    "comment": "",
    "country": "Nederland",
    "country_code": "NL"
    },
    "deliveryaddress": {
    "gender": null,
    "full_name": "",
    "initials": "",
    "company": "",
    "address_line_1": "",
    "address_line_2": "",
    "street": "",
    "housenumber": null,
    "housenumber_suffix": "",
    "zipcode": "",
    "city": "",
    "province": "",
    "telephone": "",
    "mobile": "",
    "comment": "",
    "country": "",
    "country_code": null
    },
    "email_invoice": "",
    "email_invoice_company": "",
    "email_invoice_union": "",
    "email": "h.niehorster@hjalding.nl",
    "customertype": "b2c",
    "bankaccount": "",
    "iban": "",
    "bic": "",
    "bank": "",
    "tenname": "",
    "bankname": "",
    "btw": "",
    "kvk": "",
    "reference": "",
    "reservationnumber": "",
    "income": "",
    "branche": "",
    "branch": "",
    "website": "",
    "clubcity": "",
    "clubcomment": "",
    "clubname": "",
    "beurs": "",
    "fair": "",
    "stand": "",
    "booth": "",
    "carbrand": "",
    "cartype": "",
    "carweight": "",
    "birthdate": null,
    "birthplace": "",
    "birthtime": null,
    "cardnumber": "",
    "findus": "",
    "ssnnumber": "",
    "zzppass": "",
    "costcentre": ""
    },
    "pickup_address": null,
    "packing_slip_deeplink": "https:\/\/bonapp1.ccvshop.nl\/onderhoud\/AdminItems\/StandaloneObjects\/PackingSlip.php?OrderId=def5020007135ee9fa092760494aa13554e0e5236083a1a3aa6629054e4d56093f6d973f33a962206959649dc56a1fef36f4384267d5273867d3aabbe8ac4e436f643b9bdaed33fe4af9e90f2c68ea67999607da0bc8fb6df33bddb63e989f34671ef3c3",
    "orderrows": {
    "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orders\/339323562\/orderrows\/"
    },
    "ordernotes": {
    "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orders\/339323562\/ordernotes\/"
    },
    "ordermessages": {
    "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orders\/339323562\/ordermessages\/"
    },
    "ordernotifications": {
    "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orders\/339323562\/ordernotifications\/"
    },
    "orderaffiliatenetworks": {
    "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orders\/339323562\/orderaffiliatenetworks\/"
    },
    "orderlabels": {
    "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orders\/339323562\/orderlabels\/"
    },
    "invoices": {
    "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orders\/339323562\/invoices\/"
    }
    }
     */
}
