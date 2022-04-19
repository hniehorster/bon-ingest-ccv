<?php

namespace App\Console\Commands;

use App\Classes\AuthenticationHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Jobs\ProcessOrderJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class FetchOrders extends Command
{

    CONST PAGE_SIZE = 250;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:fetch_history {externalIdentifier} {createdAtMax} {queueName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the hsitorical orders';

    public function handle()
    {
        $externalIdentifier = $this->argument('externalIdentifier');
        $createdAtMax = $this->argument('createdAtMax');
        $queueName = $this->argument('queueName');

        $this->output->writeln('--- Start Fetching Historical Orders ---');
        $this->output->writeln(' External Identifier: ' . $externalIdentifier);
        $this->output->writeln(' CreatedAtMax: ' . $createdAtMax);
        $this->output->writeln(' queueName: ' . $queueName);

        $apiCredentials = AuthenticationHelper::getAPICredentials($externalIdentifier);

        $webshopAppClient = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);

        $orderCount = $webshopAppClient->orders->count(['created_at_max' => $createdAtMax]);

        $this->output->writeln('Found ' . $orderCount . ' order to be processed');

        $orders = $webshopAppClient->orders->get(null, ['created_at_max' => $createdAtMax, 'limit' => 250]);

        foreach ($orders as $order) {

            Queue::later(QueueHelperClass::getNearestTimeRoundedUp(5, true), new ProcessOrderJob($order['id'], $apiCredentials->externalIdentifier), null, $queueName);
            $this->output->writeln(' - Order ' . $order['id'] . ' has been queued on queue ' . $queueName);
        }

        $this->output->writeln('--- Done ---');
    }
}
