<?php
namespace App\Transformers\Orders;

use App\Transformers\Orders\OrderTransformer;
use App\Transformers\Transformer;

class OrderRowTransformer {

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

            'product_id'        => 'product_id',
            'product_gid'       => 'gid:product:if_empty:product_id:id',
            'product_title'     => 'product_name',

            'quantity'          => 'count',

            'sku'               => 'sub_sku_number',
            'ean'               => 'sub_ean_number',
            'article_code'      => 'product_number',
            'weight'            => 'weight',

            'base_price_excl'   => 'dtax:price_without_discount:tax',
            'base_price_incl'   => 'price_without_discount',
            'price_excl'        => 'dtax:total_price:tax',
            'price_incl'        => 'total_price',
            'supplier_title'    => 'supplier'
        ];
    }

}

/*
     {
      "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orderrows\/648228528\/",
      "id": 648228528,
      "order_id": 339493812,
      "product_type": "product",
      "product_name": "OnePlus Blauw",
      "product_name_google": "OnePlus-Blauw",
      "product_number": "",
      "sub_product_number": "",
      "sub_sku_number": "",
      "sub_ean_number": "",
      "product_id": 845754960,
      "product_href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/products\/845754960",
      "count": 1,
      "price": 595,
      "product_purchase_price": 0,
      "discount": 10,
      "custom_price": false,
      "tax": 21,
      "unit": "stuk",
      "weight": 0,
      "memo": "",
      "package_id": 1664901,
      "package_name": "Kartonnen doos",
      "stock_location": "",
      "supplier": "",
      "user_discount": 0,
      "original_price": 585,
      "selling_price": 585,
      "price_without_discount": 595,
      "price_without_discount_with_attributes": 595,
      "total_price": 585,
      "total_extra_option_price": 0,
      "price_with_attributes": 585,
      "total_price_with_attributes": 585,
      "attributes": [],
      "uploads": [],
      "parent": {
        "href": "https:\/\/bonapp1.ccvshop.nl\/api\/rest\/v1\/orders\/339493812"
      }
 */
