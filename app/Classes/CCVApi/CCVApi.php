<?php
namespace App\Classes\CCVApi;

use App\Classes\CCVApi\Resources\Apps;
use App\Classes\CCVApi\Resources\DiscountCoupons;
use App\Classes\CCVApi\Resources\Domains;
use App\Classes\CCVApi\Resources\Languages;
use App\Classes\CCVApi\Resources\Merchant;
use App\Classes\CCVApi\Resources\OrderRows;
use App\Classes\CCVApi\Resources\Orders;
use App\Classes\CCVApi\Resources\ProductPhotos;
use App\Classes\CCVApi\Resources\Products;
use App\Classes\CCVApi\Resources\Webhooks;
use App\Classes\CCVApi\Resources\Webshops;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\returnValue;

class CCVApi {

    const PAGE_SIZE = 25;

    /**
     * @var string
     */
    protected $apiKey;
    protected $queryParams = [];
    protected $sortOrder;

    protected $pageNumber = 0;

    /**
     * @var string
     */
    protected $baseURL;
    protected $apiSecret;
    protected $version;
    protected $hasNextPage = false;
    protected $fullAPIURL;
    protected $hashURL;
    protected $responseBody;

    /**
     * @param string $baseURL
     * @param string $apiKey
     * @param string $apiSecret
     * @param string $version
     */
    public function __construct(string $baseURL, string $apiKey, string $apiSecret, string $version = '1'){

        $this->baseURL      = $baseURL;
        $this->apiKey       = $apiKey;
        $this->apiSecret    = $apiSecret;
        $this->version      = $version;

        $this->registerResources();

    }

    /**
     * @return void
     */
    private function registerResources()
    {
        $this->apps             = new Apps($this);
        $this->discountcoupons  = new DiscountCoupons($this);
        $this->domains          = new Domains($this);
        $this->languages        = new Languages($this);
        $this->merchant         = new Merchant($this);
        $this->orders           = new Orders($this);
        $this->orderRows        = new OrderRows($this);
        $this->products         = new Products($this);
        $this->productPhotos    = new ProductPhotos($this);
        $this->webhooks         = new Webhooks($this);
        $this->webshops         = new Webshops($this);

    }

    /**
     * @param string $url
     * @return void
     */
    public function generateURL(string $url) : void {
        $this->hashURL    = '/api/rest/v' . $this->version . $url;
        $this->fullAPIURL = $this->baseURL . $this->hashURL;
    }

    /**
     * @param $url
     * @param $method
     * @param $payload
     * @param $options
     * @return mixed
     * @throws \Exception
     */
    public function sendRequest($url, $method, $payload = null, $options = []) {

        $this->hasNextPage = false;

        $this->generateURL($url);

        $timestamp = (new DateTime('now', new DateTimeZone('UTC')))->format(DateTimeInterface::ISO8601);

        $postData = $payload !== null ? json_encode($payload) : null;

        $url = $this->hashURL . $this->convertQueryParams();

        $aDataToHash = [];
        $aDataToHash[] = $this->apiKey;
        $aDataToHash[] = $method;
        $aDataToHash[] = $url;
        $aDataToHash[] = $postData;
        $aDataToHash[] = $timestamp;

        $sStringToHash = implode('|', $aDataToHash);

        $requestHeaders['x-hash']   = hash_hmac('sha512', $sStringToHash, $this->apiSecret);
        $requestHeaders['x-public'] = $this->apiKey;
        $requestHeaders['x-date']   = $timestamp;
        $requestHeaders['Accept']   = 'application/json';

        $request = Http::withHeaders($requestHeaders);

        switch($method) {
            case 'GET':
                $response = $request->get($this->fullAPIURL . $this->convertQueryParams(), $payload);
                break;
            case 'POST':
                $response = $request->post($this->fullAPIURL . $this->convertQueryParams(), $payload);
                break;
            case 'PATCH':
                $response = $request->patch($this->fullAPIURL . $this->convertQueryParams(), $payload);
                break;
            case 'PUT':
                $response = $request->put($this->fullAPIURL . $this->convertQueryParams(), $payload);
                break;
            case 'DELETE':
                $response = $request->delete($this->fullAPIURL . $this->convertQueryParams());
                break;
            default:
                throw new \Exception('[CCVAPI] Method not supported');
        }

        if($response->successful()){

            $this->responseBody = json_decode($response->body());

            if(isset($this->responseBody->next)){

                $this->hasNextPage = true;
            }

            return $this->responseBody;

        }else{
            throw new \Exception('[CCVAPI - ERROR] ' . $this->fullAPIURL . '  ' . $response->body());
        }
    }

    /**
     * @return mixed
     */
    public function toArray() {
        return json_decode(json_encode($this->responseBody), true);
    }

    /**
     * @return bool
     */
    public function hasNextPage() : bool {
        return $this->hasNextPage;
    }

    /**
     * @param int $pageNumber
     * @return void
     */
    public function setPageNumber(int $pageNumber) {
        $this->pageNumber = $pageNumber;
    }

    /**
     * @param $array
     */
    public function getQueryParams($array = []) {

        $this->queryParams['start'] = 0;
        $this->queryParams['size'] = self::PAGE_SIZE;

        if($this->pageNumber > 0) {

            $this->queryParams['start'] = $this->pageNumber * self::PAGE_SIZE;
            $this->queryParams['size']  = self::PAGE_SIZE;
        }

        if(is_null($this->sortOrder)){
            $this->sortOrder = 'id_desc';
        }

        $this->queryParams['orderby'] = $this->sortOrder;

        $this->queryParams = array_merge($this->queryParams, $array);

        return $this->queryParams;
    }

    public function convertQueryParams(array $params = [] ) : string {
        return '?' . http_build_query($this->getQueryParams($params));
    }

}
