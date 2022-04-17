<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Shopify\Models\ShopifyStore;


class InstallationController extends Controller
{
    private $_appApiName;
    private $_appApiKey;
    private $_appApiSecretKey;
    private $_appScopes;
    private $_appNonceLife;
    private $_appUrl;

    private $_httpProtocol = 'https://';
    private $_authUri = '/admin/oauth/authorize';
    private $_shopifyHostnameSuffix = '.myshopify.com';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_appApiName = env('SHOPIFY_APP_NAME');
        $this->_appApiKey = env('SHOPIFY_APP_API_KEY');
        $this->_appApiSecretKey = env('SHOPIFY_APP_API_SECRET');
        $this->_appScopes = env('SHOPIFY_APP_SCOPES');
        $this->_appNonceLife = (int)env('SHOPIFY_NONCE_LIFE');
        $this->_appUrl = env('APP_URL');
    }

    /**
     * Initialise Shopify Store installation process
     */
    public function installStore(Request $request)
    {
        if (!isset($request->shop) || empty($request->shop)) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'A Shop name must be provided to be able to install'
            ];

            return response()->json($response, $errorCode);
        }
        foreach (stores() as $storeName => $storeKeys) {
            if ($storeName == $request->shop) {
                $this->_appApiName = $storeName;
                $this->_appApiKey = $storeKeys['key'];
                $this->_appApiSecretKey = $storeKeys['secret'];
            }
        }

        // Check if the store is already in the system
        $store = ShopifyStore::where('domain', $request->shop)->first();

        // Prepare the store for installation
        if (!isset($store)) {
            // Setup a new store instance.
            // We set the Hostname and Domain to the same value as we may not know the correct Hostname at this stage
            $store = new ShopifyStore;
            $store->domain = $store->hostname = $request->shop;
            $store->save();
        } else {
            // TODO: implement re-permission logic
            // If the store exists check if it has a Access Token

            // If Access Token is present, check that it is valid

            // If Access Token is valid, check the saved scope with the Apps default scope requirements
        }
        $store->prepareNonce();

        $queryParams = [
            'client_id' => $this->_appApiKey,
            'scope' => $this->_appScopes,
            'redirect_uri' =>  $this->_appUrl . '/install/validate', // route('app-install-validate'),
            'state' => $store->nonce
        ];
        $queryString = urldecode(http_build_query($queryParams));

        // Reset any Session data and set the installation Store to session
        session()->flush();
        session(['store_id' => $store->id]);

        $redirectUrl = $this->_httpProtocol . $store->domain . $this->_authUri . '?' . $queryString;
//        dd($redirectUrl);
        return redirect($redirectUrl);
    }
    public function validateStore(Request $request)
    {
        // Ensure the Nonce was delivered
        if (!isset($request->state) || empty($request->state)) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'A Shop State must be provided to validate an installation'
            ];

            return response()->json($response, $errorCode);
        }
        foreach (stores() as $storeName => $storeKeys){
            if ($storeName == $request->shop) {
                $this->_appApiName = $storeName;
                $this->_appApiKey = $storeKeys['key'];
                $this->_appApiSecretKey = $storeKeys['secret'];
            }
        }
        $storeId = session('store_id', false);
        if ($storeId === false) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'Unable to find the corresponding Store'
            ];

            return response()->json($response, $errorCode);
        }

        // Find the store for this validation
        $store = ShopifyStore::find($storeId);
        if (!isset($store)) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'Unable to find the corresponding Store'
            ];

            return response()->json($response, $errorCode);
        }

        // Ensure the Nonce is not too old
        $nonceCreatedAt = strtotime($store->nonce_created_at);
        $nonceExpiresAt = $nonceCreatedAt + $this->_appNonceLife;
        if ($request->timestamp > $nonceExpiresAt) {
            $response = [];

            $errorCode = 403;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'The Nonce lifetime for this installation request has expired'
            ];

            return response()->json($response, $errorCode);
        }

        // Ensure a Authorization Code was passed
        if (!isset($request->code) || empty($request->code)) {
            $response = [];

            $errorCode = 404;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'A Shop Authorization Code must be provided to validate an installation'
            ];

            return response()->json($response, $errorCode);
        }

        // Validate the provided Hostname
        if (!$this->_validateShopifyHostname($request->shop)) {
            $response = [];

            $errorCode = 403;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'The provided Hostname is not valid'
            ];

            return response()->json($response, $errorCode);
        }

        // Fetch the Permanent Access Token and approved Scopes
        $shopifyBaseUrl = $this->_httpProtocol . $request->shop;

        $data = [GuzzleRequestOptions::JSON => [
            'client_id' => $this->_appApiKey,
            'client_secret' => $this->_appApiSecretKey,
            'code' => $request->code
        ]];

        $shopify = new Guzzle(['base_uri' => $shopifyBaseUrl]);

        try {
            $ShopifyResponse = $shopify->post('admin/oauth/access_token', $data);
        } catch (\Exception $e) {
            $response = [];

            $errorCode = 400;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'Unable to retrieve Access Token from Shopify'
            ];

            return response()->json($response, $errorCode);
        }

        $ShopifyResponse = json_decode(
            (string)$ShopifyResponse->getBody(),
            true
        );

        // Update the Store with the latest information
        $shopName = str_replace($this->_shopifyHostnameSuffix, '', $request->shop);

        $store->hostname = $request->shop;
        $store->name = $shopName;
        $store->access_token = $ShopifyResponse['access_token'];
        $store->scope = $ShopifyResponse['scope'];
        $store->save();
        $store->clearNonce();

        // Post installation core data fetching
        $shopifyStore = shopify()->getShop();
        if (!is_object($shopifyStore) && !isset($shopifyStore->errors)) {
            $store->timezone = $shopifyStore['iana_timezone'];
            $store->currency = $shopifyStore['currency'];
            $store->save();
        }

        // Installation successful, branch off here and perform any initialisation tasks
        // TODO: Build Dynamic event listener system and trigger post installation event here

        $redirectUrl = $this->_httpProtocol . $store->hostname . '/admin';
        return redirect($redirectUrl);
    }

    private function _validateShopifyHostname($hostname)
    {
        if (empty($hostname)) {
            return false;
        }

        // Hostname ends with the standard Shopify Suffix
        $length = strlen($this->_shopifyHostnameSuffix);
        if (substr($hostname, -$length) === $this->_shopifyHostnameSuffix) {
            $shopName = str_replace($this->_shopifyHostnameSuffix, '', $hostname);

            // Does not contain characters other than letters (a-z), numbers (0-9), dots, and hyphens.
            if (preg_match('/^[a-z0-9\.-]+$/', $shopName)) {
                return true;
            }
        }

        return false;
    }

}
