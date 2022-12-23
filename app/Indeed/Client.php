<?php

namespace App\Indeed;

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Exception\RequestException;

use App\Console\ConsoleCommand;

class Client //extends BasicShopifyAPI
{
    protected $isCli = false;
    protected $cli;

    protected $_httpClient;

    private $api_url;
    private $name;
    private $access_token;

    public function __construct($_api_url,
                                $_name,
                                $_access_token)
    {
        $this->api_url = $_api_url;
        $this->name = $_name;
        $this->access_token = $_access_token;

        $this->isCli = app()->runningInConsole();
        $this->cli = new ConsoleCommand();

        $this->_httpClient = new httpClient([
            'headers' => [
                'User-Agent' => 'Indeed Middleware by OpenResourcing',
                'Authorization' => 'Bearer ' . $this->access_token
            ],
            'base_uri' => $this->api_url
        ]);
        //return parent::__construct($options);
    }

    public function appInfo(){
        $response = $this->request('GET', $this->api_url . "/appinfo", []);
        return $response;
    }
    public function testClientMethod(){
        dump([
            "store_id" => store()->id,
            "api_url" => $this->api_url,
            "name" => $this->name,
            "access_token" => $this->access_token
        ]);

    }
    public function initIndeedAPI(){
        $auth_token_url = "https://apis.indeed.com/oauth/v2/tokens";
        //overriding the httpclient - so it can use the one-time auth url
        $this->_httpClient = new httpClient([
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json'
            ],
            'base_uri' => $auth_token_url
        ]);

        $payload = [
            'client_id' => env('INDEED_CLIENT_ID'),
            'client_secret' => env('INDEED_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'employer_access'
        ];
        $response = $this->request('POST', $auth_token_url, [
            "form_params" => $payload
        ]);

        $access_token = null;
        if (isset($response['message'])){
            $token_message = json_decode($response['message'],true);
            if (isset($token_message['access_token'])){
                $access_token = $token_message['access_token'];
            }
        }
        dump([
            "access_token" => $access_token
        ]);
        if ($access_token){
            $indeedAPI = \App\Indeed\Models\IndeedAPI::find(store()->id);
            if (!$indeedAPI) {
                $indeedAPI = new \App\Indeed\Models\IndeedAPI();
            }
            $indeedAPI->id = store()->id;
            $indeedAPI->api_url = "https://secure.indeed.com/v2/api";
            $indeedAPI->name = env('INDEED_NAME');
            $indeedAPI->access_token = $access_token;

            try {
                $result = $indeedAPI->save();
                dump([
                    "result" => $result
                ]);
            } catch (\Exception $e) {
                dump([
                    "exception" => $e
                ]);
            }

            try {
                $result = $indeedAPI->save();
                dump([
                    "result" => $result
                ]);
            } catch (\Exception $e) {
                dump([
                    "exception" => $e
                ]);
            }

            config(['indeed' => false]);
        }


        // always reset the indeed helper session config - so it can reset next time with the access token

        // write this to the table

        return $response;
    }

    public function getProductsByRef($ref){
        $uri = "/Products";
        $response = $this->request('GET', $this->url . $uri, [
            'query' => "sqlfilters=ref='". $ref ."'"
        ]);
        return $response;

    }
    public function getProduct($productId){
        $uri = "Products/{$productId}";
        $response = $this->request('GET', $this->url . $uri, [
            'query' => []
        ]);
        if ($response['success'] === true) {
            return (json_decode($response['message'], true));
        } else {
            return [];
        }
    }
    public function testDolibarrClient(){
        return [
            "dolibarr" => [
                "url" => $this->url,
                "login" => $this->login,
                "password" => $this->password,
                "token" => $this->token
            ]
        ];
    }

    public function getAllProducts()
    {

        $uri = 'products/';
        $response = $this->request('GET', $this->url . $uri, [
//            'query' => 'variant_filter=2'
        ]);
        if ($response['success'] === true) {
            return (json_decode($response['message'], true));
        } else {
            dump(["exception" => $response]);
            return [];
        }
    }

    public function createProduct(array $payload){
        $uri = '/Products';
        $response = $this->request('POST', $this->url . $uri, [
            "body" => json_encode($payload)
        ]);
        return $response;
    }
    public function createVariant($product_id, array $payload){
        $uri = "/Products/{$product_id}/variants";
        $response = $this->request('POST', $this->url . $uri, [
            "body" => json_encode($payload)
        ]);
        return $response;
    }
    public function getAllAttributes(){
        $uri = "/products/attributes";
        $response = $this->request('GET', $this->url . $uri, []);
        return $response;
    }
    public function getAllValuesForAttribute(int $id){
        $uri = "/products/attributes/{$id}/values";
        $response = $this->request('GET', $this->url . $uri, []);
        return $response;
    }

    public function fetchAllDolibarrVariantAttributes(){
        $attributes = [];
        $dolibarr_attributes = dolibarr()->getAllAttributes();
        if ($dolibarr_attributes["success"]) {
            $message_attributes_array = json_decode($dolibarr_attributes["message"]);
            foreach($message_attributes_array as $message_attribute){
                $attribute_values = [];
                $dolibarr_attribute_values = dolibarr()->getAllValuesForAttribute($message_attribute->id);
                $message_values_array = json_decode($dolibarr_attribute_values["message"]);
                if (is_array( $message_values_array )  && count($message_values_array) > 0 )
                    foreach ($message_values_array as $message_value){
                        $value = $message_value->value;
                        if (str_contains($value,"'")){
                            $value = str_replace("'",'',$value);
                        }
                        $ref = $message_value->ref;
                        if (str_contains($ref,"'")){
                            $ref = str_replace("'",'',$ref);
                        }
                        $attribute_values[$ref] = [
                            "id" => $message_value->id,
                            "ref" => $ref,
                            "value" => $value
                        ];
                    }
                $attributes[strtoupper($message_attribute->ref)] = [
                    "id" => $message_attribute->id,
                    "ref" => $message_attribute->ref,
                    "label" => $message_attribute->label,
                    "values" => $attribute_values
                ];
            }
        }
        return $attributes;
    }

    protected function request(string $method, string $uri, array $data = [])
    {
//        dump(["data" => $data]);
        $result = [];

        try {
            $response = $this->_httpClient->request($method, $uri, $data);

        } catch (RequestException $e) {

            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();

                $result['success'] = false;
                $result['status_code'] = $errorResponse->getStatusCode();
                $result['message'] = $errorResponse->getReasonPhrase();
            } else {
                $result['success'] = false;
                $result['status_code'] = $e->getCode();
                $result['message'] = $e->getMessage();
            }

            return $result;
        } catch (\Exception $e) {

            $result['success'] = false;
            $result['status_code'] = $e->getCode();
            $result['message'] = $e->getMessage();

            return $result;
        }

        if ((200 <= $response->getStatusCode()) && $response->getStatusCode() < 300) {
            $result['success'] = true;
            $result['status_code'] = $response->getStatusCode();
            $result['headers'] = $response->getHeaders();
            $result['message'] = $response->getBody()->getContents();
        } else {
            $result['success'] = false;
            $result['status_code'] = $response->getStatusCode();
            $result['headers'] = $response->getHeaders();
            $result['message'] = $response->getReasonPhrase();
        }

        return $result;
    }

}
