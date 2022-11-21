<?php

namespace App\Jobs\Initial;

use App\Classes\CCVApi\CCVApi;
use App\Jobs\Job;
use App\Jobs\Webhooks\OrderCreatedJob;
use App\Models\Handshake;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class InitialOrderFetch extends Job
{

    protected $apiPublic;
    protected $createdAtMax;
    protected $startTime;

    CONST PAGE_SLEEP = 2; //Limits are limited to 150 per minute
    CONST QUEUE_NAME = 'initial';
    CONST ORDER_INTERVAL = 2;

    public function __construct(string $apiPublic, Carbon $createdAtMax)
    {
        $this->apiPublic = $apiPublic;
        $this->createdAtMax = $createdAtMax;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Log::info('Initial Order Grab initiated');

        $this->startTime = Carbon::now()->addMinutes(15);

        $apiUser = Handshake::where('api_public', $this->apiPublic)->first();

        $ccvClient = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);

        $orderCount = 0;
        $pageNumber = 0;

        do {

            Log::info('Start grabbing orders for ' . $pageNumber );

            $ccvClient->setPageNumber($pageNumber);
            $orders = $ccvClient->orders->get(null, ['max_create_date' => $this->createdAtMax->format('Y-m-d H:i:s')]);

            foreach($orders->items as $order) {

                $orderCount++;

                Log::info($orderCount . '. Dispatching an order for ' . $order->id );

                $this->startTime = $this->startTime->addSeconds(self::ORDER_INTERVAL);

                Queue::later($this->startTime, new OrderCreatedJob($order->id, $apiUser->external_identifier, json_decode(json_encode($order), true)), null, self::QUEUE_NAME);

            }

            sleep(self::PAGE_SLEEP);

            $pageNumber++;

        } while($ccvClient->hasNextPage());

        Log::info('Initial Order Grab finished');

    }
}
