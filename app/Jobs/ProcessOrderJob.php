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
    public $tries = 100;

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
        Log::info(' ---- STARTING JOB ON QUEUE ' . $this->queueName . ' ------- ');
        $apiCredentials = AuthenticationHelper::getAPICredentials($this->externalIdentifier);

        try {
            $webshopAppClient = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);

            if (is_null($this->orderData)) {
                $this->orderData = $webshopAppClient->orders->get($this->externalOrderId);
                Log::info('[LSAPI] GET order ' . $this->externalOrderId);

            }

            $transformedOrder = (new Transformer($apiCredentials->businessUUID, $this->orderData, $apiCredentials->defaults))->order->transform();

            //Check if Order Already Exists
            $bonApi = new BonIngestAPI(env('BON_SERVER'), $apiCredentials->internalApiKey, $apiCredentials->internalApiSecret, $apiCredentials->language);

            $bonOrderCheck = $bonApi->orders->get(null, ['gid' => $transformedOrder['gid']]);
            Log::info('[BONAPI] GET order ' . $transformedOrder['gid']);

            if ($bonOrderCheck->meta->count > 0) {
               //Update the order
                $bonOrder = $bonApi->orders->update($bonOrderCheck->data[0]->uuid, $transformedOrder);
                Log::info('[BONAPI] UPDATE order ' . $transformedOrder['gid'] . ' ' . $bonOrderCheck->data[0]->uuid);
            }else{
                $bonOrder = $bonApi->orders->create($transformedOrder);
                Log::info('[BONAPI] CREATE order ' . $transformedOrder['gid']);

            }

            // Let's add the OrderLineItems
            foreach($this->orderData['products']['resource']['embedded'] as $product) {

                $transformedLineItem = (new Transformer($apiCredentials->businessUUID, $product, $apiCredentials->defaults))->orderLineItem->transform();
                $transformedLineItem['order_uuid'] = $bonOrder->uuid;

                //Check if Line item already exists
                $bonLineItemCheck = $bonApi->orderLineItems->get(null, ['order_uuid' => $bonOrder->uuid, 'line_item_id' => $transformedLineItem['line_item_id']]);
                Log::info('[BONAPI] GET orderLineItems ' . $transformedOrder['gid']);

                if($bonLineItemCheck->meta->count > 0) {

                    $bonLineItem = $bonApi->orderLineItems->update($bonLineItemCheck->data[0]->uuid, $transformedLineItem);
                    Log::info('[BONAPI] UPDATE orderLineItems ' . $transformedOrder['gid']);

                }else{

                    $bonLineItem = $bonApi->orderLineItems->create($transformedLineItem);
                    Log::info('[BONAPI] CREATE orderLineItems ' . $transformedOrder['gid']);

                }

                try{
                    $shopProduct = $webshopAppClient->products->get($transformedLineItem['product_id']);

                    $transformedProduct = (new Transformer($apiCredentials->businessUUID, $shopProduct, $apiCredentials->defaults))->product->transform();

                    if(!is_null($transformedProduct['image'])){

                        $bonLineItemImage = $bonApi->orderLineItemImages->create($bonLineItem->uuid, ['external_url' => $transformedProduct['image']]);
                        Log::info('[BONAPI] CREATE orderLineItemImage ' . $bonLineItem->uuid);
                        
                    }
                } catch (Exception $e) {

                    if($e->getCode() == 404){
                        Log::info('[LSAPI] Product not found, but process order');

                    }elseif ($e->getCode() == 429) {

                        Log::info('[LSAPI] Rate Limit hit for order ' . $this->externalOrderId . ' with store ' . $apiCredentials->businessUUID);
                        //Queue::later(QueueHelperClass::getNearestTimeRoundedUp(), new ProcessOrderJob($this->externalOrderId, $this->externalIdentifier, $this->orderData), null, $this->queueName);
                        $this->release(QueueHelperClass::getNearestTimeRoundedUp(5, true));

                    }else{

                        $this->release(QueueHelperClass::getNearestTimeRoundedUp(5, true));

                    }
                }
            }

            $orderCreatedAt = new Carbon($this->orderData['createdAt']);

            /*
            //Only process the shipment if younger then 15 days old.
            if($orderCreatedAt->diff(Carbon::now())->days < 15){
                foreach($this->orderData['shipments']['resource']['embedded'] as $shipment) {
                    Queue::later(QueueHelperClass::getNearestTimeRoundedUp(5, true), new ProcessShipmentJob($shipment['id'], $this->externalIdentifier, $shipment, $this->orderData), null, $this->queueName);
                }
            }
            */

            Log::info(' ---- SUCCESS JOB ------ ');

        }
        catch (Exception $e) {

            Log::info(' ---- JOB FAILED ------ ');
            Log::info( ' Message: ' . $e->getMessage());
            Log::info( ' File: ' . $e->getFile());
            Log::info(' ---- FAILED JOB ------ ');

            if ($e->getCode() == 429) {
                Log::info('[LSAPI] Rate Limit hit for order ' . $this->externalOrderId . ' with store ' . $apiCredentials->businessUUID);
                //Queue::later(QueueHelperClass::getNearestTimeRoundedUp(), new ProcessOrderJob($this->externalOrderId, $this->externalIdentifier, $this->orderData), null, $this->queueName);
                $this->release(QueueHelperClass::getNearestTimeRoundedUp(5, true));
                Log::info('RELEASED BACK TO QUEUE');
            }else{
                //release back to the queue if failed
                Log::info('Releasing back to queue for other reason');
                $this->release(QueueHelperClass::getNearestTimeRoundedUp(5, true));
                Log::info('RELEASED BACK TO QUEUE');
            }
        }

        Log::info(' ---- ENDING JOB ------ ');
    }
}
