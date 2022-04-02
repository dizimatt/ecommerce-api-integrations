<?php

namespace App\Console\Commands;

use App\Shopify\Console\AbstractCommand;

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

        echo "\n\n";

        wordpress()->donothing();

//        $dolibarr_attributes = dolibarr()->fetchAllDolibarrVariantAttributes();
//        dump($dolibarr_attributes);

/*        $products = shopify()->getAllProducts();
        if (is_array($products)) {
            dump(["count" => count($products),
                "first product" => $products[0]]);
        } else {
            dump(["products fetch result" => $products]);
        }
        */
        $time_end = microtime(true);
        $execution_time = $time_end - $time_start;

        $this->info("Tester::handle() COMPLETED in {$execution_time}");

        echo "\n\n";

        return;
    }
}
