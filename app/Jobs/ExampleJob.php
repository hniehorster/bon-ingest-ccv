<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class ExampleJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        Log::info('Test job _ contrstruct)');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Test job _ handled)');
    }
}
