<?php

namespace App\Http\Middleware;

use App\Logger;
use Monolog\Handler\StreamHandler;
use App\Shopify\Models\ShopifyAppKey;
use Closure;

class ShopifyAdminAuthenticate
{

    /*
    public function __construct()
    {
        $this->_appApiSecret = env('SHOPIFY_APP_API_SECRET');
    }
    */

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */

    private function checkHMAC($all_params){
        $store_id = 0;
        $logger = new Logger('ShopifyAdminAuthenticateHMAC');
        $loggerFilename = storage_path(
            'logs/admin_authenticate.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        if (isset($all_params['hmac'])){
            $param_hmac = $all_params['hmac'];
            unset($all_params['hmac']);
        } else {
            $param_hmac = "";
        }

        if (isset($all_params['shop'])){
            $shopify_app_key = ShopifyAppKey::where('store_name',$all_params['shop'])->first();
            if ($shopify_app_key){
                $shopify_app_key = $shopify_app_key->toArray();
                $store_api_secret = $shopify_app_key['store_api_secret'];
                $store_id = $shopify_app_key['id'];
            } else {
                $store_api_secret = "";
            }
        } else {
            $store_api_secret = "";
        }

        foreach($all_params as $key=>$value){

            $key=str_replace("%","%25",$key);
            $key=str_replace("&","%26",$key);
            $key=str_replace("=","%3D",$key);
            $value=str_replace("%","%25",$value);
            $value=str_replace("&","%26",$value);

            $ar[] = $key."=".$value;
        }

        $str = join('&',$ar);

        $logger->info("hmac key",["store_api_secret" => $store_api_secret]);
        $generated_hmac =  hash_hmac('sha256',$str,$store_api_secret,false);
        /*
        $logger->info("generated the hmac",[
            "generated_hmac" => $generated_hmac
        ]);
        */

        if ($generated_hmac === $param_hmac) {
            return $store_id;
        } else {
            return false;
        }

    }
    public function handle($request, Closure $next)
    {
        $logger = new Logger('ShopifyAdminAuthenticateHandle');
        $loggerFilename = storage_path(
            'logs/admin_authenticate.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);
        $all_params = $request->all();

        $store_id = $this->checkHMAC($all_params);
        if ($store_id) {
            authoriseStore($store_id);
            return $next($request);
        } else {
            $logger->error("controller wasn't called from shopify site, please check hmac and shopify secret key");
            return [
                "error" => "controller wasn't called from shopify site, please check hmac and shopify secret key"
            ];
        }

    }
}
