<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function() {
    return redirect('https://www.getbonhq.eu');
});

$router->group([
    'prefix' => '{apiLocale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
], function ($apiLocale) use ($router) {

    //Accept the handshake
    $router->get('/handshake', 'Install\HandshakeController@accept');
    $router->post('/handshake', 'Install\HandshakeController@accept');


    //Show the shopId form
    $router->get('/install', 'Install\InstallController@preInstall');

    //Show the subscription form
    $router->post('/install/subscription_confirm', ['as' => 'confirmSubscription', 'uses' => 'Install\InstallController@confirmSubscription']);

    //Generate the redirect
    $router->post('/install', ['as' => 'redirectPage', 'uses' => 'Install\InstallController@generateRedirect']);

    //Show the confirmed screen (no screen but socket).
    $router->get('/install/confirmed', ['as' => 'confirmedPage', 'uses' => 'Install\InstallController@postInstall']);

    $router->get('/time', 'TimeController@get');

    $router->get('/test', function() {
        echo route('ordersWebhook');
    });

});

/**
 * Bon Communication Endpoints
 */
$router->group([
    'prefix' => '{apiLocale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => 'service.authentication'
], function ($apiLocale) use ($router) {

    $router->post('/coupons', ['as' => 'internalCouponsCreate', 'uses' => 'Internal\Coupons\CouponController@create']);
    $router->delete('/coupons/{businessUUID}/{couponID}', ['as' => 'internalCouponsDelete', 'uses' => 'Internal\Coupons\CouponController@delete']);

});

$router->post('/webhooks/orders/created', ['as' => 'orderCreatedWebhook', 'uses' => 'Orders\OrderController@acceptWebhook']);
$router->post('/webhooks/orders/is_paid', ['as' => 'orderIsPaidWebhook', 'uses' => 'Orders\OrderController@acceptWebhook']);
$router->post('/webhooks/orders/status_change', ['as' => 'orderStatusChangedWebhook', 'uses' => 'Orders\OrderController@acceptWebhook']);
$router->post('/webhooks/orders/track_and_trace', ['as' => 'orderTrackAndTraceWebhook', 'uses' => 'Shipments\ShipmentController@acceptWebhook']);

