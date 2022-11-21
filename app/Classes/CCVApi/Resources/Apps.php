<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;
use Illuminate\Support\Facades\Log;

class Apps {

    const URL = '/apps/';

    private $client;

    /**
     * @param CCVApi $client
     */
    public function __construct(CCVApi $client){
        $this->client = $client;
    }

    /**
     * @param int $appId
     * @return mixed
     * @throws \Exception
     */
    public function get(int $appId, array $params = null) {

        return $this->client->sendRequest(self::URL . $appId, 'GET');

    }

    /**
     * @param int $appId
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function update(int $appId, array $params) {

        return $this->client->sendRequest(self::URL . $appId, 'PATCH', $params);

    }
}
