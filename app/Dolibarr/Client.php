<?php

namespace App\Dolibarr;

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
    private $login;
    private $password;
    private $token;
    private $verifySsl = true;

    const ORDER_SYNC_TAG = "testsync";
    const ORDER_PROMO_TAG = "PromoOrder";

    public function __construct($_url,
                $_login,
                $_password,
                $_token,
                $_verifySsl = true)
    {
        $this->url = $_url;
        $this->login = $_login;
        $this->password = $_password;
        $this->token = $_token;
        $this->verifySsl = $_verifySsl;

        $this->isCli = app()->runningInConsole();
        $this->cli = new ConsoleCommand();

        $this->_httpClient = new httpClient([
            'headers' => [
                'User-Agent' => 'Dolibarr Middleware by OpenResourcing',
                'DOLAPIKEY' => $this->token
            ],
            'base_uri' => $this->url,
//            'auth' => [$this->_username, $this->_password],
            'verify' => $_verifySsl
        ]);
        //return parent::__construct($options);
    }

    public function getProducts(){

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
        dump($this->testDolibarrClient());

        $uri = '/Products/';
//"http://192.168.1.6/api/index.php" .
        $response = $this->request('GET', $this->url . $uri, [
            'query' => []
        ]);
        return $response;
    }

    protected function request(string $method, string $uri, array $data = [])
    {
        dump(["data" => $data]);
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
