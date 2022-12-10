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
        return "<h1>Welcome to Open Resourcing Shopify App</h1>";
    }
    public function listAllProducts(Request $request)
    {
        $products = shopify()->getAllProducts(); //["ids" => "6632857895096"]);
//        $dolibarr_product = dolibarr()->getAllProducts(); //getProduct(3469);
        $html = '<table><tr><th>id</th><th>image</th><th>title</th></tr>';
        foreach ($products as $product){
            if ($product) {
                $html .= "<tr><td>{$product['id']}</td>".
                "<td>".($product['image']?"<img src='{$product['image']['src']}' width='250' />":"")."</td>".
                "<td>{$product['title']}</td></tr>";
            }
        }
        $html .= '</table>';
        return $html;
        return response()->json([
            "success" => true,
            "products" => $products,
//            "dolibarr_product" => $dolibarr_product
        ]);

    }
}
