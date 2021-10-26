<?php

namespace App\Http\Controllers\Shipments;

use App\Classes\QueueHelperClass;
use App\Classes\WebhookRequestHelperClass;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class ShipmentController extends BaseController {

    const OBJECT_TYPE = 'shipments';
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
