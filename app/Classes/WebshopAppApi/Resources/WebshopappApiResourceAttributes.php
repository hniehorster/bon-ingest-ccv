<?php
namespace App\Classes\WebshopAppApi\Resources;

use App\Classes\WebshopAppApi\WebshopappApiClient;

class WebshopappApiResourceAttributes {

    private $client;

    public function __construct(WebshopappApiClient $client)
    {
        $this->client = $client;
    }

    public function create($fields)
    {
        $fields = array('attribute' => $fields);

        return $this->client->create('attributes', $fields);
    }

    /**
     * @param int $attributeId
     * @param array $params
     *
     * @return array
     * @throws WebshopappApiException
     */
    public function get($attributeId = null, $params = array())
    {
        if (!$attributeId)
        {
            return $this->client->read('attributes', $params);
        }
        else
        {
            return $this->client->read('attributes/' . $attributeId, $params);
        }
    }

    /**
     * @param array $params
     ** @throws WebshopappApiException
     */
    public function count($params = array())
    {
        return $this->client->read('attributes/count', $params);
    }

    /**
     * @param int $attributeId
     * @param array $fields
     * @throws WebshopappApiException
     */
    public function update($attributeId, $fields)
    {
        $fields = array('attribute' => $fields);

        return $this->client->update('attributes/' . $attributeId, $fields);
    }

    /**
     * @param int $attributeId
     * @throws WebshopappApiException
     */
    public function delete($attributeId)
    {
        return $this->client->delete('attributes/' . $attributeId);
    }
}
