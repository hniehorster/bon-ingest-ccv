<?php
namespace App\Transformers;

use App\Exceptions\Transformers\InvalidTransformerObjectTypeException;
use App\Transformers\Language\LanguageTransformer;
use App\Transformers\Orders\OrderLineItemTransformer;
use App\Transformers\Orders\OrderRowTransformer;
use App\Transformers\Orders\OrderShipmentTransformer;
use App\Transformers\Orders\OrderTransformer;
use App\Transformers\Products\ProductPhotoTransformer;
use App\Transformers\Products\ProductTransformer;
use App\Transformers\Shipments\ShipmentProductTransformer;
use App\Transformers\Shipments\ShipmentTransformer;
use App\Transformers\Shop\ShopTransformer;
use BonSDK\Classes\BonSDKGID;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Sentry\Util\JSON;

class Transformer
{
    /**
     * @var string
     */
    public $businessUUID;
    public $externalObject;
    public $subResources = null;
    public $defaults;

    public function __construct(string $businessUUID, $externalObject, array $defaults = []){

        $this->externalObject   = $externalObject;
        $this->businessUUID     = $businessUUID;
        $this->platform         = env('PLATFORM_TEXT');
        $this->defaults         = $defaults;

        $this->registerResources();
    }

    /**
     * @return array
     */
    public function getExternalData() : array {
        return $this->externalObject;
    }

    /**
     *
     */
    public function registerResources(){
        $this->order                = new OrderTransformer($this);
        $this->orderRow             = new OrderRowTransformer($this);
        $this->orderShipment        = new OrderShipmentTransformer($this);
        $this->shop                 = new ShopTransformer($this);
        $this->language             = new LanguageTransformer($this);
        $this->product              = new ProductTransformer($this);
        $this->productPhoto         = new ProductPhotoTransformer($this);
        $this->shipment             = new ShipmentTransformer($this);
        $this->shipmentProduct      = new ShipmentProductTransformer($this);
    }

    /**
     * @param $input
     * @return array
     * @throws InvalidTransformerObjectTypeException
     */
    public function sanitizeInput($input): array
    {
        if (is_string($input)) {
            if ($this->jsonValidator($input)) {
                return json_decode($input);
            }
        }
        elseif (is_array($input)) {
            return $input;
        }
        elseif (is_object($input)) {
            return (array)$input;
        }
        else{
            throw new InvalidTransformerObjectTypeException();
        }
    }

    /**
     * @param array $children
     */
    public function registerChildren(array $children):void {
        foreach ($children as $key => $value) {
            $className = $value . 'Transformer';
            $this->$className = new $className($this, $key);
        }
    }

    /**
     * @param string $objectJSON
     * @return array
     */
    public function toArray(string $objectJSON): array
    {
        return json_decode($objectJSON);
    }

