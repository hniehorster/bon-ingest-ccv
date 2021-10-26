<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public $apiLocale;

    public function __construct(Request $request)
    {
        $this->apiLocale = $request->route()[2]['apiLocale'];

        Log::info('Locale Found ' . $this->apiLocale);

        App::setLocale($this->apiLocale);

    }
}
