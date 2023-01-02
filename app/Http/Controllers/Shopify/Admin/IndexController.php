<?php

namespace App\Http\Controllers\Shopify\Admin;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class IndexController extends BaseController
{
    public function __construct()
    {
        //
    }
    public function index(Request $request)
    {
        $shopify_products = \App\Shopify\Models\ShopifyProduct::where('store_id',store()->id)->get();
        $bigcommerce_products = \App\BigCommerce\Models\BigCommerceProduct::where('store_id',store()->id)->get();
        return view('shopify.index',[
            'shopify_products' => $shopify_products,
            'bigcommerce_products' => $bigcommerce_products
        ]);
    }
}
