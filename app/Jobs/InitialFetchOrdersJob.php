<?php

namespace App\Jobs;
use Illuminate\Support\Facades\Log;

class InitialFetchOrdersJob extends Job
{

    public $initialOrderFetch;

    public function __construct($initialOrderFetch)
    {
        $this->initialOrderFetch = $initialOrderFetch;
    }

    public function handle()
    {
    }
}
