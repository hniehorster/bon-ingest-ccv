<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;

class Webhooks {

    const URL = '/webhooks';

    private $client;

    /**
     * @param CCVApi $client
     */
    public function __construct(CCVApi $client){
        $this->client = $client;
    }

    /**
     * @param $webhookId
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function get($webhookId = null, $params = []) {

        $url = self::URL;

        if(!is_null($webhookId)){
            $url .= '/' . $webhookId;
        }

        return $this->client->sendRequest($url, 'GET');

    }

    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function create(array $params) {

        return $this->client->sendRequest(self::URL, 'POST', $params);

    }

    /**
     * @param int $webhookId
     * @return mixed
     * @throws \Exception
     */
    public function delete(int $webhookId) {
        return $this->client->sendRequest(self::URL . '/' . $webhookId, 'DELETE');
    }
}
