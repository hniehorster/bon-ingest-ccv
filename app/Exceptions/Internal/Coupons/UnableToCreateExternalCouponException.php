<?php
namespace App\Exceptions\Internal\Coupons;

use Exception;
use Illuminate\Support\Facades\Log;

class UnableToCreateExternalCouponException extends Exception
{
    public function render()
    {
        return response(['error' => 'UNABLE_TO_CREATE_EXTERNAL_COUPON'], 500);
    }
}
