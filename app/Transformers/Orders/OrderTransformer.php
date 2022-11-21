<?php
namespace App\Transformers\Orders;

use App\Transformers\Transformer;

class OrderTransformer {

    public $orderObject;
    public $transform;

    public function __construct(Transformer $transform) {

        $this->transform = $transform;
    }

    /**
     * @param $orderJSON
     * @return array
     */
    public function transform() : array
    {
        return $this->transform->transformObject($this->transform->externalObject, $this->matchingData());
    }

    /**
     * @return string[]
     */
    public function matchingData() : array {

        return [
            'business_uuid'                     => 'BON_BUSINESSUUID',
            'gid'                               => 'gid:order:id',
            'number'                            => 'ordernumber_full',
            'locale'                            => 'orderedinlng',
            'price_incl'                        => 'total_price',
            'price_excl'                        => 'SUB@total_price:total_tax',
            'weight'                            => 'total_weight',
            'first_name'                        => 'customer.billingaddress.first_name',
            'last_name'                         => 'customer.billingaddress.lastname',
            'phone'                             => 'customer.billingaddress.telephone',
            'email'                             => 'customer.email',
            'discount_code'                     => 'discountcoupon.code',
            'currency_code'                     => 'bon_default:currency',

            'payment_status'                    => '',
            'shipment_status'                   => '',

            'address_billing_name'              => 'customer.billingaddress.full_name',
            'address_billing_address_1'         => 'customer.billingaddress.address_line_1',
            'address_billing_address_2'         => 'customer.billingaddress.address_line_2',
            'address_billing_number'            => 'customer.billingaddress.housenumber',
            'address_billing_number_extension'  => 'customer.billingaddress.housenumber_suffix',
            'address_billing_zipcode'           => 'customer.billingaddress.zipcode',
            'address_billing_city'              => 'customer.billingaddress.city',
            'address_billing_region'            => 'customer.billingaddress.province',
            'address_billing_region_code'       => '',
            'address_billing_country_code'      => 'customer.billingaddress.country_code',
            'address_billing_country_title'     => 'customer.billingaddress.country',
            'address_shipping_name'             => 'customer.deliveryaddress.full_name',
            'address_shipping_address_1'        => 'customer.deliveryaddress.address_line_1',
            'address_shipping_address_2'        => 'customer.deliveryaddress.address_line_2',
            'address_shipping_number'           => 'customer.deliveryaddress.housenumber',
            'address_shipping_number_extension' => 'customer.deliveryaddress.housenumber_suffix',
            'address_shipping_zipcode'          => 'customer.deliveryaddress.zipcode',
            'address_shipping_city'             => 'customer.deliveryaddress.city',
            'address_shipping_region'           => 'customer.deliveryaddress.province',
            'address_shipping_region_code'      => '',
            'address_shipping_country_code'     => 'customer.deliveryaddress.country_code',
            'address_shipping_country_title'    => 'customer.deliveryaddress.country',
            'shop_created_at'                   => 'create_date',
            'shop_updated_at'                   => 'create_date',
        ];
    }
}
