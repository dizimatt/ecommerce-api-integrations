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
    'prefix' => '/BCDemoApp',
    'namespace' => 'BC'
], function() use ($router) {
    $router->post('/auth/callback', [
        'uses' => 'AuthController@callback_token',
        'as' => 'bc-auth-callback-token'
    ]);
    $router->get('/auth/callback', [
        'uses' => 'AuthController@callback',
        'as' => 'bc-auth-callback'
    ]);
    $router->get('/auth/load', [
        'uses' => 'AuthController@load',
        'as' => 'bc-auth-load'
    ]);
});

$router->group([
    'prefix' => '/',
    'middleware' => ['open-auth'] //shopify-admin-auth
], function() use ($router) {

    $router->get('/', [
        'uses' => 'IndexController@index',
        'as' => 'app-index'
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

$router->group([
    'prefix' => '/products',
    'namespace' => 'Products',
    'middleware' => ['open-auth'] //shopify-admin-auth
], function() use ($router){
    $router->get('/getallproducts', [
        'uses' => 'ProductsController@getAllProducts',
        'as' => 'admin-products-getall'
    ]);
});

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

