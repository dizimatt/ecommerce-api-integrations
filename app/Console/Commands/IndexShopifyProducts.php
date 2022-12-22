<?php

namespace App\Console\Commands;

use App\Console\StoreAbstractCommand;

class IndexShopifyProducts extends StoreAbstractCommand
{
    const PROGRESS_BAR_FORMAT = 'debug';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:index:products
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

        $shopifyproducts = shopify()->getAllProducts();
        \App\Shopify\Models\ShopifyProduct::truncate();

        foreach ($shopifyproducts as $shopify_product){
            $shopifyProduct = new \App\Shopify\Models\ShopifyProduct();
            $shopifyProduct->store_id = store()->id;
            $shopifyProduct->title = $shopify_product['title'];
            $shopifyProduct->handle = $shopify_product['handle'];
            $shopifyProduct->status = $shopify_product['status'];
            $shopifyProduct->published_scope = $shopify_product['published_scope'];
            $shopifyProduct->admin_graphql_api_id = $shopify_product['admin_graphql_api_id'];
            $shopifyProduct->shopify_created_at = $shopify_product['created_at'];
            $shopifyProduct->shopify_updated_at = $shopify_product['updated_at'];
            $shopifyProduct->shopify_published_at = $shopify_product['published_at'];
            try {
                $shopifyProduct->saveOrFail();
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
