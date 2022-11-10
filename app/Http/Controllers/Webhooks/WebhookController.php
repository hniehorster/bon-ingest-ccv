<?php
namespace App\Http\Controllers\Webhooks;

use App\Classes\AuthenticationHelper;
use App\Classes\WebhookRequestHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Exceptions\Internal\Coupons\UnableToCreateExternalCouponException;
use App\Http\Controllers\Controller;
use App\Jobs\OrderCreatedJob;
use App\Jobs\OrderIsPaidJob;
use App\Jobs\OrderStatusChangedJob;
use App\Jobs\OrderTrackAndTraceJob;
use App\Models\BusinessToken;
use BonSDK\Classes\BonSDKGID;
use BonSDK\SDKIngest\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class WebhookController extends Controller
{
    use ApiResponder;

    /**
     * @param Request $request
     * @return void
     */
    public function orderCreated(Request $request) {

        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderCreatedJob($queueData->content['id'], $request->shopId, $queueData->content))->onQueue('direct');

    }

    /**
     * @param Request $request
     * @return void
     */
    public function orderIsPaid(Request $request) {

        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderIsPaidJob($queueData->content['id'], $request->shopId, $queueData->content))->onQueue('direct');
    }

    /**
     * @param Request $request
     * @return void
     */
    public function orderStatusChanged(Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderStatusChangedJob($queueData->content['id'], $request->shopId, $queueData->content))->onQueue('direct');
    }

    /**
     * @param Request $request
     * @return void
     */
    public function orderTrackAndTrace(Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderTrackAndTraceJob($queueData->content['id'], $request->shopId, $queueData->content))->onQueue('direct');
    }
}
