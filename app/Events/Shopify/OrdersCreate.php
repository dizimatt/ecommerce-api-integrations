<?php

namespace App\Events\Shopify;

use App\Events\ShopifyEventAbstract;

class OrdersCreate extends ShopifyEventAbstract
{
    // For a list of valid event topics, visit:
    // https://help.shopify.com/en/api/reference/events/webhook#events
    static protected $shopifyTopic = 'orders/create';

    public $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $order)
    {
        parent::__construct();
        $this->order = $order;
    }
}
