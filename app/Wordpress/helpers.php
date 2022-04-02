<?php
if (!function_exists('wordpress')) {
    function wordpress()
    {
        // Fetch from Singleton
        $wordpress = config('wordpress', false);

        if (!$wordpress) {
            // Initialise Current Store Singleton
            try {
                $wordpress_account = \App\Wordpress\Models\WordpressAccount::where("store_id",store()->id)->firstOrFail();
            } catch (\Exception $e) {
                return false;
            }

            // Initialise Shopify API Client Singleton
            $wordpress = new \App\Wordpress\Client(
                $wordpress_account->sandbox_url,
                $wordpress_account->sandbox_login,
                $wordpress_account->sandbox_password,
                $wordpress_account->sandbox_key,
                $wordpress_account->sandbox_secret,
                false
            );
            // Store as a Singleton
            config(['wordpress' => $wordpress]);
        }

        return $wordpress;
    }
}
