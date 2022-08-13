<?php

namespace App\Http\Controllers\BC;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as Guzzle;
use App\BigCommerce\Models\BigCommerceStore;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Handler\StreamHandler;
use mysql_xdevapi\Exception;
use App\Logger;


class AuthController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    /*
    public function __construct()
    {
    }
    */

    public function callback_token(Request $request)
    {
        $logger = new Logger('bc-install');
        $loggerFilename = storage_path(
            'logs/bc-install.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);
        $logger->info("called callback token");

        try {
            $access_token = $request->access_token;
            dump(["request_info" => $request]);
            $BCStore = BigCommerceStore::where('id', 1)->first();
            if ($BCStore) {
                $BCStore->access_token = $access_token;
                $BCStore->save();
            }
        } catch (\Exception $e){
            $logger->error($e->getMessage());
        }
    }
    /**
     * Initialise Shopify Store installation process
     */
    public function callback(Request $request)
    {
        $logger = new Logger('bc-callback');
        $loggerFilename = storage_path(
            'logs/bc-callback.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);
        $logger->info("called callback request");

        $code=$request->code;
        $context=$request->context;
        $scope=$request->scope;
        $client_id=env('BC_CLIENT_ID');
        $client_secret=env('BC_CLIENT_SECRET');
        $redirect_uri=env('APP_URL');

        $logger->info("request: code: " . $code);
        $logger->info("request: context: " . $context);
        $logger->info("request: scope: " . $scope);
        $logger->info("request: client_id: " . $client_id);
        $logger->info("request: client_secret: " . $client_secret);

        $bigcommerce_guzzle = new Guzzle([
            'base_uri' => 'https://login.bigcommerce.com/oauth2/token',
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ]);

        $data = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code,
            'context' => $context,
            'scope' => $scope,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_uri . '/BCDemoApp/auth/oauth'
        ];
//        return(json_encode(['auth_will_send' => $data]));

//        throw new Exception("just stopping here!",0);
        try {
            $BCResponse = $bigcommerce_guzzle->request(
                'POST',
                'https://login.bigcommerce.com/oauth2/token',
                [
                    'form_params' => $data
                ]
            );
        } catch (\Exception $e) {
            $response = [];

            $errorCode = 400;
            $response['error'] = [
                'code' => $errorCode,
                'message' => 'Unable to retrieve Access Token from Shopify',
                'exception_message' => $e->getMessage()
            ];

            return response()->json($response, $errorCode);
        }
        $response_obj = json_decode($BCResponse->getBody()->getContents(),true);
        dump(["body" => $response_obj]);
        if (isset($response_obj['access_token'])){
            $access_token = $response_obj['access_token'];
        } else {
            $access_token = null;
        }
        dump(["access_token" => $access_token]);

        $BCStore = BigCommerceStore::where('id', 1)->first();
        if ($BCStore) {
            $BCStore->access_token = $access_token;
            $BCStore->save();
        }
        // Reset any Session data and set the installation Store to session
//        session()->flush();


//        $redirectUrl = 'https://openresourcing.mybigcommerce.com.au' . '?' . $queryString;
//        dd($redirectUrl);
        return(json_encode(['authed-access-token' => $access_token]));
//        return redirect($redirectUrl);
    }
    public function load(Request $request)
    {
        $logger = new Logger('bc-load');
        $loggerFilename = storage_path(
            'logs/bc-load.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);
        $logger->info("called load request");

        $queryParams = [
        ];
        $queryString = urldecode(http_build_query($queryParams));

        // Reset any Session data and set the installation Store to session
        session()->flush();

//        $redirectUrl = 'https://openresourcing.mybigcommerce.com.au' . '?' . $queryString;
//        dd($redirectUrl);
//        dump(['testing']);
        authoriseStore(1);
        $bc_products = bigcommerce()->getProducts();
//        dump(['products' => $bc_products]);
        return(response()->json($bc_products
        /*[
            "products" => $bc_products] */
        ));
//        return redirect($redirectUrl);
    }

}
