<?php
namespace App\Http\Controllers\Connect;

use App\Http\Controllers\Controller;
use App\Models\Handshake;
use App\Models\ManualLinkToken;
use BonSDK\ApiIngest\BonIngestAPI;
use BonSDK\SDKIngest\Services\Businesses\BusinessAdminService;
use BonSDK\SDKIngest\Services\Businesses\BusinessService;
use BonSDK\SDKIngest\Services\Communications\AuthPlatformSelectedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ConnectController extends Controller {

    /**
     * @param Request $request
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     * @throws \Illuminate\Validation\ValidationException
     */
    public function show(Request $request) {

        $rules = [
            'user_uuid' => 'required|uuid'
        ];

        $this->validate($request, $rules);

        return view('connect.show', [
            'user_uuid' => $request->user_uuid,
            'apiLocale' => App::getLocale()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\View\View|\Laravel\Lumen\Application|\Laravel\Lumen\Http\ResponseFactory
     * @throws \BonSDK\ApiIngest\Exceptions\BonIngestApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request) {

        $this->validate($request, [
            'token_1'   => 'required|string|max:4',
            'token_2'   => 'required|string|max:4',
            'user_uuid' => 'required|uuid'
        ]);

        $tokenCheck = ManualLinkToken::where([
            'token_1' => $request->token_1,
            'token_2' => $request->token_2,
        ])->firstOrFail();

        if($tokenCheck) {

            //Create the Admin
            $adminData = [
                'business_uuid' => $tokenCheck->business_uuid,
                'user_uuid'     => $request->user_uuid,
                'is_owner'      => true,
                'is_active'     => true
            ];

            $apiUser = Handshake::where('business_uuid', $tokenCheck->business_uuid)->first();

            $businessAdmin = (new BusinessAdminService())->createBusinessAdmin('en', $adminData);

            $socket = (new AuthPlatformSelectedService())->confirmAuthPlatformSelected('en', $request->user_uuid);

            $tokenCheck->delete();

            return view('connect.success');

        } else {
            return view('connect.show', ['error', 'NO_TOKEN_FOUND']);
        }
    }
}
