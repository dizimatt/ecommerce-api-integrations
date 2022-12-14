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

    public function testProducts(Request $request)
    {
        /*
        $queryParams = [
        ];
        $queryString = urldecode(http_build_query($queryParams));
*/
        // Reset any Session data and set the installation Store to session
        session()->flush();


        try {

            $bc_products = bigcommerce()->getProducts();
            $return_html = "<html><body><table><tr><th>id</th><th>Name</th><th>Price</th></tr>";
            foreach ($bc_products['data'] as $bc_product) {
                $product_edit_url = "/manage/products/edit/{$bc_product['id']}";
                $return_html .= "<tr><td><a href=\"{$product_edit_url}\">{$bc_product['id']}</a></td><td>{$bc_product['name']}</td><td>{$bc_product['price']}</td></tr>";
            }
            $return_html .= "</table><hr />"
                ."<a href=\"/bigcommerce/app/auth/load?store=".$request->store."\">back to App Home</a>"
                ."</body></html>";
            return $return_html;

        } catch (\Exception $e) {
            return "exception: " . $e->getMessage();
        }

    }

}
