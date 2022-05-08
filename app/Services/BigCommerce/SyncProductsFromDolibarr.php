<?php

namespace App\Services\BigCommerce;

use App\Config;
use App\Console\ConsoleCommand;
use App\Logger;
use App\ProductSkuMapper;
use Monolog\Handler\StreamHandler;
use function PHPUnit\Framework\isNull;

class SyncProductsFromDolibarr
{

    public static function execute(int $dolibarrProductId = null)
    {

        $logger = new Logger('App_Services_From_Dolibarr_to_BC');
        $loggerFilename = storage_path(
            'logs/App_Services_From_Dolibarr_to_BC.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        $isCli = app()->runningInConsole();
        $cli = new ConsoleCommand;

        $dolibarr_products = dolibarr()->getAllProducts();

        $i = 0;
        foreach ($dolibarr_products as $k => $product) {
            $payload = [
                "name" => $product["label"],
                "type" => "physical",
                "sku" => $product['ref'],
                "description" => $product['description'],
                "weight" => (isNull($product['weight'])?0:$product['weight']),
                "price" => $product["price"],
                "cost_price" => (isNull($product["cost_price"])?0:$product["cost_price"]),
                "retail_price" => $product["price"],
                "sale_price" => $product["price_min"],
                "page_title" => $product["label"],
                "categories" => [
                    24
                ],
                "weight" => 0
            ];

            $result = bigcommerce()->createProduct($payload);
            if ($result['success'] === true){
                $message = json_decode($result['message'], true);
                $product_id = $message['data']['id'];
                dump(["created BC product" => $product_id]);

                $result = bigcommerce()->assignProductToChannel($product_id,1);
                if ($result['success'] === true){
                    dump([
                        "channel_association" => [
                            "product_id" => $product_id,
                            "channel_id" => 1
                        ]
                    ]);
                } else {
                    $logger->warning("failed to import dolibarr product into BC : " . print_r($result, true));
                }

            } else {
                if ($result['status_code'] == 409){
                    $logger->warning("this product already exists in BC! ");
                }
                if (isset($result['body'])){
                    $body_obj = json_decode($result['body']);
                    $logger->warning(json_encode($body_obj,JSON_PRETTY_PRINT));
                }
            }
            if (++$i >= 5) break;
        }
        /*
        dd(["dolibarr" => dolibarr()->getAllProducts(),
            "shopify" => shopify()->getAllProducts($filter)]);
        */
    }

}
