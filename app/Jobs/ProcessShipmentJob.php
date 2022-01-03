<?php

namespace App\Jobs;

use App\Classes\AuthenticationHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Transformers\Transformer;
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
                $this->shipmentData = $webshopAppClient->orders->get($this->externalShipmentId);
            }

            $transformedShipment = (new Transformer($apiCredentials->businessUUID, $this->shipmentData, $apiCredentials->defaults))->shipment->transform();

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
