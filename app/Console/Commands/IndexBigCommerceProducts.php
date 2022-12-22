<?php

namespace App\Console\Commands;

use App\Console\StoreAbstractCommand;

class IndexBigCommerceProducts extends StoreAbstractCommand
{
    const PROGRESS_BAR_FORMAT = 'debug';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bigcommerce:index:products
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
//        $shopify_products = shopify()->getAllProducts();
//        dump($shopify_products);

        $bcproducts = bigcommerce()->getProducts();
        \App\BigCommerce\Models\BigCommerceProduct::truncate();

        foreach ($bcproducts['data'] as $bc_product){
            $bigcommerceProduct = new \App\BigCommerce\Models\BigCommerceProduct();
            $bigcommerceProduct->store_id = store()->id;
            $bigcommerceProduct->name = $bc_product['name'];
            $bigcommerceProduct->sku = $bc_product['sku'];
            $bigcommerceProduct->retail_price = $bc_product['retail_price'];
            $bigcommerceProduct->is_visible = $bc_product['is_visible'];
            $bigcommerceProduct->date_created = $bc_product['date_created'];
            $bigcommerceProduct->date_modified = $bc_product['date_modified'];
            try {
                $bigcommerceProduct->saveOrFail();
            } catch(\Exception $e){
                $this->info('failed to insert the row - possible a duplicate error - see following exception: ' . $e->getMessage());
            }
        }


        $time_end = microtime(true);
        $execution_time = $time_end - $time_start;

        $this->info("Tester::handle() COMPLETED in {$execution_time}");

        echo "\n\n";

        return;
    }
}
