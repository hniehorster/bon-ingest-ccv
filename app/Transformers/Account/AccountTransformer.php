<?php
namespace App\Transformers\Language;

use App\Transformers\Transformer;

class AccountTransformer {

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
    public function transform(array $subResources = null) : array {
        return $this->transform->transformObject($this->transform->externalObject, $this->matchingData());
    }

    /**
     * @return string[]
     */
    public function matchingData() : array
    {
        return [
            'name'                => 'company',
            'address_1'           => 'address_line',
            'address_2'           => '',
            'number'              => '',
            'number_extension'    => '',
            'zipcode'             => 'zipcode',
            'city'                => 'city',
            'country'             => 'country',
            'country_code'        => 'country_code',
            'region'              => '',
            'coc_number'          => 'coc_number',
            'coc_location'        => '',
            'vat_number'          => 'tax_number',
            'national_id'         => '',
        ];
    }

}
