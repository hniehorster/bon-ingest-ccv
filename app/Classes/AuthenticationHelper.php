<?php
namespace App\Classes;

use App\Models\BusinessToken;
use stdClass;

class AuthenticationHelper {

    /**
     * @param $externalIdentifier
     * @return stdClass
     */
    public static function getAPICredentials($externalIdentifier) : stdClass {

        $tokens = BusinessToken::byExternalIdentifier($externalIdentifier)->firstOrFail();

        $return = new stdClass();
        $return->cluster            = $tokens->cluster;
        $return->language           = $tokens->language;
        $return->businessUUID       = $tokens->business_uuid;
        $return->externalIdentifier = $tokens->external_identifier;
        $return->externalApiKey     = $tokens->external_api_key;
        $return->internalApiKey     = $tokens->internal_api_key;
        $return->internalApiSecret  = $tokens->internal_api_secret;
        $return->defaults           = $tokens->defaults;

        $apiSecret = env('LIGHTSPEED_API_KEY_EU1_SECRET');

        if($tokens->cluster == 'us1') {
            $apiSecret = env('LIGHTSPEED_API_KEY_US1_SECRET');
        }

        $return->externalApiSecret = md5($tokens->external_api_secret . $apiSecret);

        return $return;

    }
}
