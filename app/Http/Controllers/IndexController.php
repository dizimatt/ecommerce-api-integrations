<?php

namespace App\Http\Controllers;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class IndexController extends BaseController
{
    public function __construct()
    {
        //
    }
    public function index(Request $request)
    {
//        authoriseStore(1);
        $all_products = bigcommerce()->getProducts();

//        return json_encode(["store" => store()]);
        return view('index', ['store' => store(), 'products' => $all_products]);
    }
}
