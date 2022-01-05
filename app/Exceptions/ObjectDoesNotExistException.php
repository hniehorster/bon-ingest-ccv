<?php
namespace App\Exceptions;

use Exception;

class ObjectDoesNotExistException extends Exception
{
    public function render()
    {
        return response(['error' => 'OBJECT_DOES_NOT_EXIST'], 500);
    }
}
