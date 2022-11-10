<?php
namespace App\Transformers\Products;

use App\Transformers\Orders\OrderTransformer;
use App\Transformers\Transformer;

class ProductPhotoTransformer {

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
            'image' => 'deeplink'
        ];
    }

}
