<?php

namespace App\Jobs;
use App\Classes\AuthenticationHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\Exception\WebshopappApiException;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use stdClass;
use Symfony\Component\Process\Process;

class QueueAllOrdes extends Job
{
    public $tries = 30;

    public $externalIdentifier;
    public $createdAtMax;
    public $startTime;
    protected int $maxPageNumber;
    protected int $pageNumber;

    const QUEUE_NAME = "initial";
    const JOB_INTERVAL = 2;

    public function __construct($externalIdentifier, string $createdAtMax)
    {
        $this->externalIdentifier   = $externalIdentifier;
        $this->createdAtMax         = $createdAtMax;
        $this->startTime            = Carbon::now()->addMinutes(1);
    }

    public function handle()
    {

        try {

            $timeStart = $this->startTime;

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

            $endTime = \Illuminate\Support\Carbon::now();

            $duration = $endTime->diffInSeconds($timeStart);

            $perOrder = $duration/$orderCount;

            Log::info('Total Duration: '. $duration . '.' . $orderCount .' ' . gmdate('H:i:s', $duration) . ' avg ' . $perOrder . ' seconds');

        } catch (Exception $e) {
            Log::info('Message: ' . $e->getMessage());
            Log::info('onLine: ' . $e->getLine());
        }
    }

    /**
     * @param int $pageNumber
     * @param stdClass $apiCredentials
     * @throws \App\Classes\WebshopAppApi\Exception\WebshopappApiException
     */
    public function fetchPage(int $pageNumber, stdClass $apiCredentials)
    {
        $ordersAPI = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);

        $orderObjects = $ordersAPI->orders->get(null, ['created_at_max' => $this->createdAtMax, 'limit' => env('API_MAX_PAGE_SIZE'), 'page' => $pageNumber]);

        foreach($orderObjects as $orderObject) {
            Queue::later(QueueHelperClass::getNearestTimeRoundedUp(5, true), new ProcessOrderJob($orderObject['id'], $apiCredentials->externalIdentifier), null, self::QUEUE_NAME);
        }
    }
}
