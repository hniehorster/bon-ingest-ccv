<?php

namespace App\Jobs;

use App\Classes\AuthenticationHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Transformers\Transformer;
use BonSDK\ApiIngest\BonIngestAPI;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;


class ProcessOrderJob extends Job implements ShouldQueue
{
    public $tries = 30;

    public $orderData;
    public $externalOrderId;
    public $externalIdentifier;
    public $queueName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $externalOrderId, string $externalIdentifier, array $orderData = null, string $queueName = null)
    {
        $this->externalOrderId    = $externalOrderId;
        $this->externalIdentifier = $externalIdentifier;
        $this->orderData          = $orderData;

        $this->queueName          = 'default';

        if(!is_null($queueName)) {
            $this->queueName = $queueName;
        }

    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {

        try {
            $apiCredentials = AuthenticationHelper::getAPICredentials($this->externalIdentifier);
            $webshopAppClient = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);

            if (is_null($this->orderData)) {
                $this->orderData = $webshopAppClient->orders->get($this->externalOrderId);
            }

            $transformedOrder = (new Transformer($apiCredentials->businessUUID, $this->orderData, $apiCredentials->defaults))->order->transform();

            //Check if Order Already Exists
            $bonApi = new BonIngestAPI(env('BON_SERVER'), $apiCredentials->internalApiKey, $apiCredentials->internalApiSecret, $apiCredentials->language);
            $bonOrderCheck = $bonApi->orders->get(null, ['gid' => $transformedOrder['gid']]);

            if ($bonOrderCheck->meta->count > 0) {
               //Update the order
                $bonOrder = $bonApi->orders->update($bonOrderCheck->data[0]->uuid, $transformedOrder);
            }else{
                $bonOrder = $bonApi->orders->create($transformedOrder);
            }

            // Let's add the OrderLineItems
            foreach($this->orderData['products']['resource']['embedded'] as $product) {

                $transformedLineItem = (new Transformer($apiCredentials->businessUUID, $product, $apiCredentials->defaults))->orderLineItem->transform();
                $transformedLineItem['order_uuid'] = $bonOrder->uuid;

                //Check if Line item already exists
                $bonLineItemCheck = $bonApi->orderLineItems->get(null, ['order_uuid' => $bonOrder->uuid, 'line_item_id' => $transformedLineItem['line_item_id']]);

                if($bonLineItemCheck->meta->count > 0) {

                    $bonLineItem = $bonApi->orderLineItems->update($bonLineItemCheck->data[0]->uuid, $transformedLineItem);

                }else{

                    $bonLineItem = $bonApi->orderLineItems->create($transformedLineItem);

                }

                $shopProduct = $webshopAppClient->products->get($transformedLineItem['product_id']);

                $transformedProduct = (new Transformer($apiCredentials->businessUUID, $shopProduct, $apiCredentials->defaults))->product->transform();

                if(!is_null($transformedProduct['image'])){
                    $bonLineItemImage = $bonApi->orderLineItemImages->create($bonLineItem->uuid, ['external_url' => $transformedProduct['image']]);
                }
            }

            $orderCreatedAt = new Carbon($this->orderData['createdAt']);

            //Only process the shipment if younger then 15 days old.
            if($orderCreatedAt->diff(Carbon::now())->days < 15){
                foreach($this->orderData['shipments']['resource']['embedded'] as $shipment) {
                    Queue::later(QueueHelperClass::getNearestTimeRoundedUp(), new ProcessShipmentJob($shipment['id'], $this->externalIdentifier, $shipment, $this->orderData), null, $this->queueName);
                }
            }

        }
        catch (Exception $e) {

            if ($e->getCode() == 429) {
                Queue::later(QueueHelperClass::getNearestTimeRoundedUp(), new ProcessOrderJob($this->externalOrderId, $this->externalIdentifier, $this->orderData), null, $this->queueName);
            }else{
                //release back to the queue if failed
                $this->release(QueueHelperClass::getNearestTimeRoundedUp());
            }
        }
    }
}
