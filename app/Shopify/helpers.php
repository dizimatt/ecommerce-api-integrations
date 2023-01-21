<?php
if (!function_exists('shopify')) {
    function shopify()
    {
        // Fetch from Singleton
        $shopify = config('shopify', false);

        if (!$shopify) {
            // Initialise Shopify API Client Singleton
            // Initialise Current Store Singleton
            try {
                $shopify_store = \App\Shopify\Models\ShopifyStore::findOrFail(store()->id);
            } catch (\Exception $e) {
                return false;
            }

            $shopify = new \App\Shopify\Client();

            $shopify->setShop($shopify_store->hostname);
            $shopify->setAccessToken($shopify_store->access_token);
            $shopify->setVersion(env('SHOPIFY_APP_API_VERSION'));
            $shopify->startSession();

            // Store as a Singleton
            config(['shopify' => $shopify]);
        }

        return $shopify;
    }
    if (!function_exists('shopifyStores')) {
        function shopifyStores()
        {
            $key_store_data = \App\Shopify\Models\ShopifyAppKey::get();
            $data = array();

            $i = 0;
            foreach ($key_store_data as $store) {
                $data[$store['store_name']] = array('key' => $store['store_api_key'], 'secret' => $store['store_api_secret']);
                $i++;
            }

            return $data;
        }
    }

    if (!function_exists('setShopifyTopics')) {
        function setShopifyTopics(array $shopifyTopics)
        {
            config(['shopify_topics' => $shopifyTopics]);
        }
    }

    if (!function_exists('getShopifyTopics')) {
        function getShopifyTopics()
        {
            return config('shopify_topics', false);
        }
    }

}
