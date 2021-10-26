<?php

return [
    'platform_name' => 'LightspeedEcom',
    'has_webhooks' => true,
    'webhooks' => [
        'orders' => [
            'url' => 'ordersWebhook',
        ],
        'shipments' => [
            'url' => 'shipmentsWebhook',
        ]
    ]
];
