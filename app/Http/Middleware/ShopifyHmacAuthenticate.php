<?php

namespace App\Http\Middleware;

use Closure;
use App\Store;

class ShopifyHmacAuthenticate
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
        $isAuthorised = session('authorised', false);

        if (!$isAuthorised) {
            // Ensure that authentication params are set
            if ($queryString = $request->getQueryString()) {
                $getData = getQueryParams($queryString);
            } else {
                $response = [];

                $errorCode = 403;
                $response['error'] = [
                    'code' => $errorCode,
                    'message' => 'Invalid request'
                ];

                return response()->json($response, $errorCode);
            }

            // Ensure the Shop parameter is passed
            if (!isset($getData['shop']) || empty($getData['shop'])) {
                $response = [];

                $errorCode = 404;
                $response['error'] = [
                    'code' => $errorCode,
                    'message' => 'A Shop name must be provided'
                ];

                return response()->json($response, $errorCode);
            }

            // Ensure the HMAC or Signature exists
            if (!isset($getData['hmac']) && !isset($getData['signature'])) {
                $hmacFound = false;
                if (!empty($getData['hmac'])) {
                    $hmacFound = true;
                }
                if (!empty($getData['signature'])) {
                    $hmacFound = true;
                }

                if (!$hmacFound) {
                    $response = [];

                    $errorCode = 403;
                    $response['error'] = [
                        'code' => $errorCode,
                        'message' => 'A valid HMAC or Signature must be provided'
                    ];

                    return response()->json($response, $errorCode);
                }
            }

            // Extract the HMAC
            $isHmac = false;
            if (isset($getData['hmac'])) {
                $isHmac = true;
                $hmac = $getData['hmac'];
                unset($getData['hmac']);
            } else {
                $hmac = $getData['signature'];
                unset($getData['signature']);

                // A signature is being used, decode the path prefix string
                $getData['path_prefix'] = urldecode($getData['path_prefix']);
            }

            // Sort the GET Request Params
            ksort($getData);

            // Rebuild the query string without the HMAC
            if ($isHmac) {
                $mergedQuery = urldecode(http_build_query($getData));
            } else {
                $mergedQuery = [];
                foreach ($getData as $name => $value) {
                    if (is_array($value)) {
                        $mergedQuery[] = $name . '=' . implode(',', $value);
                    } else {
                        $mergedQuery[] = "$name=$value";
                    }
                }
                $mergedQuery = implode('', $mergedQuery);
            }
            foreach (stores() as $storeName => $storeKeys){
                if ($storeName == $getData['shop']) {
                    $this->_appApiSecret = $storeKeys['secret'];
                }
            }
            // Regenerate comparison signature
            $calHmac = hash_hmac('sha256', $mergedQuery, $this->_appApiSecret);

            // Compare the signatures
            if (!hash_equals($hmac, $calHmac)) {
                $response = [];

                $errorCode = 403;
                $response['error'] = [
                    'code' => $errorCode,
                    'message' => 'The provided HMAC is not valid'
                ];

                return response()->json($response, $errorCode);
            }

            // Check if this is an installation request
            $storeId = session('installation_store_id', false);
            if ($storeId === false) {
                // Get the Store for the validated request
                $store = Store::where('hostname', $getData['shop'])->first();
                if (!isset($store)) {
                    $response = [];

                    $errorCode = 404;
                    $response['error'] = [
                        'code' => $errorCode,
                        'message' => 'Unable to find the corresponding Store for validation'
                    ];

                    return response()->json($response, $errorCode);
                }

                $storeId = $store->id;
            }

            // The user is now Authorized
            session([
                'authorised' => true,
                'store_id' => $storeId
            ]);
            authoriseStore($storeId);
        } else {
            authoriseStore(session('store_id'));
        }

        return $next($request);
    }
}
