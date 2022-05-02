<?php

namespace App\BigCommerce;

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Pool as httpPool;
use GuzzleHttp\Psr7\Request as httpRequest;
use GuzzleHttp\Psr7\Uri as httpUri;
use GuzzleHttp\Exception\RequestException;

use App\Console\ConsoleCommand;

class Client //extends BasicShopifyAPI
{
    protected $restClient;

    protected $isCli = false;
    protected $cli;

    protected $_httpClient;

    private $url;
    private $token;


    public function __construct($_url,
                $_token)
    {
        $this->url = $_url;
        $this->token = $_token;

        $this->isCli = app()->runningInConsole();
        $this->cli = new ConsoleCommand();

        $this->_httpClient = new httpClient([
            'headers' => [
                'User-Agent' => 'BigCommerce Middleware by OpenResourcing',
                'X-Auth-Token' => $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'base_uri' => $this->url,
            'verify' => false
        ]);
//        dump(["httpclient" => $this->_httpClient]);
        //return parent::__construct($options);
    }

    public function getProducts(array $filter = []){
        $uri = "/catalog/products";
        $response = $this->request('GET', $this->url . $uri, [
            'query' => $filter
        ]);
        if ($response['success'] === true) {
            return (json_decode($response['message'], true));
        } else {
            return [];
        }
    }
    public function getProduct($productId){
        $uri = "/catalog/products/{$productId}";
        $response = $this->request('GET', $this->url . $uri, [
            'query' => []
        ]);
        if ($response['success'] === true) {
            return (json_decode($response['message'], true));
        } else {
            return [];
        }
    }
    public function createProduct($payload){
        $uri = "/catalog/products";
        $response = $this->request('POST',$this->url . $uri, [
            "body" => json_encode($payload)
        ]);
        return $response;
    }

    public function assignProductToChannel($product_id, $channel_id){
        $uri = "/catalog/products/channel-assignments";
        $response = $this->request('PUT',$this->url . $uri, [
            "body" => json_encode([
                [
                    "product_id" => $product_id,
                    "channel_id" => $channel_id
                ]
            ])
        ]);
        return $response;
    }

    public function testBigCommerceClient(){
        return [
            "bigcommerce" => [
                "url" => $this->url,
                "token" => $this->token
            ]
        ];
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
