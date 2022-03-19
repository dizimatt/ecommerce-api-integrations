<?php

namespace App\Listeners\Shopify;

use App\Events\Shopify\OrdersCreate;
use App\Services\Orders;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Logger;
use Monolog\Handler\StreamHandler;

class SyncOrder implements ShouldQueue
{
    public $connection = 'database';
    public $queue = 'shopify_webhook';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(OrdersCreate $event)
    {
        // This must not be removed as it sets the store context for the listener
        $store = $event->store;
        if (!$store) {
            throw new \Exception('The event object did not contain a valid store context');
        }

        // The store is now Authorized
        authoriseStore($store->id);

        $logger = self::getLogger();

        $debugObj = [
            'store_singleton' => store()->toArray(),
            'order' => $event->order
        ];
        $logger->debug('Webhook Event Listener Hit', $debugObj);

        // Disbale syncing on webhook temp fro debugging
//        if (store()->hostname == 'bec-and-bridge-us.myshopify.com') {
//            return;
//        }

        Orders::SyncOrders($event->order['id']);
    }

    static protected function getLogger()
    {
        $loggerName = str_replace('\\', '_', get_called_class());

        $logger = new Logger($loggerName);
        $loggerFilename = storage_path(
            "logs/{$loggerName}.log"
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        return $logger;
    }
}
