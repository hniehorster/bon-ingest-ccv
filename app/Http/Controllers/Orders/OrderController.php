<?php

namespace App\Http\Controllers\Orders;

use App\Classes\QueueHelperClass;
use App\Classes\WebhookRequestHelperClass;
use Illuminate\Http\Request;
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

        QueueHelperClass::pushOn(self::OBJECT_QUEUE, self::OBJECT_TYPE, $queueData);

    }

}
