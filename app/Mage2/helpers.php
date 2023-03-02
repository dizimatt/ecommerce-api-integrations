<?php
if (!function_exists('mage2')) {
    function mage2()
    {
        // Fetch from Singleton
        $mage2 = config('mage2', false);

        if (!$mage2) {
            // Initialise Current Store Singleton
            try {
                $mage2_store = \App\Mage2\Models\Mage2Store::findOrFail(store()->id);
            } catch (\Exception $e) {
                dump([
                   "error querying the mage2stores:" . $e->getMessage()
                ]);
                return false;
            }

            // Initialise Shopify API Client Singleton
            $mage2 = new \App\Mage2\Client(
                $mage2_store->api_url,
                $mage2_store->access_token
            );
            // Store as a Singleton
            config(['mage2' => $mage2]);
        }

        return $mage2;
    }
}
