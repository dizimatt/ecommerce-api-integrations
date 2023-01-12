<?php

namespace App\Console\Commands;

use App\Console\StoreAbstractCommand;

class SyncBigCommerceToShopify extends StoreAbstractCommand
{
    const PROGRESS_BAR_FORMAT = 'debug';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync:products-from-bc
                                {store_id : The integrations Store ID for the client Store}';

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

//        $shopifyproducts = shopify()->getAllProducts();
        $bigcommerce_products = \App\BigCommerce\Models\BigCommerceProduct::where('store_id',store()->id)
            ->limit(2)
            ->get();

//        \App\Shopify\Models\ShopifyProduct::truncate();

        foreach ($bigcommerce_products as $bigcommerce_product){
            dump(
                [
                    'name' => $bigcommerce_product->name
                ]
            );
            $payload= [
                'title' => $bigcommerce_product->name,
                'body_html' => $bigcommerce_product->name,
                'vendor' => 'Open Resourcing',
                'product_type' => '',
                'status' => 'draft'
            ];
            $result =shopify()->createProduct($payload,false);
            dump($result);
        }


        $time_end = microtime(true);
        $execution_time = $time_end - $time_start;

        $this->info("Tester::handle() COMPLETED in {$execution_time}");

        echo "\n\n";

        return;
    }
}
