<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

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

$router->post('/webhooks/orders', ['as' => 'ordersWebhook', 'uses' => 'Orders\OrderController@acceptWebhook']);
$router->post('/webhooks/shipments', ['as' => 'shipmentsWebhook', 'uses' => 'Orders\ShipmentController@acceptWebhook']);

