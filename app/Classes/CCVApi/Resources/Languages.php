<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;
use Illuminate\Support\Facades\Log;

class Languages {

    const URL = '/languages/';

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
    public function get() {

        return $this->client->sendRequest(self::URL, 'GET');

    }
}
