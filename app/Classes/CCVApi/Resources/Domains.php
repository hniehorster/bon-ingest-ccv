<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;
use Illuminate\Support\Facades\Log;

class Domains {

    const URL = '/webshops/%s/domains/';

    private $client;

    /**
     * @param CCVApi $client
     */
    public function __construct(CCVApi $client){
        $this->client = $client;
    }

    /**
     * @param int $webshopId
     * @return mixed
     * @throws \Exception
     */
    public function get(int $webshopId) {

        $url = sprintf(self::URL, $webshopId);

        Log::info("URL" . $url);

        return $this->client->sendRequest($url, 'GET');

    }
}
