<?php
namespace App\Http\Controllers\Install;

use App\Classes\CCVApi\CCVApi;
use App\Classes\InstallHelper;
use App\Http\Controllers\Controller;
use App\Models\Handshake;
use App\Models\ManualLinkToken;
use BonSDK\Classes\BonSDKGID;
use BonSDK\SDKIngest\Services\Accounts\AccountService;
use BonSDK\SDKIngest\Services\Businesses\BusinessAuthService;
use BonSDK\SDKIngest\Services\Businesses\BusinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
    public function finalize(Request $request) {

        $apiKey = $request->api_public;

        $apiUser = Handshake::where('api_public', $apiKey)->first();
        $ccvClient = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);
        $installHelper = new InstallHelper();

        $appApproval = $ccvClient->apps->update(env('CCV_APP_ID'), ['is_installed' => true]);

        $defaults = $installHelper->grabLanguages($ccvClient, $apiUser->language);
        $defaults['status']     = 'live';
        $defaults['currency']   = 'EUR';

        $webshops = $ccvClient->webshops->get();
        $webshopId = $webshops->items[0]->id;

        $apiUser->external_identifier   = $webshopId;
        $apiUser->defaults              = $defaults;
        $apiUser->save();

        $merchantDetails = $ccvClient->merchant->get($webshopId);

        $shortGID = (new BonSDKGID())->encodeShortHand(env('PLATFORM_TEXT'), 'business', $webshopId)->getGID();
        $domainInfo = $ccvClient->domains->get($webshopId);

        $businesses = json_decode((new BusinessService())->obtainBusinesses('en', ['gid_short' => $shortGID]));

        if($businesses->meta->count == 0) {

            $accountDetails = [
                'name'                => $merchantDetails->company,
                'address_1'           => $merchantDetails->address_line,
                'address_2'           => null,
                'number'              => null,
                'number_extension'    => null,
                'zipcode'             => $merchantDetails->zipcode,
                'city'                => $merchantDetails->city,
                'country'             => $merchantDetails->country,
                'country_code'        => $merchantDetails->country_code,
                'region'              => null,
                'coc_number'          => $merchantDetails->coc_number,
                'coc_location'        => null,
                'vat_number'          => $merchantDetails->tax_number,
                'national_id'         => null,
            ];

            $newAccount = json_decode((new AccountService())->createAccount('en', $accountDetails));
            $GID = (new BonSDKGID())->encode(env('PLATFORM_TEXT'), 'business', $newAccount->uuid, $webshopId)->getGID();

            $businessData = [
                'account_uuid'      => $newAccount->uuid,
                'gid'               => $GID,
                'gid_short'         => $shortGID,
                'name'              => $newAccount->name,
                'website'           => $installHelper->determineDomainnameURL($domainInfo->items[0]->domainname, $domainInfo->items[0]->ssldomain),
                'type'              => "digital",
                'address_1'         => $merchantDetails->street,
                'address_2'         => "",
                'number'            => "",
                'number_extension'  => "",
                'zipcode'           => $merchantDetails->zipcode,
                'city'              => $merchantDetails->city,
                'country'           => $merchantDetails->country,
                'country_code'      => $merchantDetails->country_code,
                'region'            => "",
                'default_locale'    => $domainInfo->items[0]->language,
                'default_currency'  => 'EUR'
            ];

            $newBusiness = json_decode((new BusinessService())->createBusiness($domainInfo->items[0]->language, $businessData));

            $businessAuthData['business_uuid']  = $newBusiness->uuid;
            $businessAuthData['type']           = "internal";
            $businessAuthData['description']    = "ccv-ingest";

            $businessAuth = json_decode((new BusinessAuthService())->createBusinessAuth($businessData['default_locale'], $businessAuthData));

            $apiUser->internal_api_key      = $businessAuth->api_key;
            $apiUser->internal_api_secret   = $businessAuth->api_secret;
            $apiUser->save();

            $businessUUID = $newBusiness->uuid;

        }else{
            $businessUUID = $businesses->data[0]->uuid;
        }

        $apiUser->business_uuid = $businessUUID;
        $apiUser->save();

        $newManualLinkToken = new ManualLinkToken();
        $newManualLinkToken->business_uuid = $businessUUID;
        $newManualLinkToken->token_1 = Str::upper(Str::random(4));
        $newManualLinkToken->token_2 = Str::upper(Str::random(4));
        $newManualLinkToken->save();

        $createdAtMax = Carbon::now();

        $installHelper->installWebhooks($apiUser->api_public);
        $installHelper->fireInitialOrderGrabEvent($apiUser->api_public, $createdAtMax);


        return view('install.completed', [
            'manualToken' => $newManualLinkToken,
            'business'      => (new BusinessService())->obtainBusiness('en', $businessUUID)
        ]);
    }

    /**
     * @param Request $request
     */
    public function confirm(Request $request) {

        return view('install.confirm', [
            'api_public' => $request->api_public,
            'language'   => $request->language,
            'x_hash'     => $request->get('x-hash')
        ]);
    }

}
