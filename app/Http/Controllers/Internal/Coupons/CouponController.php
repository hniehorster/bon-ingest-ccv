<?php
namespace App\Http\Controllers\Internal\Coupons;

use App\Classes\AuthenticationHelper;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Exceptions\Internal\Coupons\UnableToCreateExternalCouponException;
use App\Http\Controllers\Controller;
use App\Models\BusinessToken;
use BonSDK\Classes\BonSDKGID;
use BonSDK\SDKIngest\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class CouponController extends Controller {

    use ApiResponder;

    public function create(Request $request) {

        $this->validate($request, [
            'is_active'         => 'required|boolean',
            'code'              => 'required|string',
            'minimum_amount'    => 'required|numeric',
            'valid_from'        => 'required|date',
            'valid_till'        => 'required|date',
            'amount'            => 'required|numeric',
            'type'              => ['required', Rule::in(['amount','percentage'])],
            'connected_gid'     => 'required|string',
            'usage_limit'       => 'numeric'
        ]);

        try {

            $usageLimit = 1;

            if(isset($request->usage_limit)) {
                $usageLimit = $request->usage_limit;
            }

            $bonGID = (new BonSDKGID())->decode($request->connected_gid);

            $businessDetails = BusinessToken::where('business_uuid', $bonGID->getBusinessUUID())->first();

            $apiCredentials = AuthenticationHelper::getAPICredentials($businessDetails->external_identifier);

            $webshopAppClient = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);

            if($request->type == 'percentage') {
                $request->amount = $request->amount*100;
            }

            $discountParams = [
                'isActive'          => (bool) $request->is_active,
                'code'              => $request->code,
                'minimumAmount'     => $request->minimum_amount,
                'startDate'         => Carbon::parse($request->valid_from)->format('Y-m-d'),
                'endDate'           => Carbon::parse($request->valid_till)->format('Y-m-d'),
                'discount'          => $request->amount,
                'type'              => $request->type,
                'usageLimit'        => $usageLimit
            ];

            Log::info('Creating the external coupon with params: ' , $discountParams);

            $coupon = $webshopAppClient->discounts->create($discountParams);

            return $this->successResponse($coupon, Response::HTTP_CREATED);


        } catch (Exception $e) {

            Log::info('[EXTERNAL ERROR] Coupon Creation');
            Log::info('Message: ' . $e->getMessage());
            Log::info('Trace: ' . $e->getTraceAsString());

            throw new UnableToCreateExternalCouponException();
        }
    }

    /**
     * @param string $businessUUID
     * @param string $couponId
     * @param Request $request
     * @return void
     */
    public function delete(string $businessUUID, string $couponId, Request $request) {

        $request->merge(['business_uuid' => $businessUUID]);
        $request->merge(['coupon_id' => $couponId]);

        $this->validate($request, [
            'business_uuid'     => 'required|uuid',
            'coupon_id'         => 'required|string'
        ]);

        $businessDetails = BusinessToken::where('business_uuid', $request->business_uuid)->first();

        $apiCredentials = AuthenticationHelper::getAPICredentials($businessDetails->external_identifier);

        $webshopAppClient = new WebshopappApiClient($apiCredentials->cluster, $apiCredentials->externalApiKey, $apiCredentials->externalApiSecret, $apiCredentials->language);

        $webshopAppClient->discounts->delete($request->coupon_id);

    }

}
