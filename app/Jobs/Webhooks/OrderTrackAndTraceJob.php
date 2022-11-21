<?php

namespace App\Jobs\Webhooks;

use App\Classes\AuthenticationHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Jobs\Job;
use App\Transformers\Transformer;
use BonSDK\ApiIngest\BonIngestAPI;
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
