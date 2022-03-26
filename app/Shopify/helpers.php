<?php
if (!function_exists('shopify')) {
    function shopify()
    {
        // Fetch from Singleton
        $shopify = config('shopify', false);

        if (!$shopify) {
            // Initialise Shopify API Client Singleton
            $shopify = new \App\Shopify\Client();

            $shopify->setShop(store()->hostname);
            $shopify->setAccessToken(store()->access_token);
            $shopify->setVersion(env('SHOPIFY_APP_API_VERSION'));
            $shopify->startSession();

            // Store as a Singleton
            config(['shopify' => $shopify]);
        }

        return $shopify;
    }
}
