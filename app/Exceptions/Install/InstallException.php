<?php
namespace App\Exceptions\Install;

use Exception;
use Illuminate\Support\Facades\Log;

class InstallException extends Exception
{
    public function render()
    {
        return response(['error' => 'UNABLE TO CREATE EXTERNAL WEBHOOKS'], 500);
    }
}
