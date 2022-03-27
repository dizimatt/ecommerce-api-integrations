<?php

namespace App\Shopify\Console;

use App\Shopify\Models\ShopifyStore;
use Illuminate\Console\Command;

abstract class AbstractCommand extends Command
{
    public function handle()
    {
        try {
            $store = ShopifyStore::findOrFail($this->argument('store_id'));
        } catch (\Exception $e) {
            $this->line('');
            $this->error("The given Store ID was not found.");
            $this->line('');
            exit;
        }

        authoriseStore($store->id);
    }

}
