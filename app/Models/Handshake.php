<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Handshake
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $hash
 * @property string|null $api_public
 * @property string|null $api_secret
 * @property string|null $api_root
 * @property string|null $return_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake query()
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereApiPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereApiRoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereReturnUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Handshake whereUuid($value)
 * @mixin \Eloquent
 */
class Handshake extends Model
{
    protected $fillable = [
        'hash',
        'api_public',
        'api_secret',
        'api_root',
        'return_url'
    ];

    protected $casts = [
        'hash'          => 'string',
        'api_public'    => 'string',
        'api_secret'    => 'string',
        'api_root'      => 'string',
        'return_url'    => 'string',
    ];
}
