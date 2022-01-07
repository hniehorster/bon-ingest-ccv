<?php
namespace App\Http\Controllers\Install;

use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Http\Controllers\Controller;
use App\Jobs\ExampleJob;
use App\Jobs\FetchShopDataJob;
use App\Models\BusinessToken;
use App\Transformers\Transformer;
use BonSDK\Classes\BonSDKGID;
use BonSDK\SDKIngest\Services\Accounts\AccountService;
use BonSDK\SDKIngest\Services\Businesses\BusinessAuthService;
use BonSDK\SDKIngest\Services\Businesses\BusinessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
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

        //1. Create the account
        $shopApi = new WebshopappApiClient($cluster, $apiKey, md5($request->get('token') . $apiSecret), $request->get('language'));

        $businessDetails = $shopApi->shop->get();
        $externalAccountDetails = $shopApi->shopCompany->get();

        $shortGID = (new BonSDKGID())->encodeShortHand(env('PLATFORM_TEXT'), 'business', $request->get('shop_id'))->getGID();

        //Check if the business has been registered already
        $businesses = json_decode((new BusinessService())->obtainBusinesses('en', ['gid_short' => $shortGID]));

        if($businesses->meta->count == 0){

            echo "New Business Needs to be created";

            $accountData['name']                = $externalAccountDetails['name'];
            $accountData['address_1']           = $externalAccountDetails['street'];
            $accountData['address_2']           = $externalAccountDetails['street2'];
            $accountData['number']              = "";
            $accountData['number_extension']    = "";
            $accountData['zipcode']             = $externalAccountDetails['zipcode'];
            $accountData['city']                = $externalAccountDetails['city'];
            $accountData['country']             = $externalAccountDetails['country']['title'];
            $accountData['country_code']        = $externalAccountDetails['country']['code'];
            $accountData['region']              = $externalAccountDetails['region'];
            $accountData['coc_number']          = $externalAccountDetails['cocNumber'];
            $accountData['coc_location']        = $externalAccountDetails['cocLocation'];
            $accountData['vat_number']          = $externalAccountDetails['vatNumber'];
            $accountData['national_id']         = $externalAccountDetails['nationalId'];

            //2. Create the Account
            $newAccount = json_decode((new AccountService())->createAccount('en', $accountData));

            $GID = (new BonSDKGID())->encode(env('PLATFORM_TEXT'), 'business', $newAccount->uuid, $request->shop_id)->getGID();

            //3. Create the business

            $businessData['account_uuid']       = $newAccount->uuid;
            $businessData['gid']                = $GID;
            $businessData['gid_short']          = $shortGID;
            $businessData['name']               = $externalAccountDetails['name'];
            $businessData['website']            = $businessDetails['mainDomain'];
            $businessData['type']               = "digital";
            $businessData['address_1']          = $externalAccountDetails['street'];
            $businessData['address_2']          = $externalAccountDetails['street2'];
            $businessData['number']             = "";
            $businessData['number_extension']   = "";
            $businessData['zipcode']            = $externalAccountDetails['zipcode'];
            $businessData['city']               = $externalAccountDetails['city'];
            $businessData['country']            = $externalAccountDetails['country']['title'];
            $businessData['country_code']       = $externalAccountDetails['country']['code'];
            $businessData['region']             = $externalAccountDetails['region'];

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

            //Add the user to the business as a store owner


            //Handle all the post install requirements.
            if(config('platform_config.has_webhooks')){

                $newWebhooks = config('platform_config.webhooks');

                //first remove all the webhooks
                $webhooks = $shopApi->webhooks->get();

                if(count($webhooks) > 0){
                    foreach($webhooks as $webhook) {
                        $shopApi->webhooks->delete($webhook['id']);
                    }
                }

                foreach($newWebhooks as $webhook){

                    $webhookParams = [];
                    $webhookParams['itemAction']    = $webhook['itemAction'];
                    $webhookParams['itemGroup']     = $webhook['itemGroup'];
                    $webhookParams['isActive']      = true;
                    $webhookParams['address']       = route($webhook['url']);
                    $webhookParams['format']        = 'json';
                    $webhookParams['language']      = $request->get('language');

                    Log::info(json_encode($webhookParams, JSON_PRETTY_PRINT));

                    $shopApi->webhooks->create($webhookParams);
                }
            }

            if(config('platform_config.has_shop_scripts')){

            }

            $now = Carbon::now()->format('Y-m-d H:i:s');

            //4. Create a job to fetch all orders
            dispatch(new FetchShopDataJob($request->get('shop_id')));

        }else{

            echo "Existing Business Found";

            //store already installed push socket out :P

        }

        return response("Success!");
    }

}
