<?php
namespace App\Http\Controllers\Internal\Coupons;

use App\Classes\AuthenticationHelper;
use App\Classes\CCVApi\CCVApi;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Exceptions\Internal\Coupons\UnableToCreateExternalCouponException;
use App\Http\Controllers\Controller;
use App\Models\BusinessToken;
use App\Models\Handshake;
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws UnableToCreateExternalCouponException
     * @throws \Illuminate\Validation\ValidationException
     */
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

            $calculatedDiscount = $request->amount;

            if($request->type == 'percentage') {
                $calculatedDiscount = $request->amount*100;
            }

            if($request->type == 'percentage') {
                $discountTypeName = 'Procent';
            } else {
                $discountTypeName = 'Price';
            }

            $discountParams = [
                'code'              => $request->code,
                'discount'          => $calculatedDiscount,
                'type'              => $discountTypeName,
                'minimumprice'      => $request->minimum_amount,
                'begindate'         => Carbon::parse($request->valid_from)->format('Y-m-d'),
                'enddate'           => Carbon::parse($request->valid_till)->format('Y-m-d'),
                'givesfreeshipping' => false,
                'onetimeuse'        => true,
            ];

            $bonGID = (new BonSDKGID())->decode($request->connected_gid);

            $apiUser = Handshake::where('business_uuid', $bonGID->getBusinessUUID())->first();

            $ccvClient = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);

            Log::info('Creating the external coupon with params: ' , $discountParams);

            $coupon = $ccvClient->discountcoupons->create($discountParams);

            return $this->successResponse(json_encode($coupon), Response::HTTP_CREATED);


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
     */
    public function delete(string $businessUUID, string $couponId, Request $request) {

        $request->merge(['business_uuid' => $businessUUID]);
        $request->merge(['coupon_id' => $couponId]);

        $this->validate($request, [
            'business_uuid'     => 'required|uuid',
            'coupon_id'         => 'required|string'
        ]);

        $apiUser = Handshake::where('business_uuid', $request->business_uuid)->first();

        $ccvClient = new CCVApi($apiUser->api_root, $apiUser->api_public, $apiUser->api_secret);

        $ccvClient->discountcoupons->delete($request->coupon_id);

        return $this->successResponse('COUPON_DELETED', Response::HTTP_NO_CONTENT);
    }

}
