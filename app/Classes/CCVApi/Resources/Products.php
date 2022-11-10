<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;

class Products {

    const URL = '/products';

    private $client;

    public function __construct(CCVApi $client){
        $this->client = $client;
    }

    public function get($productId = null, $params = []) {

        $url = self::URL;

        if(!is_null($productId)){
            $url .= '/' . $productId;
        }

        return $this->client->sendRequest($url, 'GET');

    }
}
