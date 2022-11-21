<?php
namespace App\Classes;

use App\Classes\CCVApi\CCVApi;
use App\Exceptions\Install\InstallException;
use App\Jobs\Initial\InitialOrderFetch;
use App\Models\Handshake;
use Carbon\Carbon;
use Exception;

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

}
