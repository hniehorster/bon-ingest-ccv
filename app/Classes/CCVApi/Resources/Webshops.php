<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;

class Webshops {

    const URL = '/webshops';

    private $client;

    /**
     * @param CCVApi $client
     */
    public function __construct(CCVApi $client){
        $this->client = $client;
    }

    /**
     * @param $webshopId
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function get($webshopId = null, $params = []) {

        $url = self::URL;

        if(!is_null($webshopId)){
            $url .= '/' . $webshopId;
        }

        return $this->client->sendRequest($url, 'GET');
    }
}
