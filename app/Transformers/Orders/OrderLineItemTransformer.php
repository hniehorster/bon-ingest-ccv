<?php
namespace App\Transformers\Orders;

use App\Transformers\Orders\OrderTransformer;
use App\Transformers\Transformer;

class OrderLineItemTransformer {

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
            'business_uuid'     => 'BON_BUSINESSUUID',
            'line_item_id'      => 'id',
            'variant_id'        => 'variant.resource.id',
            'variant_gid'       => 'gid:variant:variant.resource.id',
            'product_id'        => 'product.resource.id',
            'product_gid'       => 'gid:product:variant.resource.id',
            'product_title'     => 'productTitle',
            'variant_title'     => 'variantTitle',
            'quantity'          => 'quantityOrdered',
            'quantity_returned' => 'quantityReturned',
            'sku'               => 'sku',
            'ean'               => 'ean',
            'article_code'      => 'articleCode',
            'weight'            => 'weight',
            'volume'            => 'volume',
            'base_price_excl'   => 'basePriceExcl',
            'base_price_incl'   => 'basePriceIncl',
            'price_excl'        => 'priceExcl',
            'price_incl'        => 'priceIncl',
            'supplier_title'    => 'supplierTitle',
            'brand_title'       => 'brandTitle'
        ];
    }

}
