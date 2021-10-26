<?php
namespace App\Exceptions\Queues;

use Exception;

class InvalidJobTypeException extends Exception
{
    public function render()
    {
        return response(['error' => 'INVALID JOB_TYPE'], 500);
    }
}
