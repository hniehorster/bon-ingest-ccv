<?php
namespace App\Classes\WebshopAppApi\Resources;

use App\Classes\WebshopAppApi\WebshopappApiClient;

class WebshopappApiResourceCheckoutsPaymentMethods
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
     *
     * @return array
     * @throws WebshopappApiException
     */
    public function get($checkoutId)
    {
        return $this->client->read('checkouts/' . $checkoutId . '/payment_methods');
    }
}
