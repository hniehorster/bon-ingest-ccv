<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessToken extends Model {

    protected $fillable = [
        'business_uuid',
        'external_identifier',
        'cluster',
        'language',
        'external_api_key',
        'external_api_secret',
        'internal_api_key',
        'internal_api_secret',
        'defaults',
    ];

    protected $casts = [
        'business_uuid'         => 'string',
        'external_identifier'   => 'string',
        'cluster'               => 'string',
        'language'              => 'string',
        'external_api_key'      => 'string',
        'external_api_secret'   => 'string',
        'internal_api_key'      => 'string',
        'internal_api_secret'   => 'string',
        'defaults'              => 'array',
    ];

    /**
     * @param $value
     */
    public function setDefaultsAttribute($value)
    {
        $this->attributes['defaults'] = json_encode($value);
    }

    /**
     * @param $query
     * @param $externalIdentifier
     * @return mixed
     */
    public function scopeByExternalIdentifier($query, $externalIdentifier) {
        return $query->where('external_identifier', $externalIdentifier);
    }
}
