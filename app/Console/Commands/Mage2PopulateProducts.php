<?php

namespace App\Console\Commands;

use App\Console\StoreAbstractCommand;
use function PHPUnit\Framework\isNull;

class Mage2PopulateProducts extends StoreAbstractCommand
{
    const PROGRESS_BAR_FORMAT = 'debug';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mage2Populate
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

        echo "\n\n\n\n";
        $shopify_products = shopify()->getAllProducts([]); //["handle" => "test-product-number-1"]);
        if (isset($shopify_products['errors'])){
            dd([
                "failed message" => $shopify_products['errors']
            ]);
        }
        if ($shopify_products) {
            dump([
                "product_count" => count($shopify_products),
                "shopify_products" => $shopify_products
            ]);

            foreach ($shopify_products as $shopify_product) {
                $payload = [
                    "product" => [
                        "sku" => ($shopify_product['variants'][0]['sku']?$shopify_product['variants'][0]['sku']
                            : $shopify_product['handle'] . '-' . $shopify_product['variants'][0]['position']),
                        "name" => $shopify_product['title'],
                        "attribute_set_id" => 4,
                        "price" => (real)$shopify_product['variants'][0]['price'],
                        "status" => 1,
                        "visibility" => 1,
                        "type_id" => "simple",
                        "weight" => "500",
                        "extension_attributes" => [
                            "category_links" => [
                                [
                                    "position" => 0,
                                    "category_id" => "2"
                                ]
                            ],
                            "stock_item" => [
                                "qty" => $shopify_product['variants'][0]['inventory_quantity'],
                                "is_in_stock" => true
                            ]
                        ],
/*                        "custom_attributes" => [
                            [
                                "attribute_code" => "pattern",
                                "value" => "1960"
                            ],
                            [
                                "attribute_code" => "color",
                                "value" => "45"
                            ],
                            [
                                "attribute_code" => "size",
                                "value" => "168"
                            ]
                        ]
*/
                    ]
                ];
                echo " product_as_json_string : " . json_encode($payload, JSON_PRETTY_PRINT);

//            dd(["skipping creation"]);

                $result = mage2()->createProduct($payload);
                dump([
                    "insert result" => $result
                ]);

//                break;
            }
        }


            $time_end = microtime(true);
            $execution_time = $time_end - $time_start;

            $this->info("Tester::handle() COMPLETED in {$execution_time}");

            echo "\n\n";

            return;
        }
}
