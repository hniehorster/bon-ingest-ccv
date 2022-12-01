<?php

namespace App\Jobs\Webhooks\Products;

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

class ProductDeletedJob extends Job implements ShouldQueue
{
    public $tries = 100;

    public $productData;
    public $externalProductId;
    public $externalIdentifier;
    public $queueName;
    public $reRelease = false;

    public function __construct(string $externalProductId, string $externalIdentifier, array $productData = null)
    {
        $this->externalProductId    = $externalProductId;
        $this->externalIdentifier   = $externalIdentifier;
        $this->productData          = $productData;
    }

    public function handle() {

        Log::info(' ---- STARTING ' . static::class . ' ON QUEUE ' . $this->queueName . ' ------- ');

        try {

            $apiUser = Handshake::where('external_identifier', $this->externalIdentifier)->first();

            $ccvClient = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);

        } catch (Exception $e) {

            Log::info(' ---- JOB FAILED ------ ');
            Log::info( ' Message: ' . $e->getMessage());
            Log::info( ' File: ' . $e->getFile());
            Log::info( ' Trace: ' . $e->getTraceAsString());
            Log::info(' ---- FAILED JOB ------ ');

            if ($e->getCode() == 429) {
                Log::info('[CCVAPI] Rate Limit hit for order ' . $this->externalProductId . ' with store ' . $apiUser->businessUUID);
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
}
