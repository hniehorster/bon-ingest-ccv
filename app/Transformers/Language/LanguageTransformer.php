<?php
namespace App\Transformers\Language;

use App\Transformers\Transformer;

class LanguageTransformer {

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
            'is_default'    => 'isDefault',
            'is_active'     => 'isActive',
            'code'          => 'code',
            'locale'        => 'locale',
        ];
    }

}
