<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function() {
    return redirect('https://www.getbonhq.eu');
});

//Accept the handshake
$router->get('/handshake', 'Install\HandshakeController@accept');
$router->post('/handshake', 'Install\HandshakeController@accept');


$router->get('/install', ['as' => 'confirmInstall', 'uses' => 'Install\InstallController@confirm']);
$router->post('/install/finalize', ['as' => 'finalizeInstall', 'uses' => 'Install\InstallController@finalize']);
$router->post('/uninstall', ['as' => 'uninstallStore', 'uses' => 'Uninstall\UninstallController@uninstall']);


$router->group([
    'prefix' => '{apiLocale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
], function ($apiLocale) use ($router) {

    $router->get('/time', 'TimeController@get');

    $router->get('/test', function() {
        echo route('ordersWebhook');
    });

    /****
     * DEBUG API
     */
    $router->get('/install/order', 'Install\InstallController@grabOrder');
    $router->get('/install/orders', 'Install\InstallController@grabAllOrders');
    $router->get('/install/webhooks', 'Install\InstallController@testWebhoks');

    /**
     * Connect
     */
    $router->get('/connect',  ['as' => 'connect.show', 'uses' => 'Connect\ConnectController@show']);
    $router->get('/connect/error',  ['as' => 'connect.error', 'uses' => 'Connect\ConnectController@show']);
    $router->post('/connect',  ['as' => 'connect.store', 'uses' => 'Connect\ConnectController@store']);
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

$router->post('/webhooks/orders/created/{shopId}', ['as' => 'orderCreatedWebhook', 'uses' => 'Webhooks\Orders\OrdersWebhookController@orderCreated']);
$router->post('/webhooks/orders/is_paid/{shopId}', ['as' => 'orderIsPaidWebhook', 'uses' => 'Webhooks\Orders\OrdersWebhookController@orderIsPaid']);
$router->post('/webhooks/orders/status_change/{shopId}', ['as' => 'orderStatusChangedWebhook', 'uses' => 'Webhooks\Orders\OrdersWebhookController@orderStatusChanged']);
$router->post('/webhooks/orders/track_and_trace/{shopId}', ['as' => 'orderTrackAndTraceWebhook', 'uses' => 'Webhooks\Orders\OrdersWebhookController@orderTrackAndTrace']);

$router->post('/webhooks/products/created/{shopId}', ['as' => 'productCreatedWebhook', 'uses' => 'Webhooks\Products\ProductsWebhookController@productCreated']);
$router->post('/webhooks/products/updated/{shopId}', ['as' => 'productUpdatedWebhook', 'uses' => 'Webhooks\Products\ProductsWebhookController@productUpdated']);
$router->post('/webhooks/products/deleted/{shopId}', ['as' => 'productDeletedWebhook', 'uses' => 'Webhooks\Products\ProductsWebhookController@productDeleted']);

$router->post('/webhooks/categories/created/{shopId}', ['as' => 'categoryCreatedWebhook', 'uses' => 'Webhooks\Categories\CategoriesWebhookController@categoryCreated']);
$router->post('/webhooks/categories/updated/{shopId}', ['as' => 'categoryUpdatedWebhook', 'uses' => 'Webhooks\Categories\CategoriesWebhookController@categoryUpdated']);
$router->post('/webhooks/categories/deleted/{shopId}', ['as' => 'categoryDeletedWebhook', 'uses' => 'Webhooks\Categories\CategoriesWebhookController@categoryDeleted']);

$router->post('/webhooks/returns/created/{shopId}', ['as' => 'returnCreatedWebhook', 'uses' => 'Webhooks\Returns\ReturbsWebhookController@returnCreated']);
$router->post('/webhooks/returns/updated/{shopId}', ['as' => 'returnUpdatedWebhook', 'uses' => 'Webhooks\Returns\ReturbsWebhookController@returnUpdated']);
$router->post('/webhooks/returns/deleted/{shopId}', ['as' => 'returnDeletedWebhook', 'uses' => 'Webhooks\Returns\ReturbsWebhookController@returnDeleted']);
