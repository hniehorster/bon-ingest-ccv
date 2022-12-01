<?php

namespace App\Jobs\Webhooks\Orders;

use App\Classes\CCVApi\CCVApi;
use App\Classes\QueueHelperClass;
use App\Jobs\Job;
use App\Models\Handshake;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ReturnDeletedJob extends Job implements ShouldQueue
{
    public $tries = 100;

    public $returnData;
    public $externalReturnId;
    public $externalIdentifier;
    public $queueName;
    public $reRelease = false;

    public function __construct(string $externalReturnId, string $externalIdentifier, array $returnData = null)
    {
        $this->externalReturnId     = $externalReturnId;
        $this->externalIdentifier   = $externalIdentifier;
        $this->returnData           = $returnData;
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
                Log::info('[CCVAPI] Rate Limit hit for order ' . $this->externalReturnId . ' with store ' . $apiUser->businessUUID);
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
