<?php

namespace App\Http\Controllers\Shopify\Admin;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Logger;
use Monolog\Handler\StreamHandler;
use App\Shopify\Models\ShopifyAppKey;

class IndexController extends BaseController
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

//        $shopify_products = \App\Shopify\Models\ShopifyProduct::where('store_id',store()->id)->get();
//        $bigcommerce_products = \App\BigCommerce\Models\BigCommerceProduct::where('store_id',store()->id)->get();

        $all_params = $request->all();

            return view('shopify.admin.index', [
                'shop' => $all_params['shop'],
                'all_params' => $all_params
//                'shopify_products' => $shopify_products,
//                'bigcommerce_products' => $bigcommerce_products
            ]);

    }
}
