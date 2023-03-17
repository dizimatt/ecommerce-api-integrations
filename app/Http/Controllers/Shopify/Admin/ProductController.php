<?php

namespace App\Http\Controllers\Shopify\Admin;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Logger;
use Monolog\Handler\StreamHandler;
use App\Shopify\Models\ShopifyAppKey;

class ProductController extends BaseController
{
    public function __construct()
    {
        //
    }


    public function index(Request $request)
    {
        $logger = new Logger('admin_index');
        $loggerFilename = storage_path(
            'logs/admin_index.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        $shopify_products = \App\Shopify\Models\ShopifyProduct::where('store_id',store()->id)->get()->toArray();

            return view('shopify.admin.products', [
                'shopify_products' => $shopify_products
            ]);

    }
}
