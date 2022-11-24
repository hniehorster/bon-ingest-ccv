<?php
namespace App\Classes;

use App\Classes\CCVApi\CCVApi;
use App\Exceptions\Install\InstallException;
use App\Jobs\Initial\InitialOrderFetch;
use App\Models\Handshake;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class InstallHelper {

    /**
     * @param string $apiPublic
     * @return bool
     * @throws InstallException
     */
    public function installWebhooks(string $apiPublic){

        $apiUser = Handshake::where('api_public', $apiPublic)->first();

        try{
            $ccvClient = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);

            $allWebhooks = $ccvClient->webhooks->get();

            foreach($allWebhooks->items as $webhook) {
                $ccvClient->webhooks->delete($webhook->id);
            }

            $platformWebhooks = config('platform_config.webhooks');

            foreach($platformWebhooks as $webhook) {
                $ccvClient->webhooks->create([
                    'event' => $webhook['event'],
                    'address' => route($webhook['address'], ['shopId' => $apiUser->external_identifier]),
                    'is_active' => $webhook['is_active'],
                ]);
            }

            return true;
        }catch (Exception $e) {
            throw new InstallException($e->getMessage());
        }
    }

    /**
     * @param string $url
     * @param bool $isSSL
     * @return string
     */
    public function determineDomainnameURL(string $url, bool $isSSL = false) {

        $host = "http://";

        if($isSSL) {
            $host = "https://";
        }

        return $host . $url;
    }

    /**
     * @param string $apiPublic
     * @return void
     */
    public function fireInitialOrderGrabEvent(string $apiPublic, Carbon $createdAtMax) {
        dispatch(new InitialOrderFetch($apiPublic, $createdAtMax));
    }

    /**
     * @param CCVApi $ccVClient
     * @param string $apiLanguage
     * @return array
     * @throws Exception
     */
    public function grabLanguages(CCVApi $ccVClient, string $apiLanguage) : array
    {
        $languages = $ccVClient->languages->get();

        $returnArray = [];

        $index = 0;

        foreach ($languages->items as $language) {

            if($language->iso_code == $apiLanguage) {
                $returnArray['languages'][$index]['is_default'] = true;
            } else {
                $returnArray['languages'][$index]['is_default'] = false;
            }

            $returnArray['languages'][$index]['is_active'] = $language->active;
            $returnArray['languages'][$index]['code'] = $language->iso_code;
            $returnArray['languages'][$index]['locale'] = $language->iso_code . '_' . Str::upper($language->iso_code);

        }

        return $returnArray;
    }

}
