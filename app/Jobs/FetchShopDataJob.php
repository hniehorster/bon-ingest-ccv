<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchShopDataJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $externalIdentifier;
    public $createdAtMax;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $externalIdentifier, string $createdAtMax)
    {
        $this->externalIdentifier   = $externalIdentifier;
        $this->createdAtMax         = $createdAtMax;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Initial Job Pushed');

        dispatch(new InitialFetchOrdersJob($this->externalIdentifier, $this->createdAtMax));
    }
}
