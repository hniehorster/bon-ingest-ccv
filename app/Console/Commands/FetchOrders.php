<?php

namespace App\Console\Commands;

use App\Classes\AuthenticationHelper;
use App\Classes\CCVApi\CCVApi;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Jobs\ProcessOrderJob;
use App\Jobs\Webhooks\Orders\OrderCreatedJob;
use App\Models\Handshake;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class FetchOrders extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:fetch_history {apiPublic} {createdAtMax} {queueName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the historical orders';

    public function handle()
    {
        Log::info('Initial Order Grab initiated');

        $this->startTime = Carbon::now()->addMinutes(15);

        $apiPublic = $this->argument('apiPublic');
        $createdAtMax = $this->argument('createdAtMax');

        $apiUser = Handshake::where('api_public', $apiPublic)->first();

        $ccvClient = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);

        $orderCount = 0;
        $pageNumber = 0;

        do {

            Log::info('Start grabbing orders for ' . $pageNumber );

            $ccvClient->setPageNumber($pageNumber);
            $orders = $ccvClient->orders->get(null, ['max_create_date' => $createdAtMax]);

            foreach($orders->items as $order) {

                $orderCount++;

                Log::info($orderCount . '. Dispatching an order for ' . $order->id );

                $this->startTime = $this->startTime->addSeconds(2);

                Queue::later($this->startTime, new OrderCreatedJob($order->id, $apiUser->external_identifier, json_decode(json_encode($order), true)), null, $this->argument('queueName'));

            }

            Log::info('Next Page ' . $ccvClient->hasNextPage());

            sleep(2);

            $pageNumber++;

        } while($ccvClient->hasNextPage());

        Log::info('Initial Order Grab finished');
    }
}
