<?php

namespace App\Jobs;

use App\Classes\AuthenticationHelper;
use App\Classes\QueueHelperClass;
use App\Classes\WebshopAppApi\WebshopappApiClient;
use App\Transformers\Transformer;
use BonSDK\ApiIngest\BonIngestAPI;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OrderStatusChangedJob extends Job implements ShouldQueue
{
    public $tries = 100;

    public $orderData;
    public $externalOrderId;
    public $externalIdentifier;
    public $queueName;
    public $reRelease = false;

    public function __construct(string $externalOrderId, string $externalIdentifier, array $orderData = null)
    {
        $this->externalOrderId    = $externalOrderId;
        $this->externalIdentifier = $externalIdentifier;
        $this->orderData          = $orderData;
    }

    public function handle() {
        Log::info(' ---- STARTING ' . static::class . ' ON QUEUE ' . $this->queueName . ' ------- ');



        Log::info(' ---- ENDING ' . static::class . ' ON QUEUE ' . $this->queueName . ' ------- ');
    }
}


/* Webhook Payload
{
  "triggered_at": "2022-11-09T16:17:30Z",
  "href": "https://bonapp1.ccvshop.nl/api/rest/v1/orders/339493812",
  "id": 339493812,
  "order_number": 3,
  "ordernumber_prefix": null,
  "ordernumber_full": "3",
  "total_price": 1175,
  "previous_status": 1,
  "status": 5,
  "customer_email": "h.niehorster@hjalding.nl",
  "customer_mobile": ""
}
 */

/* Status Explanations
1. New
2. In process
3. Wait for manufacturer
4. Wait for payment
5. Sent
6. Delivered
7. Completed
8. Cancelled
9. Wait for supplier
10. Is being packaged
11. Ready to be collected
12. Is being assembled
13. Backorder
14. Reserved
*/
