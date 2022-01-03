<?php

namespace App\Http\Controllers\Shipments;

use App\Classes\QueueHelperClass;
use App\Classes\WebhookRequestHelperClass;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessOrderJob;
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

        dispatch(new ProcessOrderJob($queueData->headers['x-shipment-id'], $queueData->headers['x-shop-id'], $queueData->content['shipment']));
    }

}
