<?php

namespace App\Http\Controllers\Shopify;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class ProductsController extends BaseController
{
    public function __construct()
    {
        //
    }

    public function getProducts(Request $request)
    {
        $products = shopify()->getAllProducts(); //["ids" => "6632857895096"]);
        $dolibarr_product = dolibarr()->getAllProducts(); //getProduct(3469);
        return response()->json([
            "success" => true,
            "products" => $products,
            "dolibarr_product" => $dolibarr_product
        ]);

    }
}
