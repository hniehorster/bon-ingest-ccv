<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BusinessToken
 *
 * @property int $id
 * @property string|null $business_uuid
 * @property string|null $external_identifier External ID of the store
 * @property string|null $cluster
 * @property string|null $language
 * @property string|null $external_api_key
 * @property string|null $external_api_secret
 * @property string|null $internal_api_key
 * @property string|null $internal_api_secret
 * @property array|null $defaults
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken byExternalIdentifier($externalIdentifier)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereBusinessUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereCluster($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereDefaults($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereExternalApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereExternalApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereExternalIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereInternalApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereInternalApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
