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
            'gid'                               => 'gid:business:id',
            'number'                            => 'number',
            'locale'                            => 'language.code',
            'price_incl'                        => 'priceIncl',
            'price_excl'                        => 'priceExcl',
            'weight'                            => 'weight',
            'volume'                            => 'volume',
            'first_name'                        => 'firstname',
            'last_name'                         => 'lastname',
            'phone'                             => 'phone',
            'email'                             => 'email',
            'browser_ip'                        => 'remoteIp',
            'currency_code'                     => 'bon_default:currency',
            'payment_status'                    => 'paymentStatus',
            'shipment_status'                   => 'shipmentStatus',
            'address_billing_name'              => 'addressBillingName',
            'address_billing_address_1'         => 'addressBillingStreet',
            'address_billing_address_2'         => 'addressBillingStreet2',
            'address_billing_number'            => 'addressBillingNumber',
            'address_billing_number_extension'  => 'addressBillingExtension',
            'address_billing_zipcode'           => 'addressBillingZipcode',
            'address_billing_city'              => 'addressBillingCity',
            'address_billing_region'            => 'addressBillingRegionData.name',
            'address_billing_region_code'       => 'addressBillingRegionData.code',
            'address_billing_country_code'      => 'addressBillingCountry.code',
            'address_billing_country_title'     => 'addressBillingCountry.title',
            'address_shipping_name'             => 'addressShippingName',
            'address_shipping_address_1'        => 'addressShippingStreet',
            'address_shipping_address_2'        => 'addressShippingStreet2',
            'address_shipping_number'           => 'addressShippingNumber',
            'address_shipping_number_extension' => 'addressShippingExtension',
            'address_shipping_zipcode'          => 'addressShippingZipcode',
            'address_shipping_city'             => 'addressShippingCity',
            'address_shipping_region'           => 'addressShippingRegionData.name',
            'address_shipping_region_code'      => 'addressShippingRegionData.code',
            'address_shipping_country_code'     => 'addressShippingCountry.code',
            'address_shipping_country_title'    => 'addressShippingCountry.title',
            'shop_created_at'                   => 'createdAt',
            'shop_updated_at'                   => 'updatedAt',

        ];
    }
}
