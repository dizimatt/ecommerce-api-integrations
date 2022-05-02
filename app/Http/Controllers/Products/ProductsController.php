<?php

namespace App\Http\Controllers\Products;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class ProductsController extends BaseController
{
    public function __construct()
    {
        //
    }

    public function getAllProducts(Request $request)
    {
        /*
        if (!isset($request->shop) || empty($request->shop)) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'A Shop name must be provided to be able to install'
            ];

            return response()->json($response, $errorCode);
        }
        */
        $products = shopify()->getAllProducts(); //["ids" => "6632857895096"]);
        $dolibarr_product = dolibarr()->getAllProducts(); //getProduct(3469);
        return response()->json([
            "success" => true,
            "products" => $products,
            "dolibarr_product" => $dolibarr_product
        ]);

    }
}
