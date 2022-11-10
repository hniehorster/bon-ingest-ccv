<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;

class Orders {

    const URL = '/orders';

    private $client;

    public function __construct(CCVApi $client){
        $this->client = $client;
    }

    public function get($orderId = null, $params = []) {

        $url = self::URL;

        if(!is_null($orderId)){
            $url .= '/' . $orderId;
        }

        return $this->client->sendRequest($url, 'GET');

    }
}
