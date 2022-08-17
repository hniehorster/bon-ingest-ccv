<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->group([
    'prefix' => '{apiLocale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
], function ($apiLocale) use ($router) {

    $router->get('/install', 'Install\InstallController@preInstall');
    $router->post('/install', ['as' => 'redirectPage', 'uses' => 'Install\InstallController@generateRedirect']);
    $router->get('/install/confirmed', ['as' => 'confirmedPage', 'uses' => 'Install\InstallController@postInstall']);
    $router->get('/time', 'TimeController@get');

    $router->get('/test', function() {
        echo route('ordersWebhook');
    });

});

$router->group([
    'prefix' => '{apiLocale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => 'service.authentication'
], function ($apiLocale) use ($router) {

    $router->post('/coupons', ['as' => 'internalCouponsCreate', 'uses' => 'Internal\Coupons\CouponController@create']);
    $router->delete('/coupons/{businessUUID}/{couponID}', ['as' => 'internalCouponsDelete', 'uses' => 'Internal\Coupons\CouponController@delete']);

});

$router->post('/webhooks/orders', ['as' => 'ordersWebhook', 'uses' => 'Orders\OrderController@acceptWebhook']);
$router->post('/webhooks/shipments', ['as' => 'shipmentsWebhook', 'uses' => 'Shipments\ShipmentController@acceptWebhook']);

