<?php

namespace App\Console\Commands;

use App\Shopify\Console\AbstractCommand;

use App\Services\WebhookInit;

class ShopifyWebhookInit extends AbstractCommand
{
    const PROGRESS_BAR_FORMAT = 'debug';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:shopify:webhook-init
                                {store_id : The integrations Store ID for the Shopify Store}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialises Webhook Maintenance for Shopify Stores';

    /**
     * Execute the console command.
     *
     * @param  \App\DripEmailer $drip
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        WebhookInit::manage();
    }
}
