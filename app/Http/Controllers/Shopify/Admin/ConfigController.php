<?php

namespace App\Http\Controllers\Shopify\Admin;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Logger;
use Monolog\Handler\StreamHandler;

class ConfigController
{
    public function index(Request $request)
    {
        $logger = new Logger('config_index');
        $loggerFilename = storage_path(
            'logs/config_index.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        $shopify_store = \App\Shopify\Models\ShopifyStore::where('id', store()->id)->first()->toArray();
        $all_params = $request->all();

        return view('shopify.admin.config', [
            'shopify_store' => $shopify_store
        ]);

    }

}
