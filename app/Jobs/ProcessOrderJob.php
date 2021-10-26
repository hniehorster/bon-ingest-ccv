<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class ProcessOrderJob extends Job
{
    public $orderData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderData)
    {
        $this->orderData = $orderData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('AN ORDER IS PROCESSED' . json_encode($this->orderData, JSON_PRETTY_PRINT));
    }
}
