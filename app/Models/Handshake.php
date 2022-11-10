<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Handshake
 *
 * @property int $id
 * @property string|null $hash
 * @property string|null $external_identifier
 * @property string|null $language
 * @property string|null $api_public
 * @property string|null $api_secret
 * @property string|null $api_root
 * @property string|null $return_url
 * @property array|null $defaults
 * @property string|null $internal_api_key
 * @property string|null $internal_api_secret
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake query()
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereApiPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereApiRoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereDefaults($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereExternalIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereInternalApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereInternalApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereReturnUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Handshake extends Model
{
    protected $fillable = [
        'hash',
        'external_identifier',
        'language',
        'business_uuid',
        'api_public',
        'api_secret',
        'api_root',
        'return_url',
        'defaults',
        'internal_api_key',
        'internal_api_secret'
    ];

    protected $casts = [
        'hash'                  => 'string',
        'external_identifier'   => 'string',
        'language'              => 'string',
        'business_uuid'         => 'string',
        'api_public'            => 'string',
        'api_secret'            => 'string',
        'api_root'              => 'string',
        'return_url'            => 'string',
        'defaults'              => 'array',
        'internal_api_key'      => 'string',
        'internal_api_secret'   => 'string'
    ];

    /**
     * @param $value
     */
    public function setDefaultsAttribute($value)
    {
        $this->attributes['defaults'] = json_encode($value);
    }
}
