<?php

namespace App\Classes\WebshopAppApi\Resources;

use App\Classes\WebshopAppApi\WebshopappApiClient;

class WebshopappApiResourceAccountPermissions
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
     * @return array
     * @throws WebshopappApiException
     */
    public function get()
    {
        return $this->client->read('account/permissions');
    }
}
