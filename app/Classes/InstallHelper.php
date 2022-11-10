<?php
namespace App\Classes;

use App\Classes\CCVApi\CCVApi;
use App\Exceptions\Install\InstallException;
use App\Models\Handshake;
use Exception;
use Illuminate\Support\Facades\Log;

class InstallHelper {

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
                    'address' => route($webhook['address'], ['shopId' => $apiUser->external_indentifier]),
                    'is_active' => $webhook['is_active'],
                ]);
            }

            return true;
        }catch (Exception $e) {
            throw new InstallException();
        }
    }

    public function determineDomainnameURL(string $url, bool $isSSL = false) {

        $host = "http://";

        if($isSSL) {
            $host = "https://";
        }

        return $host . $url;
    }
}
