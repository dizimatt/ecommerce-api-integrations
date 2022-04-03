<?php

namespace App\Services\Wordpress\to;

use App\Console\ConsoleCommand;
use App\Logger;
use App\ProductSkuMapper;
use Monolog\Handler\StreamHandler;

use App\Config;

class SyncProducts
{

    public static function execute(int $shopifyProductId = null)
    {

        $logger = new Logger('App_Services_OrderSync');
        $loggerFilename = storage_path(
            'logs/App_Services_Wordpress_to_SyncProducts.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        $isCli = app()->runningInConsole();
        $cli = new ConsoleCommand;
        $allDolibarrProducts = dolibarr()->getAllProducts();
        if (count($allDolibarrProducts) != 0) {
            foreach ($allDolibarrProducts as $productToInsert) {
//            $productToInsert = $allDolibarrProducts[0];
//            dump($productToInsert);
                $newWpProduct = [
                    "name" => $productToInsert['label'],
                    "description" => $productToInsert['description'],
                    "sku" => $productToInsert['ref'],
                    "regular_price" => $productToInsert['price'],
                    "tags" => [
                        [
                            "properties" => [
                                "id" => 1,
                                "name" => "dolibar-insert",
                                "slug" => "dbrins"
                            ]
                        ]
                    ]
                ];
                $results = wordpress()->createProduct($newWpProduct);
                if (count($results) != 0) {
                    dump(["new_product" => [
                        "id" => $results['id'],
                        "name" => $results['name'],
                        "slug" => $results['slug'],
                        "sku" => $results['sku']
                    ]]);
                } else {
                    dump(["wordpress create success" => false]);
                }
            }
        } else {
            dump (["found dolibarr product" => false]);
        }

    }

}
