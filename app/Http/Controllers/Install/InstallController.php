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
use Symfony\Component\HttpFoundation\Cookie;

class InstallController extends Controller {

    /**
     * @param Request $request
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     * @throws \Illuminate\Validation\ValidationException
     */
    public function preInstall(Request $request) {

        $rules = [
            'user_uuid' => 'required|uuid'
        ];

        $this->validate($request, $rules);

        return view('shopnumber', [
            'user_uuid' => $request->user_uuid,
            'apiLocale' => $this->apiLocale
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function generateRedirect(Request $request) {

        $rules = [
            'shop_number'   => 'required|integer',
            'user_uuid'     => 'required|uuid'
        ];

        $this->validate($request, $rules);

        $platform       = "eu1";
        $baseURL        = "https://api.webshopapp.com";
        $platformKey    = env('LIGHTSPEED_API_KEY_EU1');

        if($request->shop_number > 600000) {
            $platform   = "us1";
            $baseURL    = "https://api.shoplightspeed.com";
            $platformKey = env('LIGHTSPEED_API_KEY_US1');
        }

        $fullURL = $baseURL . '/' . $this->apiLocale . '/apps/install?api_key=' . $platformKey .'&shop_id=' . $request->input('shop_number') . '&cluster=' . $platform . '&user_uuid=' . $request->input('user_uuid');;

        return RedirectResponse::create($fullURL)->withCookie(new Cookie('user_uuid', $request->input('user_uuid')));
    }

    /**
     * @param Request $request
     */
    public function postInstall(Request $request) {

        $apiKey     = env('LIGHTSPEED_API_KEY_EU1');
        $apiSecret  = env('LIGHTSPEED_API_KEY_EU1_SECRET');
        $cluster    = "eu1";

        if($request->get('cluster') == 'us1') {
            $apiKey     = env('LIGHTSPEED_API_KEY_US1');
            $apiSecret  = env('LIGHTSPEED_API_KEY_US1_SECRET');
            $cluster    = "us1";
        }

        if($request->hasCookie('user_uuid')) {

            $userUUID = $request->cookie('user_uuid');

            Log::info('grabbed the user UUID cookie ' . $userUUID);

            //1. Create the account
            $shopApi = new WebshopappApiClient($cluster, $apiKey, md5($request->get('token') . $apiSecret), $request->get('language'));

            $businessDetails = $shopApi->shop->get();
            $externalAccountDetails = $shopApi->shopCompany->get();

            $shortGID = (new BonSDKGID())->encodeShortHand(env('PLATFORM_TEXT'), 'business', $request->get('shop_id'))->getGID();

            //Check if the business has been registered already
            $businesses = json_decode((new BusinessService())->obtainBusinesses('en', ['gid_short' => $shortGID]));

            if($businesses->meta->count == 0){

                $accountData = [
                    'name'                => $externalAccountDetails['name'],
                    'address_1'           => $externalAccountDetails['street'],
                    'address_2'           => $externalAccountDetails['street2'],
                    'number'              => "",
                    'number_extension'    => "",
                    'zipcode'             => $externalAccountDetails['zipcode'],
                    'city'                => $externalAccountDetails['city'],
                    'country'             => $externalAccountDetails['country']['title'],
                    'country_code'        => $externalAccountDetails['country']['code'],
                    'region'              => $externalAccountDetails['region'],
                    'coc_number'          => $externalAccountDetails['cocNumber'],
                    'coc_location'        => $externalAccountDetails['cocLocation'],
                    'vat_number'          => $externalAccountDetails['vatNumber'],
                    'national_id'         => $externalAccountDetails['nationalId'],
                ];

                //2. Create the Account
                $newAccount = json_decode((new AccountService())->createAccount('en', $accountData));

                $GID = (new BonSDKGID())->encode(env('PLATFORM_TEXT'), 'business', $newAccount->uuid, $request->shop_id)->getGID();

                //3. Create the business

                $businessData = [
                    'account_uuid'       => $newAccount->uuid,
                    'gid'                => $GID,
                    'gid_short'          => $shortGID,
                    'name'               => $externalAccountDetails['name'],
                    'website'            => $businessDetails['mainDomain'],
                    'type'               => "digital",
                    'address_1'          => $externalAccountDetails['street'],
                    'address_2'          => $externalAccountDetails['street2'],
                    'number'             => "",
                    'number_extension'   => "",
                    'zipcode'            => $externalAccountDetails['zipcode'],
                    'city'               => $externalAccountDetails['city'],
                    'country'            => $externalAccountDetails['country']['title'],
                    'country_code'       => $externalAccountDetails['country']['code'],
                    'region'             => $externalAccountDetails['region'],
                ];

                $newBusiness = json_decode((new BusinessService())->createBusiness($request->get('language'), $businessData));

                //Create the Business Auth
                $businessAuthData['business_uuid']  = $newBusiness->uuid;
                $businessAuthData['type']           = "internal";
                $businessAuthData['description']    = "lightspeed_ecom-ingest";

                $businessAuth = json_decode((new BusinessAuthService())->createBusinessAuth($request->get('language'), $businessAuthData));

                $defaults = (new Transformer($newBusiness->uuid, $businessDetails, ))->shop->transform();

                $externalLanguages = $shopApi->languages->get();

                foreach($externalLanguages as $externalLanguage){
                    $defaults['languages'][] = (new Transformer($newBusiness->uuid, $externalLanguage))->language->transform();
                }

                //Store the BusinessAuth
                $newBusinessToken = BusinessToken::updateOrCreate([
                    'business_uuid'              => $newBusiness->uuid,
                    'external_identifier'  => $request->get('shop_id'),
                    'cluster'           => $cluster
                ], [
                    'language'             => $request->get('language'),
                    'external_api_key'     => $apiKey,
                    'external_api_secret'  => $request->get('token'),
                    'internal_api_key'     => $businessAuth->api_key,
                    'internal_api_secret'  => $businessAuth->api_secret,
                    'defaults'             => $defaults,
                ]);

                $now = Carbon::now()->format('Y-m-d H:i:s');

                //4. Create a job to fetch all orders
                dispatch(new FetchShopDataJob($request->get('shop_id'), $now));

                $businessUUID = $newBusiness->uuid;

            } else{
                $businessUUID = $businesses->data[0]->uuid;
            }

            //Check if the admin already exists.
            $businessAdmins = json_decode((new BusinessAdminService())->obtainBusinessAdmins('en', ['business_uuid' => $businessUUID]));

            $businessAdminFound = false;
            $businessAdminOwnerFound = false;

            foreach ($businessAdmins->data as $businessAdmin) {
                if($businessAdmin->user_uuid == $userUUID){
                    $businessAdminFound = true;
                }

                if($businessAdmin->is_owner) {
                    $businessAdminOwnerFound = true;
                }
            }

            if(!$businessAdminFound) {
                $createBusinessAdminParams['business_uuid'] = $businessUUID;
                $createBusinessAdminParams['user_uuid']     = $userUUID;
                $createBusinessAdminParams['is_active']     = true;

                if($businessAdminOwnerFound) {
                    $createBusinessAdminParams['is_owner']  = false;
                } else {
                    $createBusinessAdminParams['is_owner']  = true;
                }

                Log::info('Create Business Admin Data: ' . json_encode($createBusinessAdminParams, JSON_PRETTY_PRINT));

                $newBusinessAdmin = json_decode((new BusinessAdminService())->createBusinessAdmin('en', $createBusinessAdminParams));
            }

            //Handle all the post install requirements.
            if(config('platform_config.has_webhooks')){

                $newWebhooks = config('platform_config.webhooks');

                //first remove all the webhooks
                $webhooks = $shopApi->webhooks->get();

                if(count($webhooks) > 0){

                    foreach($webhooks as $existingWebhook) {
                        $shopApi->webhooks->delete($existingWebhook['id']);
                    }
                }

                foreach($newWebhooks as $webhook){

                    $webhookParams = [
                        'itemAction'    => $webhook['itemAction'],
                        'itemGroup'     => $webhook['itemGroup'],
                        'isActive'      => true,
                        'address'       => route($webhook['url']),
                        'format'        => 'json',
                        'language'      => $request->get('language')
                    ];

                    Log::info(json_encode($webhookParams, JSON_PRETTY_PRINT));

                    $shopApi->webhooks->create($webhookParams);
                }
            }

            if(config('platform_config.has_shop_scripts')){

            }

            $socket = (new AuthPlatformSelectedService())->confirmAuthPlatformSelected('en', $userUUID);

            return response("Success!");

        } else{
            return response('Your browser doesn\'t support the use of cookies', 400);
        }
    }

}
