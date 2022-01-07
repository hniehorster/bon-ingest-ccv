<?php

namespace App\Jobs;
use App\Classes\AuthenticationHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\Exception\WebshopappApiException;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use stdClass;

class InitialFetchOrdersJob extends Job
{
    public $tries = 30;

    public $externalIdentifier;
    public $pageNumber;
    public $createdAtMax;
    public $maxPageNumber;

    public function __construct($externalIdentifier, string $createdAtMax, int $pageNumber = null, int $maxPageNumber = null)
    {
        $this->externalIdentifier   = $externalIdentifier;
        $this->pageNumber           = (is_null($pageNumber)) ? $this->pageNumber = 1 : $this->pageNumber = (int) $pageNumber;
        $this->createdAtMax         = $createdAtMax;
        $this->maxPageNumber        = $maxPageNumber;
    }

    public function handle()
    {

        Log::info('Fetch Initial Orders');

        try {

            $apiCredentials = AuthenticationHelper::getAPICredentials($this->externalIdentifier);

            //start processing the pages
            if ($this->pageNumber == 1) {

                Log::info('Fetching the first page');

                //Check all the pages
                $ordersPageCount = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);
                $orderCount = $ordersPageCount->orders->count(['created_at_max' => $this->createdAtMax]);

                $this->maxPageNumber = ceil($orderCount / env('API_MAX_PAGE_SIZE'));

                $this->fetchPage(1, $apiCredentials);

            } else {

                Log::info('Fetching the ' . $this->pageNumber . ' page');

                $this->fetchPage($this->pageNumber, $apiCredentials);

            }

            if ($this->pageNumber < $this->maxPageNumber) {

                $this->pageNumber++;

                dispatch(new InitialFetchOrdersJob($this->externalIdentifier, $this->createdAtMax, $this->pageNumber, $this->maxPageNumber));
            }

        }
        catch (WebshopappApiException $e) {

            if($e->getCode() == 429){

                Log::info('Job Queued due to ApiLImit');

                Queue::later(QueueHelperClass::getNearestTimeRoundedUp(), new InitialFetchOrdersJob($this->externalIdentifier, $this->createdAtMax, $this->pageNumber, $this->maxPageNumber));
            }

        }
    }

    /**
     * @param int $pageNumber
     * @param stdClass $apiCredentials
     * @throws \App\Classes\WebshopAppApi\Exception\WebshopappApiException
     */
    public function fetchPage(int $pageNumber, stdClass $apiCredentials)
    {
        Log::info(json_encode($apiCredentials, JSON_PRETTY_PRINT));

        $ordersAPI = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);

        $orderObjects = $ordersAPI->orders->get(null, ['created_at_max' => $this->createdAtMax, 'limit' => env('API_MAX_PAGE_SIZE'), 'page' => $pageNumber]);

        foreach($orderObjects as $orderObject) {

            Log::info('Fetched Order' . $orderObject['id']);
            Queue::later(QueueHelperClass::getNearestTimeRoundedUp(5, true), new ProcessOrderJob($orderObject['id'], $apiCredentials->externalIdentifier));
            Log::info('Order Queued');
        }
    }
}
