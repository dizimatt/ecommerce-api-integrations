<?php

namespace App\Http\Middleware;

use App\Shopify\Models\ShopifyStore;
use App\Models\Store;
use Closure;

class OpenAuthenticate
{
    public function handle($request, Closure $next)
    {
        // Ensure that there are query params passed
        if ($queryString = $request->getQueryString()) {
            $getData = getQueryParams($queryString);
        } else {
            $response = [];

            $errorCode = 400;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'Invalid request'
            ];

            return response()->json($response, $errorCode);
        }

        if (!isset($getData['shop']) || empty($getData['shop'])) {
            $response = [];

            $errorCode = 400;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'No Shop Param was provided'
            ];

            return response()->json($response, $errorCode);
        }

        try {
            $store = ShopifyStore::where('hostname', $getData['shop'])->firstOrFail();
//            $store = Store::findOrFail($getData['shop']);
        } catch (\Exception $e) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'The provided Shop Hostname was not recognised'
            ];

            return response()->json($response, $errorCode);
        }

        // The user is now Authorized
        session([
            'authorised' => true,
            'store_id' => $store->id
        ]);
        authoriseStore($store->id);

        /*
        return response()->json([
            "success" => true,
            "shop" => $getData['shop'],
            "next" => $next,
            "request" => $request
        ]);
        */
        return $next($request);
    }
}
