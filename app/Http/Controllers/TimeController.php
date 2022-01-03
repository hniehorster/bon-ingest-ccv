<?php

namespace App\Http\Controllers;

use App\Classes\AuthenticationHelper;
use BonSDK\ApiIngest\BonIngestAPI;
use Illuminate\Support\Facades\Log;

class TimeController extends Controller
{
    public function get()
    {
        $externalAuth = AuthenticationHelper::getAPICredentials(159905);

        $bonApi = new BonIngestAPI('local', $externalAuth->internalApiKey, $externalAuth->internalApiSecret, $externalAuth->language);

        Log::info('Locale set is' . $bonApi->getApiLanguage());
        return $bonApi->time->get();

    }
}
