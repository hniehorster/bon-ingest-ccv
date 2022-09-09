<?php

namespace App\Jobs;

use App\Classes\AuthenticationHelper;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class FetchAllOrdersJob extends Job
{
    public $startTime;

    public $externalIdentifier;
    public $createdAtMax;

    protected int $maxPageNumber;
    protected int $pageNumber;

    const QUEUE_NAME = "initial";
    const JOB_INTERVAL = 2;

    public function __construct(int $externalIdentifier, string $createdAtMax)
    {
        $this->createdAtMax = $createdAtMax;
        $this->externalIdentifier = $externalIdentifier;

        Log::info('Fetch all orders has started for store: ' . $this->externalIdentifier);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $orderCount = 0;
            //0. Get credentials
            //1. Count Pages
            //2. Loop Through each page
            //3. Queue each order with a backoff
            //4. Limit is 12000 per hour / 2.7 requests per second. 10 requests per 5 seconds. Each order is at least 3 API calls

            $this->startTime = Carbon::now();

            $apiCredentials = AuthenticationHelper::getAPICredentials($this->externalIdentifier);

            $LSPDOrders = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);
            $LSPDOrderCount = $LSPDOrders->orders->count(['created_at_max' => $this->createdAtMax]);
            $orderCount = 0;
            $this->maxPageNumber = ceil($LSPDOrderCount / env('API_MAX_PAGE_SIZE'));

            Log::info('Total orders: ' . $LSPDOrderCount . ' spread over ' . $this->maxPageNumber . ' pages');

            for ($this->pageNumber = 1; $this->pageNumber <= $this->maxPageNumber; $this->pageNumber++) {

                Log::info('Starting Page: ' . $this->pageNumber);

                $orderObjects = $LSPDOrders->orders->get(null, ['created_at_max' => $this->createdAtMax, 'limit' => env('API_MAX_PAGE_SIZE'), 'page' => $this->pageNumber]);

                foreach ($orderObjects as $orderObject) {

                    $this->startTime = $this->startTime->addSeconds($this->getOffset());

                    $orderCount++;

                    Queue::later($this->startTime, new ProcessOrderJob($orderObject['id'], $apiCredentials->externalIdentifier), null, self::QUEUE_NAME);

                    Log::info($orderCount . '. order ' . $orderObject['id'] . ' has been stored with timing ' . $this->startTime->timestamp);
                }

            }

            $endTime = Carbon::now();

            $duration = $endTime->diffInSeconds($this->startTime);

            $perOrder = $duration/$orderCount;

            Log::info('Total Duration: '. $duration . '.' . $orderCount .' ' . gmdate('H:i:s', $duration) . ' avg ' . $perOrder . ' seconds');

        } catch (Exception $e) {
            Log::info('Message: ' . $e->getMessage());
            Log::info('onLine: ' . $e->getLine());
        }
    }


    public function getOffset()
    {

        $a=array(1,1,1,1,1,2);
        $random_keys=array_rand($a);

        return $a[$random_keys];
    }
}
