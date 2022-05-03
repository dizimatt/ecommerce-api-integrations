<?php

namespace App\Console\Commands;

use App\Console\StoreAbstractCommand;
use function PHPUnit\Framework\isNull;

class Tester extends StoreAbstractCommand
{
    const PROGRESS_BAR_FORMAT = 'debug';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test
                                {store_id : The integrations Store ID for the Shopify Store}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Function Test command';

    /**
     * Execute the console command.
     *
     * @param  \App\DripEmailer $drip
     * @return mixed
     */
    public function handle()
    {
        $time_start = microtime(true);

        parent::handle();

        echo "\n\n";
        $this->info('Tester::handle() EXECUTED');
        echo "\n";

        // ----------------------------------------------------------------------
        // Test code here
        // ----------------------------------------------------------------------

        echo "\n\n";

//        $product = shopify()->getAllProducts(["ids" => "6632857895096"]);
//        $product = shopify()->getProduct("6632857895096");

//        dump (["BC_products" => bigcommerce()->getProduct(80)]);
//        dump (["BC_products" => bigcommerce()->getProducts()]);

        $dolibarr_products = dolibarr()->getAllProducts();
//        dd(["dolibarr_product" => $dolibarr_products[0]]);

//        dd(["dolibarr_count" => count($dolibarr_products)]);

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
                    dump(["failed to create BC product" => $result]);
                }

            } else {
                dump(["failed to create BC product" => $result]);
            }
            if (++$i >= 5) break;
        }


        dd();

        $dolibarr_product = dolibarr()->getProduct(3469);
        dump($dolibarr_product);

        $time_end = microtime(true);
        $execution_time = $time_end - $time_start;

        $this->info("Tester::handle() COMPLETED in {$execution_time}");

        echo "\n\n";

        return;
    }
}
