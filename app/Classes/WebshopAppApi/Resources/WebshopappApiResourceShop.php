<?php
namespace App\Classes\WebshopAppApi\Resources;

use App\Classes\WebshopAppApi\WebshopappApiClient;

class WebshopappApiResourceShop
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
        return $this->client->read('shop');
    }

    /**
     * @param array $fields
     *
     * @return array
     * @throws WebshopappApiException
     */
    public function update($fields)
    {
        $fields = array('shop' => $fields);

        return $this->client->update('shop', $fields);
    }
}
