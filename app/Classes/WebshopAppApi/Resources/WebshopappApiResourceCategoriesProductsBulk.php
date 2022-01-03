<?php
namespace App\Classes\WebshopAppApi\Resources;

use App\Classes\WebshopAppApi\WebshopappApiClient;

class WebshopappApiResourceCategoriesProductsBulk
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
     * @param array $fields
     *
     * @return array
     * @throws WebshopappApiException
     */
    public function create($fields)
    {
        $fields = array('categoriesProduct' => $fields);

        return $this->client->create('categories/products/bulk', $fields);
    }
}
