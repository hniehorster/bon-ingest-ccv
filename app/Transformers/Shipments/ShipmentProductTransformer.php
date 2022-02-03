<?php
namespace App\Transformers\Shipments;

use App\Transformers\Orders\OrderTransformer;
use App\Transformers\Transformer;

class ShipmentProductTransformer {

    public $transform;

    /**
     * @param Transformer $transform
     */
    public function __construct(Transformer $transform)
    {
        $this->transform = $transform;
    }

    /**
     * @return array
     */
    public function transform(array $subResources = []) : array {

        $this->transform->subResources = $subResources;

        return $this->transform->transformObject($this->transform->externalObject, $this->matchingData());
    }

    /**
     * @return string[]
     */
    public function matchingData() : array
    {
        return [
            'external_id'       => 'id',
            'quantity'          => 'quantity',
            'ean'               => 'ean',
            'sku'               => 'sku',
            'article_code'      => 'articleCode'
        ];
    }
}
