<?php
namespace App\Http\Controllers\Install;

use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Http\Controllers\Controller;
use App\Jobs\FetchShopDataJob;
use App\Models\BusinessToken;
use App\Models\Handshake;
use App\Transformers\Transformer;
use BonSDK\Classes\BonSDKGID;
use BonSDK\SDKIngest\Services\Accounts\AccountService;
use BonSDK\SDKIngest\Services\Businesses\BusinessAdminService;
use BonSDK\SDKIngest\Services\Businesses\BusinessAuthService;
use BonSDK\SDKIngest\Services\Businesses\BusinessService;
use BonSDK\SDKIngest\Services\Communications\AuthPlatformSelectedService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Cookie;

class InstallController extends Controller {


    /**
     * @param Request $request
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     * @throws \Illuminate\Validation\ValidationException
     *
     * https://ccv.ingest.getbonhq.eu/en/install?
     * api_public=e_27%5EttmcoI6cFMiGEbjNN0yKxUVKqbQ
     * &language=nl
     * &x-hash=ea1c4d1f95a745c9e8c0b04b454b33673d9e574660586626219a8c10230e316dfdea1e8caf7eaba5a09d8fc6d59e06fe5caf13d17bcdd76619cead811d4389a8
     *
     */
    public function preInstall(Request $request) {

        Log::info('-------- INCOMING INSTALL --------');
        Log::info('All request data: '. $request->getContent());
        Log::info('All request URL: ' .  URL::current());
        Log::info('ApiPublic: ' . $request->api_public);
        Log::info('Requested Hash: ' . $request->get('x-hash'));

        $handShakeData[] = 'https://ccv.ingest.getbonhq.eu/en/install';
        $handShakeData[] = $request->api_public;;

        $handShakeString = implode('|', $handShakeData);
        $handShakeSecret = env('CCV_SECRET_KEY');

        $sHash = hash_hmac('sha512', $handShakeString, $handShakeSecret);

        Log::info('Hashed String: ' . $handShakeString);
        Log::info('Hash: ' . $sHash);
        Log::info('-------');

        if($sHash === $request->get('x-hash')) {

            $apiUser = Handshake::where('api_public', $request->api_public)->first();

            //Let's patch the user
            $apiId = env('CCV_APP_ID');

            $formParams     = ['is_installed' => true];
            $Uri            = '/api/rest/v1/apps/' . $apiId;
            $sDate          = date('c');

            $aDataToHash    = [];
            $aDataToHash[]  = $request->api_public;
            $aDataToHash[]  = 'PATCH';
            $aDataToHash[]  = $apiUser->api_root . $Uri;
            $aDataToHash[]  = json_encode($formParams);
            $aDataToHash[]  = $sDate;

            $sStringToHash = implode('|', $aDataToHash);

            Log::info('User Details', $apiUser->toArray());
            Log::info('Secret String: ' .  $apiUser->api_secret);

            $sHash = hash_hmac('sha512', $sStringToHash, $apiUser->api_secret);
            Log::info('Hashed String: ' . $sStringToHash);
            Log::info('Hash: ' . $sHash);


            $response = Http::withOptions(['debug' => true])->withHeaders([
                'x-date' => $sDate,
                'x-public' => $request->api_public,
                'x-hash' => $sHash
            ])->patch($apiUser->api_root.$Uri, json_encode($formParams));

            $client = new \JacobDeKeizer\Ccv\Client();
            $client->setBaseUrl('https://bonapp1.ccvshop.nl');
            $client->setPublicKey($request->api_public);
            $client->setPrivateKey($apiUser->api_secret);

            $result = $client->root()->all();

            if($response->successful()){
                return redirect($apiUser->return_url);
            }else{
                echo $response->body();
                echo 'Something went wrong';
            }

        }else{
            throw new Exception('Invalid Request');
        }

    }


    public function confirmSubscription(Request $request)
    {
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

                $externalLanguages = $shopApi->languages->get();

                $defaultLanguage = 'en';

                foreach($externalLanguages as $language) {
                    $defaultLanguage = $language['code'];

                    if($language['isDefault']){
                        $defaultLanguage = $language['code'];
                        break;
                    }
                }

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
                    'default_locale'     => $defaultLanguage,
                    'default_currency'   => $businessDetails['currency']['shortcode']
                ];

                Log::info('Business Data: ' . json_encode($businessData, JSON_PRETTY_PRINT));

                $newBusiness = json_decode((new BusinessService())->createBusiness($request->get('language'), $businessData));

                //Create the Business Auth
                $businessAuthData['business_uuid']  = $newBusiness->uuid;
                $businessAuthData['type']           = "internal";
                $businessAuthData['description']    = "lightspeed_ecom-ingest";

                $businessAuth = json_decode((new BusinessAuthService())->createBusinessAuth($request->get('language'), $businessAuthData));

                $defaults = (new Transformer($newBusiness->uuid, $businessDetails, ))->shop->transform();

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

            //Reidrect to Apple Store

            return redirect('https://apps.apple.com/nl/app/bon-merchant-connect-and-grow/id1610694285');
        }
    }

}
