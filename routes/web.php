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
    'prefix' => '/bigcommerce/api',
    'namespace' => 'BC',
    'middleware' => ['open-auth']
], function() use ($router) {
    $router->get('/all_products', [
        'uses' => 'ProductsController@getProducts',
        'as' => 'bc-products'
    ]);
});
$router->group([
    'prefix' => '/shopify/api',
    'namespace' => 'Shopify',
    'middleware' => ['open-auth']
], function() use ($router){
    $router->get('/all_products', [
        'uses' => 'ProductsController@getProducts',
        'as' => 'shopify-products'
    ]);
});

$router->group([
    'prefix' => '/BCDemoApp',
    'namespace' => 'BC'/*
    'middleware' => ['open-auth']*/
], function() use ($router) {
    $router->post('/auth/load', [
        'uses' => 'AuthController@callback_token',
        'as' => 'bc-auth-load-token'
    ]);
    $router->post('/auth/oauth', [
        'uses' => 'AuthController@callback_token',
        'as' => 'bc-auth-callback-token'
    ]);
    $router->get('/auth/oauth', [
        'uses' => 'AuthController@callback',
        'as' => 'bc-auth-callback'
    ]);
    $router->get('/auth/load', [
        'uses' => 'AuthController@load',
        'as' => 'bc-auth-load'
    ]);
});

// Request Acknowledgment URL for uptime checks
$router->get('/ping', function () {
    return response()->json(['ack' => time()]);
});
// Base Shopify app installation route
$router->get('/install', [
    'uses' => 'InstallationController@installStore',
    'as' => 'app-install'
]);


// Authenticated section of the app
$router->group(['middleware' => ['shopify-admin-auth']], function () use ($router) {
    // Installation validation controller
    $router->get('/install/validate', [
        'uses' => 'InstallationController@validateStore',
        'as' => 'app-install-validate'
    ]);
/*
    $router->group(['prefix' => '/admin'], function () use ($router) {
        $router->get('/check-ap21-status', [
            'uses' => 'CoreController@checkStatus',
            'as' => 'check-ap21-status'
        ]);
    });
*/
});
// Webhook URLs for Shopify Webhook Notifications
$router->group([
    'prefix' => '/webhook',
    'namespace' => 'Webhook',
    'middleware' => ['shopify-webhook-auth']
], function () use ($router) {
    $router->post('/{topic:.+}', [
        'uses' => 'IndexController@handle',
        'as' => 'webhook-handle'
    ]);
});

