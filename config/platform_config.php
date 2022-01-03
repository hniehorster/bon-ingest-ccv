<?php

return [
    'platform_name' => 'LightspeedEcom',
    'has_webhooks' => true,
    'has_shop_scripts' => true,
    'webhooks' => [
        'orders' => [
            'itemGroup'     => 'orders',
            'itemAction'   => '*',
            'url'           => 'ordersWebhook',
        ],
        'shipments' => [
            'itemGroup'     => 'shipments',
            'itemAction'   => '*',
            'url'           => 'shipmentsWebhook',
        ]
    ]
];
