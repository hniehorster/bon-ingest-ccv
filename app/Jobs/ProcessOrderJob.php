<?php

namespace App\Jobs;

use App\Classes\AuthenticationHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Transformers\Transformer;
use BonSDK\ApiIngest\BonIngestAPI;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;


class ProcessOrderJob extends Job implements ShouldQueue
{
    public $tries = 30;

    public $orderData;
    public $shipmentData;
    public $externalOrderId;
    public $externalIdentifier;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $externalOrderId, string $externalIdentifier, array $orderData = null)
    {
        $this->externalOrderId    = $externalOrderId;
        $this->externalIdentifier = $externalIdentifier;
        $this->orderData          = $orderData;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {

        try {
            Log::info('Processing order: ' . $this->externalOrderId);

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

            $this->shipmentData = $webshopAppClient->shipments->get(null, ['order' => $this->externalOrderId]);

            Log::info('Order Products ' . json_encode($this->orderData['products']['resource']['embedded'], JSON_PRETTY_PRINT));

            // Let's add the OrderLineItems
            foreach($this->orderData['products']['resource']['embedded'] as $product) {

                Log::info('Products: ' . json_encode($product, JSON_PRETTY_PRINT));

                $transformedLineItem = (new Transformer($apiCredentials->businessUUID, $product, $apiCredentials->defaults))->orderLineItem->transform();
                $transformedLineItem['order_uuid'] = $bonOrder->uuid;

                Log::info('Transformed LineItem: ' . json_encode($transformedLineItem, JSON_PRETTY_PRINT));

                //Check if Line item already exists
                $bonLineItemCheck = $bonApi->orderLineItems->get(null, ['order_uuid' => $bonOrder->uuid, 'line_item_id' => $transformedLineItem['line_item_id']]);

                Log::info('Bon Check: ' . json_encode($bonLineItemCheck, JSON_PRETTY_PRINT));

                if($bonLineItemCheck->meta->count > 0) {

                    Log::info('LineItem updated needed');

                    $bonLineItem = $bonApi->orderLineItems->update($bonLineItemCheck->data[0]->uuid, $transformedLineItem);

                    Log::info('Updated: ' . json_encode($bonLineItem, JSON_PRETTY_PRINT));
                }else{

                    Log::info('LineItem create needed');

                    $bonLineItem = $bonApi->orderLineItems->create($transformedLineItem);

                    Log::info('Created: ' . json_encode($bonLineItem, JSON_PRETTY_PRINT));
                }

                Log::info('Line Item Info: ' . json_encode($bonLineItem, JSON_PRETTY_PRINT));

                $shopProduct = $webshopAppClient->products->get($transformedLineItem['product_id']);

                $transformedProduct = (new Transformer($apiCredentials->businessUUID, $shopProduct, $apiCredentials->defaults))->product->transform();

                if(!is_null($transformedProduct['image'])){
                    $bonLineItemImage = $bonApi->orderLineItemImages->create($bonLineItem->uuid, ['external_url' => $transformedProduct['image']]);
                }
            }
        }
        catch (Exception $e) {
            if ($e->getCode() == 429) {
                Queue::later(QueueHelperClass::getNearestTimeRoundedUp(), new ProcessOrderJob($this->externalOrderId, $this->externalIdentifier, $this->orderData));
            }else{
                //release back to the queue if failed
                $this->release(QueueHelperClass::getNearestTimeRoundedUp());
            }
        }
    }
}
