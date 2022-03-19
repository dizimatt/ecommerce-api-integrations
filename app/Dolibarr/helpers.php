<?php
if (!function_exists('dolibarr')) {
    function dolibarr()
    {
        // Fetch from Singleton
        $dolibarr = config('dolibarr', false);

        if (!$dolibarr) {
            // Initialise Current Store Singleton
            try {
                $dolibarr_account = \App\DolibarrAccount::findOrFail(store()->id);
            } catch (\Exception $e) {
                return false;
            }

            // Initialise Shopify API Client Singleton
            $dolibarr = new \App\Dolibarr\Client(
                $dolibarr_account->sandbox_url,
                $dolibarr_account->sandbox_login,
                $dolibarr_account->sandbox_password,
                $dolibarr_account->sandbox_token,
                false
            );
            // Store as a Singleton
            config(['dolibarr' => $dolibarr]);
        }

        return $dolibarr;
    }
}
