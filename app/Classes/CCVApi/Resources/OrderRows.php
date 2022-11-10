<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;

class OrderRows {

    const URL = '/orders/%s/orderrows/';

    private $client;

    public function __construct(CCVApi $client){
        $this->client = $client;
    }

    public function get(int $orderId, $orderRowId = null, $params = []) {

        $url = sprintf(self::URL, $orderId);

        if(!is_null($orderRowId)){
            $url = '/orderrows/' . $orderRowId;
        }

        return $this->client->sendRequest($url, 'GET');

    }
}
