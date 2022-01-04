<?php

namespace App\Console\Commands;

use App\Console\AbstractCommand;

class Tester extends AbstractCommand
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


        $products = shopify()->getAllProducts();
        foreach ($products as $product) {
            $this->line("");
            $this->info("id: " . $product['id']
                . ", title: ". $product['title']
                . ", sku: ". $product['variants'][0]['sku']
                . ",tags: ". $product['tags']
                . ", price: " . $product['variants'][0]['price']);
        }
            /*
    //        $shopifyStore = shopify()->getShop();
            $orders = shopify()->getAllNonSyncedOrders();
            foreach ($orders as $order){
                $this->line("");
                $this->info("id: ". $order['id'] . ", order number: " . $order['order_number'] . ", tags:" . $order['tags']);
            }
            */


        // ----------------------------------------------------------------------
        // Test code finished
        // ----------------------------------------------------------------------

        echo "\n\n";

        $time_end = microtime(true);
        $execution_time = $time_end - $time_start;

        $this->info("Tester::handle() COMPLETED in {$execution_time}");

        echo "\n\n";

        return;
    }
}