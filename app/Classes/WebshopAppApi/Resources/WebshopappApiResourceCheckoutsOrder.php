<?php

namespace App\Classes\WebshopAppApi\Resources;

use App\Classes\WebshopAppApi\WebshopappApiClient;

class WebshopappApiResourceCheckoutsOrder
{
    /**
     * @var WebshopappApiClient
     */
    private $client;

    public function __construct(WebshopappApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param int $checkoutId
     * @param array $fields
     *
     * @return array
     * @throws WebshopappApiException
     */
    public function create($checkoutId, $fields)
    {
        return $this->client->create('checkouts/' . $checkoutId . '/order', $fields);
    }
}

