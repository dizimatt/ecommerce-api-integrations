<?php

namespace App\Wordpress;

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Exception\RequestException;

use App\Console\ConsoleCommand;

class Client
{
    protected $restClient;

    protected $isCli = false;
    protected $cli;

    protected $_httpClient;

    private $url;
    private $login;
    private $password;
    private $key;
    private $secret;
    private $verifySsl = true;

    const ORDER_SYNC_TAG = "testsync";
    const ORDER_PROMO_TAG = "PromoOrder";

    public function __construct($_url,
                $_login,
                $_password,
                $_key,
                $_secret,
                $_verifySsl = false)
    {
        $this->url = $_url;
        $this->login = $_login;
        $this->password = $_password;
        $this->key = $_key;
        $this->secret = $_secret;
        $this->verifySsl = $_verifySsl;

        $this->isCli = app()->runningInConsole();
        $this->cli = new ConsoleCommand();

        $handler = new CurlHandler();
        $stack = HandlerStack::create($handler);

        $oauth = new Oauth1([
            'consumer_key' => $this->key,
            'consumer_secret' => $this->secret,
            'token' => '',
            'token_secret' => '',
            'request_method' => Oauth1::REQUEST_METHOD_HEADER, //REQUEST_METHOD_QUERY,
            'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC
        ]);
        $stack->push($oauth);
        $this->_httpClient = new httpClient([
            'base_uri' => $this->url,
            'handler' => $stack,
            'auth' => 'oauth'
        ]);
    }

    public function getAllProducts(){
//        $response = $this->_httpClient->get($this->url . "/products");
        $response = $this->request("GET", $this->url . "/products");
        return $response;
    }
    public function getAllEndpoints(){
//        $response = $this->_httpClient->get($this->url . "/products");
        $response = $this->request("GET", $this->url);
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
        $uri = "/Products/{$productId}";
        $response = $this->request('GET', $this->url . $uri, [
            'query' => []
        ]);
        return $response;
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
