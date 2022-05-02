<?php
if (!function_exists('bigcommerce')) {
    function bigcommerce()
    {
        // Fetch from Singleton
        $bigcommerce = config('bigcommerce', false);

        if (!$bigcommerce) {
            // Initialise Current Store Singleton
            try {
                $bigcommerce_store = \App\BigCommerce\Models\BigCommerceStore::findOrFail(store()->id);
            } catch (\Exception $e) {
                return false;
            }

            // Initialise Shopify API Client Singleton
            $bigcommerce = new \App\BigCommerce\Client(
                $bigcommerce_store->api_url,
                $bigcommerce_store->api_token
            );
            // Store as a Singleton
            config(['bigcommerce' => $bigcommerce]);
        }

        return $bigcommerce;
    }
}
