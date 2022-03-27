<?php

namespace App\Console\Commands;

use App\Shopify\Console\AbstractCommand;

class SyncToDolibarr extends AbstractCommand
{
    const PROGRESS_BAR_FORMAT = 'debug';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dolibarr:to:syncproducts
                                {store_id : The integrations Store ID for the Shopify Store}
                                {product_id? : The integrations Store ID for the Shopify Store}';

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
        $this->info('SyncToDolibarr::handle() EXECUTED');
        echo "\n";

        // ----------------------------------------------------------------------
        // Test code here
        // ----------------------------------------------------------------------

//        \App\Services\Dolibarr\to\SyncProducts::execute('3885567705185');
        \App\Services\Dolibarr\to\SyncProducts::execute();
        return;
    }
}
