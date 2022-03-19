<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class ShopifyEventAbstract extends Event
{
    use SerializesModels;

    // For a list of valid event topics, visit:
    // https://help.shopify.com/en/api/reference/events/webhook#events
    static protected $shopifyTopic = null;

    public $store;

    public function __construct()
    {
        $this->store = store();
    }

    static public function getShopifyTopic()
    {
        return static::$shopifyTopic;
    }
}
