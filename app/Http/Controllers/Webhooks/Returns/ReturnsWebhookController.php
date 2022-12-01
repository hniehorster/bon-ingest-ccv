<?php
namespace App\Http\Controllers\Webhooks\Categories;

use App\Classes\WebhookRequestHelperClass;
use App\Jobs\Webhooks\Orders\OrderCreatedJob;
use App\Jobs\Webhooks\Orders\OrderIsPaidJob;
use App\Jobs\Webhooks\Orders\OrderStatusChangedJob;
use App\Jobs\Webhooks\Orders\OrderTrackAndTraceJob;
use App\Jobs\Webhooks\Orders\ReturnCreatedJob;
use BonSDK\SDKIngest\Traits\ApiResponder;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;


class ReturnsWebhookController extends BaseController
{
    use ApiResponder;

    /**
     * @param int $shopId
     * @param Request $request
     * @return void
     */
    public function returnCreated(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new ReturnCreatedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('returns');
    }

    /**
     * @param int $shopId
     * @param Request $request
     * @return void
     */
    public function returnUpdated(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new ReturnCreatedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('returns');
    }

    /**
     * @param int $shopId
     * @param Request $request
     * @return void
     */
    public function returnDeleted(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new ReturnCreatedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('returns');
    }

}
