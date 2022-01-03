<?php
namespace App\Exceptions\Transformers;

use Exception;

class InvalidTransformerObjectTypeException extends Exception
{
    public function render()
    {
        return response(['error' => 'INVALID_OBJECT_TYPE'], 500);
    }
}
