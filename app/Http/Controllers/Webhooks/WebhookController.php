<?php
namespace App\Http\Controllers\Webhooks;

use App\Classes\WebhookRequestHelperClass;
use App\Http\Controllers\Controller;
use App\Jobs\Webhooks\OrderCreatedJob;
use App\Jobs\Webhooks\OrderIsPaidJob;
use App\Jobs\Webhooks\OrderStatusChangedJob;
use App\Jobs\Webhooks\OrderTrackAndTraceJob;
use BonSDK\SDKIngest\Traits\ApiResponder;
use Illuminate\Http\Request;


class WebhookController extends Controller
{
    use ApiResponder;

    /**
     * @param Request $request
     * @return void
     */
    public function orderCreated(int $shopId, Request $request) {

        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderCreatedJob($queueData->content['id'], $request->shopId, $queueData->content))->onQueue('direct');

    }

    /**
     * @param Request $request
     * @return void
     */
    public function orderIsPaid(int $shopId, Request $request) {

        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderIsPaidJob($queueData->content['id'], $request->shopId, $queueData->content))->onQueue('direct');
    }

    /**
     * @param Request $request
     * @return void
     */
    public function orderStatusChanged(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderStatusChangedJob($queueData->content['id'], $request->shopId, $queueData->content))->onQueue('direct');
    }

    /**
     * @param Request $request
     * @return void
     */
    public function orderTrackAndTrace(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderTrackAndTraceJob($queueData->content['id'], $request->shopId, $queueData->content))->onQueue('direct');
    }
}
