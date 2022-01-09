<?php

namespace App\Providers;

class ShopifyEventServiceProvider extends EventServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
//    protected $listen = [];
    protected $listen = [
        /*'App\Events\Shopify\ProductsCreate' => [
            'App\Listeners\Shopify\IndexProduct',
        ],
        'App\Events\Shopify\ProductsUpdate' => [
            'App\Listeners\Shopify\IndexProduct',
        ],
        'App\Events\Shopify\ProductsDelete' => [
            'App\Listeners\Shopify\IndexProductDelete',
        ],
        'App\Events\Shopify\CustomersUpdate' => [
            'App\Listeners\Shopify\SyncCustomer',
        ],
        'App\Events\Shopify\RefundsCreate' => [
            'App\Listeners\Shopify\SyncReturn',
        ],
        */
        'App\Events\Shopify\OrdersCreate' => [
            'App\Listeners\Shopify\SyncOrder'
        ]

    ];

    public function boot()
    {
        $topics = [];

        foreach ($this->listen as $eventClassName => $listenerClassNames) {
            $topics[] = call_user_func("{$eventClassName}::getShopifyTopic");
        }

        setShopifyTopics($topics);

        return parent::boot();
    }
}
