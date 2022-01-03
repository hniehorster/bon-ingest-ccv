<?php
namespace App\Classes\WebshopAppApi\Resources;

use App\Classes\WebshopAppApi\WebshopappApiClient;

class WebshopappApiResourceQuotesConvert
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
     * @param int $quoteId
     * @param array $fields
     *
     * @return array
     * @throws WebshopappApiException
     */
    public function create($quoteId, $fields)
    {
        $fields = array('order' => $fields);

        return $this->client->create('quotes/' . $quoteId . '/convert', $fields);
    }
}
