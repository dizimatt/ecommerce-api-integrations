<?php
if (!function_exists('indeed')) {
    function indeed()
    {
        // Fetch from Singleton
        $indeed = config('indeed', false);

        if (!$indeed) {
            // Initialise Current Store Singleton
            $indeed_api = \App\Indeed\Models\IndeedAPI::find(store()->id);

            if (!$indeed_api){
                // still need to return empty client - as it's needed for init
                $indeed = new \App\Indeed\Client(
                    'UNSET',
                    '',
                    ''
                );
            } else {
                // Initialise Shopify API Client Singleton
                $indeed = new \App\Indeed\Client(
                    $indeed_api->api_url,
                    $indeed_api->name,
                    $indeed_api->access_token
                );
            }

            // Store as a Singleton
            config(['indeed' => $indeed]);
        }

        return $indeed;
    }
}
