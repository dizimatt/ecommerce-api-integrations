<?php

namespace App\Http\Controllers\BC;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as Guzzle;
use App\BigCommerce\Models\BigCommerceStore;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use mysql_xdevapi\Exception;

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
        $access_token=$request->access_token;
        dump(["request_info" => $request]);
        $BCStore = BigCommerceStore::where('id', 1)->first();
        if ($BCStore) {
            $BCStore->access_token = $access_token;
            $BCStore->save();
        }
    }
    /**
     * Initialise Shopify Store installation process
     */
    public function callback(Request $request)
    {

        $code=$request->code;
        $context=$request->context;
        $scope=$request->scope;
        $client_id=env('BC_CLIENT_ID');
        $client_secret=env('BC_CLIENT_SECRET');

        $bigcommerce_guzzle = new Guzzle([
            'base_uri' => 'https://login.bigcommerce.com/',
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
            'redirect_uri' => 'http://localhost:8080/BCDemoApp/auth/callback'
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
        dump([
            "data" => $data,
            "request" => $request,
            "response" => $BCResponse
        ]);
        if (isset($BCResponse->message)) {
            $response_obj = json_decode($BCResponse->message);
            $BCStore = BigCommerceStore::where('id', 1)->first();
            if ($BCStore) {
                $BCStore->access_token = $response_obj['access_token'];
                $BCStore->save();
            }
        }
        // Reset any Session data and set the installation Store to session
        session()->flush();


//        $redirectUrl = 'https://openresourcing.mybigcommerce.com.au' . '?' . $queryString;
//        dd($redirectUrl);
//        dump(['testing']);
        return(json_encode(['authed_response' => $BCResponse]));
//        return redirect($redirectUrl);
    }
    public function load(Request $request)
    {

        $queryParams = [
        ];
        $queryString = urldecode(http_build_query($queryParams));

        // Reset any Session data and set the installation Store to session
        session()->flush();

        $redirectUrl = 'https://openresourcing.mybigcommerce.com.au' . '?' . $queryString;
//        dd($redirectUrl);
//        dump(['testing']);
        return(json_encode(['loaded']));
//        return redirect($redirectUrl);
    }

}
