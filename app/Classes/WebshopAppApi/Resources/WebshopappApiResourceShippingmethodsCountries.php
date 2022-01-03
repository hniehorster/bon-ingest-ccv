<?php
namespace App\Classes\WebshopAppApi\Resources;

use App\Classes\WebshopAppApi\WebshopappApiClient;

class WebshopappApiResourceShippingmethodsCountries
{
    /**
     * @var WebshopappApiClient
     */
    private $client;

    public function __construct(WebshopappApiClient $client)
    {
        $this->client = $client;
    }

    public function get($shippingmethodId, $countryId = null, $params = array())
    {
        if (!$countryId)
        {
            return $this->client->read('shippingmethods/' . $shippingmethodId . '/countries', $params);
        }
        else
        {
            return $this->client->read('shippingmethods/' . $shippingmethodId . '/countries/' . $countryId, $params);
        }
    }

    /**
     * @param $shippingmethodId
     * @param array $params
     * @return WebshopappApiClient
     * @throws \App\Classes\WebshopAppApi\Exception\WebshopappApiException
     */
    public function count($shippingmethodId, $params = array()) : WebshopappApiClient
    {
        return $this->client->read('shippingmethods/' . $shippingmethodId . '/countries/count', $params);
    }
}
