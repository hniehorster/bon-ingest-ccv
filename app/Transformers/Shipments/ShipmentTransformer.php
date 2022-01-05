<?php
namespace App\Transformers\Shipments;

use App\Transformers\Orders\OrderTransformer;
use App\Transformers\Transformer;

class ShipmentTransformer {

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
            'gid'               => 'gid:shipment:id',
            'number'            => 'number',
            'business_uuid'     => 'BON_BUSINESSUUID',
            'external_order_id' => 'order.resource.id',
            'tracking_code'     => 'trackingCode'
        ];
    }
}
