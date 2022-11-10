<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;

class ProductPhotos {

    const URL = '/products/%s/productphotos';

    private $client;

    public function __construct(CCVApi $client){
        $this->client = $client;
    }

    public function get(int $productId) {

        $url = sprintf(self::URL, $productId);

        return $this->client->sendRequest($url, 'GET');

    }
}
