<?php

namespace App\Wordpress;

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

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
        if ($response['success'] === true){
            return json_decode($response['message'],true);
        } else {
            return [];
        }
    }
    public function createProduct(array $payload){
        $response = $this->post($this->url . "/products", $payload);
        if ($response['success'] === true){
            return json_decode($response['message'],true);
        } else {
            return [];
        }
    }
    public function getAllEndpoints(){
        $response = $this->request("GET", $this->url);
        if ($response['success'] === true) {
            return json_decode($response['message'], true);
        } else {
            return [];
        }
        return $response;
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

    protected function post(string $uri, array $data = [])
    {
//        dump(["data" => $data]);
        $result = [];

        try {
            $response = $this->_httpClient->post($uri, [
                RequestOptions::JSON => $data
            ]);

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
