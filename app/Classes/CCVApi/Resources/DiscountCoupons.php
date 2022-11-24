<?php
namespace App\Classes\CCVApi\Resources;

use App\Classes\CCVApi\CCVApi;
use Illuminate\Support\Facades\Log;

class DiscountCoupons {

    const URL = '/discountcoupons/';

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
    public function get($couponId = null, array $params = []) {

        if (!$couponId)
        {
            return $this->client->sendRequest(self::URL . $this->client->convertQueryParams($params), 'GET');
        }
        else
        {
            return $this->client->sendRequest(self::URL . '/' . $couponId, 'GET');
        }
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
     * @param string $couponId
     * @return mixed
     * @throws \Exception
     */
    public function delete(string $couponId) {
        return $this->client->sendRequest(self::URL . '/' . $couponId, 'DELETE');
    }
}
