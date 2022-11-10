<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ManualLinkToken
 *
 * @property int $id
 * @property string $business_uuid
 * @property string $token_1
 * @property string $token_2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ManualLinkToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualLinkToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualLinkToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualLinkToken whereBusinessUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualLinkToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualLinkToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualLinkToken whereToken1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualLinkToken whereToken2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualLinkToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ManualLinkToken extends Model
{
    protected $fillable = [
        'business_uuid',
        'token_1',
        'token_2'
    ];

    protected $casts = [
        'business_uuid' => 'string',
        'token_1'       => 'string',
        'token_2'       => 'string'
    ];
}
