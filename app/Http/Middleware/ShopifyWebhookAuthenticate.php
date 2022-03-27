<?php

namespace App\Http\Middleware;

use App\Shopify\Models\StoreAppKey;
use App\Store;
use Closure;

class ShopifyWebhookAuthenticate
{
    private $_appApiSecret;

    public function __construct()
    {
        $this->_appApiSecret = env('SHOPIFY_APP_API_SECRET');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $requestHeaders = $request->headers->all();
        $requestContent = $request->getContent();

        // Retrieve the store
        if (!isset($requestHeaders['x-shopify-shop-domain'][0]) || empty($requestHeaders['x-shopify-shop-domain'][0])) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'A Shop Hostname was not provided'
            ];

            $debugObj = $response;
            $debugObj['request_headers'] = $requestHeaders;
            $debugObj['request_content'] = $requestContent;

            return response()->json($response, $errorCode);
        }

        $shopifyHostname = $requestHeaders['x-shopify-shop-domain'][0];

        // Check to see if there is an App Key specific for this Host Name
        if ($appKey = StoreAppKey::where('store_name', $shopifyHostname)->first()) {
            $this->_appApiSecret = $appKey->store_api_secret;
        }

        // Ensure the HMAC parameter is passed
        if (!isset($requestHeaders['x-shopify-hmac-sha256'][0]) || empty($requestHeaders['x-shopify-hmac-sha256'][0])) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'The HMAC header must be provided'
            ];

            $debugObj = $response;
            $debugObj['request_headers'] = $requestHeaders;
            $debugObj['request_content'] = $requestContent;

            return response()->json($response, $errorCode);
        }

        $hmacHeader = $requestHeaders['x-shopify-hmac-sha256'][0];

        // Ensure Data was sent with the request body
        $data = $request->getContent();
        if (!isset($data) || empty($data)) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'The Request Body was empty'
            ];

            $debugObj = $response;
            $debugObj['request_headers'] = $requestHeaders;
            $debugObj['request_content'] = $requestContent;

            return response()->json($response, $errorCode);
        }

        // Calculate the HMAC
        $hmacCalculated = base64_encode(
            hash_hmac('sha256', $data, $this->_appApiSecret, true)
        );

        // Compare the Calculated HMAC with the given HMAC
        if (!hash_equals($hmacHeader, $hmacCalculated)) {
            $response = [];

            $errorCode = 403;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'The provided HMAC is not valid'
            ];

            $debugObj = $response;
            $debugObj['request_headers'] = $requestHeaders;
            $debugObj['request_content'] = $requestContent;

            return response()->json($response, $errorCode);
        }

        // Retrieve the store
        if (!isset($requestHeaders['x-shopify-shop-domain'][0]) || empty($requestHeaders['x-shopify-shop-domain'][0])) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'A Shop Hostname was not provided'
            ];

            return response()->json($response, $errorCode);
        }

        $shopifyHostname = $requestHeaders['x-shopify-shop-domain'][0];

        $store = Store::where('hostname', $shopifyHostname)->first();
        if (!isset($store)) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'Unable to find the corresponding Store'
            ];

            $debugObj = $response;
            $debugObj['request_headers'] = $requestHeaders;
            $debugObj['request_content'] = $requestContent;

            return response()->json($response, $errorCode);
        }

        $storeId = $store->id;

        // The store is now Authorized
        authoriseStore($storeId);

        return $next($request);
    }
}
