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
        ],
        [
            'event'     => 'products.created',
            'address'   => 'productCreatedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'products.updated',
            'address'   => 'productUpdatedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'products.deleted',
            'address'   => 'productDeletedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'returns.created',
            'address'   => 'returnCreatedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'returns.updated',
            'address'   => 'returnUpdatedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'returns.deleted',
            'address'   => 'returnDeletedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'categories.created',
            'address'   => 'categoryCreatedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'categories.updated',
            'address'   => 'categoryUpdatedWebhook',
            'is_active' => true,
        ],
        [
            'event'     => 'categories.deleted',
            'address'   => 'categoryDeletedWebhook',
            'is_active' => true,
        ],
    ]
];
