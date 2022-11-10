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
            'event'     => 'orders.updated.ispaid',
            'address'   => 'orderIsPaidWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'orders.updated.status',
            'address'   => 'orderStatusChangedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'orders.updated.trackandtrace',
            'address'   => 'orderTrackAndTraceWebhook',
            'is_active' => true,
        ]
    ]
];
