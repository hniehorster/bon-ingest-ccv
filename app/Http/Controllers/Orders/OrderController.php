<?php

namespace App\Http\Controllers\Orders;

use App\Classes\QueueHelperClass;
use App\Classes\WebhookRequestHelperClass;
use App\Jobs\ProcessOrderJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Routing\Controller as BaseController;

class OrderController extends BaseController {

    const OBJECT_TYPE = 'orders';
    const OBJECT_QUEUE = 'general';

    /**
     * @param Request $request
     */
    public function acceptWebhook(Request $request) {

        $webhook = new WebhookRequestHelperClass($request);
        $queueData = $webhook->getQueuePreparedData();

        dispatch(new ProcessOrderJob($queueData->headers['x-order-id'], $queueData->headers['x-shop-id'], $queueData->content['order']));

        return response()->json(['message' => 'accepted'], 200);
    }

}
