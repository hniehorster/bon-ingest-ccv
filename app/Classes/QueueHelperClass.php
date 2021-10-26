<?php
namespace App\Classes;

use App\Exceptions\Queues\InvalidJobTypeException;
use App\Jobs\ProcessOrderJob;
use App\Jobs\ProcessShipmentJob;
use Illuminate\Support\Facades\Queue;

class QueueHelperClass {

    public $queueName;

    /**
     * @param $queueName
     * @param $contentType
     * @param $content
     * @throws InvalidJobTypeException
     */
    public static function pushOn($queueName, $contentType, $content) {

        //TODO: AddStatsD

        $jobClass = self::getjob($contentType);

        Queue::pushOn($queueName, new $jobClass($content));

    }

    /**
     * @param $objectType
     * @return string
     * @throws InvalidJobTypeException
     */
    private static function getjob($objectType)  {

        if(array_key_exists($objectType, self::availableJobs())) {

            return self::availableJobs()[$objectType];

        } else{
            throw new InvalidJobTypeException();
        }
    }

    /**
     * @return string[]
     */
    private static function availableJobs() {
        return  [
            'orders'    => ProcessOrderJob::class,
            'shipments' => ProcessShipmentJob::class
        ];
    }

}