    /**
     * @param null $data
     * @return bool
     */
    public function jsonValidator($data = null)
    {

        if (!empty($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }

        return false;
    }

    /**
     * @param array $externalData
     * @param array $inputArray
     * @return array
     */
    public function transformObject(array $externalData, array $inputArray) : array {

        $transformedData = [];

        foreach($inputArray as $key => $value) {
            $transformedData[$key] = $this->transformKey($value, $externalData);
        }

        return $transformedData;
    }

    /**
     * @param string $key
     * @param string $value
     * @param array $externalData
     * @return array|mixed|string
     * @throws \BonSDK\ApiClient\BonException
     */
    public function transformKey(string $value, array $externalData) {

        if(Str::startsWith($value,'gid:')) {
            return $this->getGidTransformFromString($value, $externalData);
        }
        elseif(Str::startsWith($value, 'bon_default:')){
            return $this->getMerchantDefault($value);
        }
        elseif(Str::is($value, 'BON_BUSINESSUUID')){
            return $this->getBonBusinessUUIDFromString();
        }
        elseif(Str::startsWith($value,'sub:')) {
            return $this->getSubtract($value, $externalData);
        }
        elseif(Str::startsWith($value,'dtax:')) {
            return $this->getTaxDeducted($value, $externalData);
        }
        elseif(Str::startsWith($value,'if_empty:')) {

            Log::info('If_empty found: ' . $value);

            return $this->getAlternateValueIfEmpty($value, $externalData);
        }
        //Common usage
        elseif(Str::contains($value, '.')) {
            return $this->getValueFromMultiLevelString($value, $externalData);
        }
        elseif(Str::contains($value, '|')){
            return $this->getMultipleValuesFromString($value, $externalData);
        }
        elseif(empty($value)){
            return '';
        }
        else{
            return $externalData[$value];
        }
    }

    /***
     * INDIVIDUAL STRING TRANSFORMERS
     */

    /**
     * @param $array_ptr
     * @param $key
     * @param $value
     */
    private function getValueFromMultiLevelString(string $path, array $array) {
        $path       = explode('.', $path);
        $numArgs    = count($path);

        for ( $i = 0; $i < $numArgs; $i++ ) {
            if(isset($array[$path[$i]])){
                $array = $array[$path[$i]];
            }else{
                $array = null;
            }
        }

        return $array;
    }

    /**
     * @param $path
     * @param $array
     * @return string
     */
    private function getMultipleValuesFromString(string $path, array $array) {

        $returnString = [];

        $pathParts = explode('|', $path);

        foreach($pathParts as $key) {
            array_push($returnString, $this->transformKey($key, $array));
        }

        return implode(" ", $returnString);
    }

    /**
     * @param $key
     * @param $array
     * @return string
     * @throws \BonSDK\ApiClient\BonException
     */
    private function getGidTransformFromString(string $key, array $array): string {

        $strippedKey    = substr($key,4);
        $parts          = explode(':', $strippedKey);
        $objectType     = $parts[0];
        $externalKey    = $this->transformKey($parts[1], $array);

        return (new BonSDKGID)->encode($this->platform, $objectType, $this->businessUUID, $externalKey)->getGID();
    }

    /**
     * @param string $key
     * @return string
     */
    private function getBonBusinessUUIDFromString(): string {
        return $this->businessUUID;
    }

    /**
     * @param string $key
     * @return string
     */
    private function getMerchantDefault(string $key): string {

        $returnValue = "";

        $strippedKey = substr($key, 12);

        if($strippedKey == 'defaultLanguage') {
            foreach($this->defaults['languages'] AS $language){
                if($language['is_active']) {
                    $returnValue = $language['code'];
                }
            }
        }else{
            if(array_key_exists($strippedKey, $this->defaults)){
                $returnValue = $this->defaults[$strippedKey];
            }
        }

        return $returnValue;
    }

    /**
     * @param string $values
     * @param array $array
     * @return float
     * @throws \BonSDK\ApiClient\BonException
     */
    private function getSubtract(string $values, array $array) : float {

        $values = substr($values, 4);

        $values = explode(':', $values);

        foreach($values as $key => $value) {

            $subValue = (float) $this->transformKey($value, $array);

            if($key == 0){
                $returnValue = $subValue;
            }else{
                $returnValue = $returnValue-$subValue;
            }

        }

        return $returnValue;
    }

    /**
     * @param string $values
     * @param array $array
     * @return float
     * @throws \BonSDK\ApiClient\BonException
     */
    private function getTaxDeducted(string $values, array $array) : float {

        $values = substr($values, 5);

        $values = explode(':', $values);

        $mainPrice  = (float) $this->transformKey($values[0], $array);
        $taxRate    = (float) $this->transformKey($values[1], $array);

        if($taxRate > 1){
            $taxRate = $taxRate/100;
        }

        $mainPriceExcl = $mainPrice-($mainPrice*$taxRate);

        return $mainPriceExcl;

    }

    /**
     * @param string $values
     * @param array $array
     * @return void
     */
    private function getAlternateValueIfEmpty(string $values, array $array) {

        Log::info('IF EMPTY Triggered');

        $values = substr($values, strlen('if_empty:'));

        $arrayItems = explode(':', $values);

        Log::info('Items to check: ', $arrayItems);

        $returnValue = "";

        foreach($arrayItems as $arrayItem) {

            Log::info('Checking Item:' . $arrayItem);

            $transformedArrayItem = $this->transformKey($arrayItem, $array);

            Log::info('Transformed Item:' . $transformedArrayItem);

            if(!empty($transformedArrayItem)){

                Log::info('Item is not empty, lets use this:' . $transformedArrayItem);

                $returnValue = $transformedArrayItem;
                break;
            }
        }

        return $returnValue;

    }
}
