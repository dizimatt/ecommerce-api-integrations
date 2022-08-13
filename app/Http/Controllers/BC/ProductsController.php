<?php

namespace App\Http\Controllers\BC;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as Guzzle;
use App\BigCommerce\Models\BigCommerceStore;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Handler\StreamHandler;
use mysql_xdevapi\Exception;
use App\Logger;


class ProductsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    /*
    public function __construct()
    {
    }
    */

    public function getProducts(Request $request)
    {
        $logger = new Logger('bc-products');
        $loggerFilename = storage_path(
            'logs/bc-products.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);
        $logger->info("called getProducts request");
        $all_products = bigcommerce()->getProducts();

//        return json_encode(["store" => store()]);
        return view('index', ['store' => store(), 'products' => $all_products]);

    }

}
