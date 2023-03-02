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

        echo "\n\n\n\n";


//        dump(stores());

//        $indeed_api = indeed()->testClientMethod();
//        $appinfo = indeed()->appInfo();
//        dump($appinfo);
//        $shopify_products = shopify()->getAllProducts();
//        dump($shopify_products);


        $shopify_products = shopify()->getAllProducts(["handle" => "test-product-number-1"]);
        if ($shopify_products) {
            dump([
                "product_count" => count($shopify_products),
                "shopify_products" => $shopify_products
            ]);

            foreach ($shopify_products as $shopify_product) {

            }
        }
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
