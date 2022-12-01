<?php
namespace App\Http\Controllers\Webhooks\Products;

use App\Classes\WebhookRequestHelperClass;
use App\Jobs\Webhooks\Products\ProductCreatedJob;
use App\Jobs\Webhooks\Products\ProductDeletedJob;
use App\Jobs\Webhooks\Products\ProductUpdatedJob;
use BonSDK\SDKIngest\Traits\ApiResponder;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;


class ProductsWebhookController extends BaseController
{
    use ApiResponder;

    /**
     * @param int $shopId
     * @param Request $request
     * @return void
     */
    public function productCreated(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new ProductCreatedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('products');
    }

    /**
     * @param int $shopId
     * @param Request $request
     * @return void
     */
    public function productUpdated(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new ProductUpdatedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('products');
    }

    /**
     * @param int $shopId
     * @param Request $request
     * @return void
     */
    public function productDeleted(int $shopId, Request $request) {
        $webhookHelper = new WebhookRequestHelperClass($request);
        $queueData = $webhookHelper->getQueuePreparedData();

        dispatch(new ProductDeletedJob($queueData->content['id'], $shopId, $queueData->content))->onQueue('products');
    }

}
