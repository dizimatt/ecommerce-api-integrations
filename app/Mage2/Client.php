<?php

namespace App\Mage2;

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Pool as httpPool;
use GuzzleHttp\Psr7\Request as httpRequest;
use GuzzleHttp\Psr7\Uri as httpUri;
use GuzzleHttp\Exception\RequestException;

use App\Console\ConsoleCommand;

class Client
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
//                'X-Auth-Token' => $this->token,
                'Authorization' => "Bearer " . $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'base_uri' => $this->url,
            'verify' => false
        ]);
//        dump(["httpclient" => $this->_httpClient]);
        //return parent::__construct($options);
    }

    public function fetchAttributeSets(){
        $uri = "eav/attribute-sets/list";
        $response = $this->request('GET', $this->url . $uri, [
            'query' => [
                "searchCriteria[filter_groups][0][filters][0][field]" => "attribute_set_name",
                "searchCriteria[filter_groups][0][filters][0][value]"=>"Top",
                "searchCriteria[filter_groups][0][filters][0][condition_type]"=>"eq",
                "searchCriteria[filter_groups][1][filters][0][field]"=>"entity_type_id",
                "searchCriteria[filter_groups][1][filters][0][value]"=>"4",
                "searchCriteria[filter_groups][1][filters][0][condition_type]"=>"eq"
            ]
        ]);
        if ($response['success'] === true) {
            return (json_decode($response['message'], true));
        } else {
            return ["response" => $response];
        }

    }
    public function fetchAttributes($set_id = 9){
        $uri = "products/attribute-sets/".$set_id."/attributes";
        $response = $this->request('GET', $this->url . $uri, [
            'query' => []
        ]);
        if ($response['success'] === true) {
            return (json_decode($response['message'], true));
        } else {
            return ["response" => $response];
        }

    }
    public function fetchAllCategories(){
        $uri = "categories";
        $response = $this->request('GET', $this->url . $uri, [
            'query' => [
                "searchCriteria[filter_groups][0][filters][0][field]" => "id",
                "searchCriteria[filter_groups][0][filters][0][value]" => "1",
                "searchCriteria[filter_groups][0][filters][0][condition_type]" => "gte"
            ]
        ]);
        if ($response['success'] === true) {
            return (json_decode($response['message'], true));
        } else {
            return ["response" => $response];
        }

    }

    public function createProduct($payload = []){
        $uri = "products";
        dump([
           "product_as_json" => json_encode($payload)
        ]);
        $response = $this->request('POST', $this->url . $uri, [
            'body' => json_encode($payload)
        ]);
        if ($response['success'] === true) {
            return (json_decode($response['message'], true));
        } else {
            return ["response" => $response];
        }
    }
    public function fetchProductsFromFilter($filter = []){

        $uri = "products";
        $response = $this->request('GET', $this->url . $uri, [
            'query' => $filter
        ]);
        if ($response['success'] === true) {
            return (json_decode($response['message'], true));
        } else {
            return ["response" => $response];
        }

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
                $result['body'] = $errorResponse->getBody()->getContents();
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
