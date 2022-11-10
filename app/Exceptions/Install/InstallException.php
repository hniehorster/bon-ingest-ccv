<?php
namespace App\Exceptions\Install;

use Exception;
use Illuminate\Support\Facades\Log;

class InstallException extends Exception
{
    public function render($message)
    {
        return response(['error' => 'UNABLE TO CREATE EXTERNAL WEBHOOKS', 'message' => $message], 500);
    }
}
