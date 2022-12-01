<?php
namespace App\Http\Controllers\Webhooks\Categories;

use App\Classes\WebhookRequestHelperClass;
use App\Jobs\Webhooks\Categories\CategoryCreatedJob;
use App\Jobs\Webhooks\Categories\CategoryDeletedJob;
use App\Jobs\Webhooks\Categories\CategoryUpdatedJob;
use BonSDK\SDKIngest\Traits\ApiResponder;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;


class CategoriesWebhookController extends BaseController
{
    use ApiResponder;

    /**
     * @param int $shopId
     * @param Request $request
     * @return void
     */
    public function categoryCreated(int $shopId, Request $request) {

        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new CategoryCreatedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('categories');

    }

    /**
     * @param int $shopId
     * @param Request $request
     * @return void
     */
    public function categoryUpdated(int $shopId, Request $request) {

        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new CategoryUpdatedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('categories');
    }

    /***
     * @param int $shopId
     * @param Request $request
     * @return void
     */
    public function categoryDeleted(int $shopId, Request $request) {

        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new CategoryDeletedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('categories');

    }

}
