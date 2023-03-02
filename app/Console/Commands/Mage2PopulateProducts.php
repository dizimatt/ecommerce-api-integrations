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

/*
        $attribute_sets = mage2()->fetchAttributeSets();
        dump([
            "results" => $attribute_sets
        ]);
*/
/*
        $attributes = mage2()->fetchAttributes(1);
        dump([
            "results" => $attributes
        ]);
*/

        $categories = mage2()->fetchAllCategories();
        dump([
            "results" => $categories
        ]);
/*
        $shopify_products = shopify()->getAllProducts(["handle" => "test-product-number-1"]);
        if ($shopify_products) {
            dump([
                "product_count" => count($shopify_products),
                "shopify_products" => $shopify_products
            ]);

            foreach ($shopify_products as $shopify_product) {
*/
                $payload = [
                    "product" => [
                        "sku" => "MS-Champ",
                        "name" => "Champ Tee",
                        "attribute_set_id" => 0,
                        "status" => 1,
                        "visibility" => 4,
                        "type_id" => "configurable",
                        "weight" => "0.5",
                        "extension_attributes" => [
                            "category_links" => [
                                [
                                    "position" => 0,
                                    "category_id" => "2"
                                ]
                            ]
                        ],
                        /*
                        "custom_attributes" => [
                            [
                                "attribute_code" => "description",
                                "value" => "The Champ Tee keeps you cool and dry while you do your thing. Let everyone know who you are by adding your name on the back for only $10."
                            ],
                            [
                                "attribute_code" => "tax_class_id",
                                "value" => "2"
                            ],
                            [
                                "attribute_code" => "material",
                                "value" => "148"
                            ],
                            [
                                "attribute_code" => "pattern",
                                "value" => "196"
                            ],
                            [
                                "attribute_code" => "color",
                                "value" => "52"
                            ]
                        ]
*/
                    ]
                ];
  //              dump([
  //                  "will insert:" => $payload
  //              ]);

                $result = mage2()->createProduct($payload);
                dump([
                    "insert result" => $result
                ]);
                /*
            }
        }
                */

        /*
                 $mage2_results = mage2()->fetchProductsFromFilter([
                     "searchCriteria[filter_groups][0][filters][0][field]" => "sku",
                     "searchCriteria[filter_groups][0][filters][0][value]" =>  "null",
                     "searchCriteria[filter_groups][0][filters][0][condition_type]" => "neq"
                 ]);
                 dump([
                     "results" => $mage2_results
                 ]);
        */

//        $bcproducts = bigcommerce()->getProducts();
//        dump($bcproducts);

//        $dolibarr_product = dolibarr()->getAllProducts();
//        dump($dolibarr_product);

            $time_end = microtime(true);
            $execution_time = $time_end - $time_start;

            $this->info("Tester::handle() COMPLETED in {$execution_time}");

            echo "\n\n";

            return;
        }
}
