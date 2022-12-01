<?php
namespace App\Http\Controllers\Webhooks\Orders;

use App\Classes\WebhookRequestHelperClass;
use App\Jobs\Webhooks\Orders\OrderCreatedJob;
use App\Jobs\Webhooks\Orders\OrderIsPaidJob;
use App\Jobs\Webhooks\Orders\OrderStatusChangedJob;
use App\Jobs\Webhooks\Orders\OrderTrackAndTraceJob;
use BonSDK\SDKIngest\Traits\ApiResponder;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;


class OrdersWebhookController extends BaseController
{
    use ApiResponder;

    /**
     * @param Request $request
     * @return void
     */
    public function orderCreated(int $shopId, Request $request) {

        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderCreatedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('direct');

    }

    /**
     * @param Request $request
     * @return void
     */
    public function orderIsPaid(int $shopId, Request $request) {

        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderIsPaidJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('direct');
    }

    /**
     * @param Request $request
     * @return void
     */
    public function orderStatusChanged(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderStatusChangedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('direct');
    }

    /**
     * @param Request $request
     * @return void
     */
    public function orderTrackAndTrace(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new OrderTrackAndTraceJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('direct');
    }
}
