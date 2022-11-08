<?php

return [
    'platform_name' => 'CCV',
    'has_webhooks' => true,
    'has_shop_scripts' => true,
    'webhooks' => [
        [
            'event'     => 'orders.created',
            'address'   => 'orderCreatedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'orders.ispaid',
            'address'   => 'orderIsPaidWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'orders.status',
            'address'   => 'orderStatusChangedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'orders.trackandtrace',
            'address'   => 'orderTrackAndTraceWebhook',
            'is_active' => true,
        ]
    ]
];
