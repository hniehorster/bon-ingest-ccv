<?php
namespace App\Transformers\Shop;

use App\Transformers\Transformer;

class ShopTransformer {

    public $transform;

    /**
     * @param array $orderLineItem
     */
    public function __construct(Transformer $transform)
    {
        $this->transform = $transform;
    }

    /**
     * @return array
     */
    public function transform(array $subResources = null) : array {
        return $this->transform->transformObject($this->transform->externalObject, $this->matchingData());
    }

    /**
     * @return string[]
     */
    public function matchingData() : array
    {
        return [
            'status'            => 'status',
            'currency'          => 'currency.shortcode'
        ];
    }

}
