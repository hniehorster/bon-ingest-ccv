<?php

namespace App\Jobs\Webhooks;

use App\Classes\QueueHelperClass;
use App\Jobs\Job;
use App\Models\Handshake;
use BonSDK\ApiIngest\BonIngestAPI;
use BonSDK\Classes\BonSDKGID;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class OrderIsPaidJob extends Job implements ShouldQueue
{
    public $tries = 100;

    public $orderData;
    public $externalOrderId;
    public $externalIdentifier;
    public $queueName;
    public $reRelease = false;
    protected $bonApi;

    public function __construct(string $externalOrderId, string $externalIdentifier, array $orderData = null)
    {
        $this->externalOrderId    = $externalOrderId;
        $this->externalIdentifier = $externalIdentifier;
        $this->orderData          = $orderData;
    }

    public function handle() {
        Log::info(' ---- STARTING ' . static::class . ' ON QUEUE ' . $this->queueName . ' ------- ');

        $apiUser = Handshake::where('external_identifier', $this->externalIdentifier)->first();
        $bonApi = new BonIngestAPI(env('BON_SERVER'), $apiUser->internal_api_key, $apiUser->internal_api_secret, $apiUser->language);

        $orderGID = (new BonSDKGID())->encode(env('PLATFORM_TEXT'), 'order', $apiUser->business_uuid, $this->externalOrderId)->getGID();

        $bonOrderCheck = $bonApi->orders->get(null, ['gid' => $orderGID]);

        var_dump($bonOrderCheck);

        Log::info('[BONAPI] Order Count ' . $bonOrderCheck->meta->count);

        if ($bonOrderCheck->meta->count > 0) {
            //Update the order

            if($this->orderData['paid']){
                Log::info('Order paid ');
                $paid = "paid";
            }else{
                Log::info('Order not paid ');
                $paid = "not_paid";
            }

            $bonOrder = $bonApi->orders->update($bonOrderCheck->data[0]->uuid, ['is_paid' => $paid]);

            var_dump($bonOrder);

        } else{
            $this->release(QueueHelperClass::getNearestTimeRoundedUp(5, true));
        }

        Log::info(' ---- ENDING ' . static::class . ' ON QUEUE ' . $this->queueName . ' ------- ');
    }
}

/**
 * {
"triggered_at": "2022-11-17T14:40:41Z",
"href": "https://bonapp1.ccvshop.nl/api/rest/v1/orders/340377945",
"id": 340377945,
"order_number": 24,
"ordernumber_prefix": null,
"ordernumber_full": "24",
"total_price": 590,
"paid": false, => true
"customer_email": "h.niehorster@hjalding.nl",
"customer_mobile": ""
}
 */
