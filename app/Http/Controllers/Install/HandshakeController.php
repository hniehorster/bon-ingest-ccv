<?php
namespace App\Http\Controllers\Install;

use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Http\Controllers\Controller;
use App\Jobs\FetchShopDataJob;
use App\Models\BusinessToken;
use App\Transformers\Transformer;
use BonSDK\Classes\BonSDKGID;
use BonSDK\SDKIngest\Services\Accounts\AccountService;
use BonSDK\SDKIngest\Services\Businesses\BusinessAdminService;
use BonSDK\SDKIngest\Services\Businesses\BusinessAuthService;
use BonSDK\SDKIngest\Services\Businesses\BusinessService;
use BonSDK\SDKIngest\Services\Communications\AuthPlatformSelectedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Cookie;

class HandshakeController extends Controller {

    /**
     * @param Request $request
     * @return void
     */
    public function accept(Request $request) {

        Log::info('-------- INCOMING HANDSHAKE --------');
        Log::info('All request data', $request->all());

    }

}
