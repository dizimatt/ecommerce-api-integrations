<?php

namespace App\Dolibarr;

//use OhMyBrew\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;
use App\Console\ConsoleCommand;

class Client //extends BasicShopifyAPI
{
    protected $isCli = false;
    protected $cli;

    private $url;
    private $login;
    private $password;
    private $token;

    const ORDER_SYNC_TAG = "testsync";
    const ORDER_PROMO_TAG = "PromoOrder";

    public function __construct()
    {
        $this->isCli = app()->runningInConsole();
        $this->cli = new ConsoleCommand();

        //return parent::__construct($options);
    }

    public function setUrl($url){
        $this->url = $url;
    }
    public function setLogin($login){
        $this->login = $login;
    }
    public function setPassword($password){
        $this->password = $password;
    }
    public function setToken($token){
        $this->token = $token;
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
}
