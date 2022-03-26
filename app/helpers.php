<?php

if (!function_exists('stores')) {
    function stores()
    {
        $key_store_data = \App\DB\StoreAppKey::get();
        $data = array();

        $i = 0;
        foreach ($key_store_data as $store) {
            $data[$store['store_name']] = array('key' => $store['store_api_key'], 'secret' => $store['store_api_secret']);
            $i++;
        }

        return $data;
    }
}
    if (!function_exists('session')) {
        /**
         * Get / set the specified session value.
         *
         * If an array is passed as the key, we will assume you want to set an array of values.
         *
         * @param array|string $key
         * @param mixed $default
         * @return mixed|\Illuminate\Session\Store|\Illuminate\Session\SessionManager
         */
        function session($key = null, $default = null)
        {
            if (is_null($key)) {
                return app('request')->session();
            }
            if (is_array($key)) {
                return app('request')->session()->put($key);
            }
            return app('request')->session()->get($key, $default);
        }


    }
if (!function_exists('getQueryParams')) {
    /**
     * Takes a standard QueryString String and breaks it down in to an associative array
     *
     * @param $queryString
     * @return array
     */
    function getQueryParams($queryString)
    {
        if (empty($queryString)) {
            return [];
        }

        $parameters = [];
        $explodedQueryString = explode('&', $queryString);
        foreach ($explodedQueryString as $string) {
            $values = explode('=', $string);
            $key = $values[0];
            $val = $values[1];
            $parameters[$key] = $val;
        }
        return $parameters;
    }
}

if (!function_exists('authoriseStore')) {
    function authoriseStore(int $storeId)
    {
        try {
            $store = \App\Store::findOrFail($storeId);
        } catch (\Exception $e) {
            return false;
        }

        config([
            'authorised' => true,
            'store_id' => $store->id
        ]);

        // A store has been authorised, ensure all singletons are reset
        config(['store' => false]);
        config(['shopify' => false]);
        config(['ap21' => false]);

        return true;
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('isAuthorised')) {
    function isAuthorised()
    {
        return config('authorised', false);
    }
}

if (!function_exists('store')) {
    function store()
    {
        if (!isAuthorised()) {
            return false;
        }

        // Fetch from Singleton
        $store = config('store', false);

        if (!$store) {
            // Initialise Current Store Singleton
            try {
                $store = \App\Store::findOrFail(config('store_id', false));
            } catch (\Exception $e) {
                return false;
            }

            // Store as a Singleton
            config(['store' => $store]);
        }

        return $store;
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
