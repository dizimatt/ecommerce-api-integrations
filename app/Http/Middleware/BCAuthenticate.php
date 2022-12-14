<?php

namespace App\Http\Middleware;

use App\BigCommerce\Models\BigCommerceStore;
use Closure;

class BCAuthenticate
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

        if (!isset($getData['store']) || empty($getData['store'])) {
            $response = [];

            $errorCode = 400;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'No store Param was provided'
            ];

            return response()->json($response, $errorCode);
        }

        try {
            $store = BigCommerceStore::where('domain', $getData['store'])->firstOrFail();
        } catch (\Exception $e) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'The provided Store Domain was not recognised'
            ];

            return response()->json($response, $errorCode);
        }

        // The user is now Authorized
        session([
            'authorised' => true,
            'store_id' => $store->id
        ]);
        authoriseStore($store->id);

        return $next($request);
    }
}
