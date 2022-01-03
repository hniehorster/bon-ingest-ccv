<?php
namespace App\Classes\WebshopAppApi\Resources;

use App\Classes\WebshopAppApi\WebshopappApiClient;

class WebshopappApiResourceVariantsImage
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
     * @param int $variantId
     * @param array $fields
     *
     * @return array
     * @throws WebshopappApiException
     */
    public function create($variantId, $fields)
    {
        $fields = array('variantImage' => $fields);

        return $this->client->create('variants/' . $variantId . '/image', $fields);
    }

    /**
     * @param int $variantId
     *
     * @return array
     * @throws WebshopappApiException
     */
    public function get($variantId)
    {
        return $this->client->read('variants/' . $variantId . '/image');
    }

    /**
     * @param int $variantId
     *
     * @return array
     * @throws WebshopappApiException
     */
    public function delete($variantId)
    {
        return $this->client->delete('variants/' . $variantId . '/image');
    }
}
