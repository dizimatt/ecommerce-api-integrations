<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\Tester',
        'App\Console\Commands\RegisterIndeedAPI',
        'App\Console\Commands\IndexBigCommerceProducts',
        'App\Console\Commands\IndexShopifyProducts',
        'App\Console\Commands\SyncToDolibarr',
        'App\Console\Commands\SyncToWordpress',
        'App\Console\Commands\ShopifyWebhookInit',
        'App\Console\Commands\SyncDolibarrToBigCommerce',
        'App\Console\Commands\SyncBigCommerceToShopify',
        'App\Console\Commands\Mage2PopulateProducts'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
