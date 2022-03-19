<?php

namespace App\Services\Dolibarr\to;

use App\Console\ConsoleCommand;
use App\Logger;
use App\ProductSkuMapper;
use Monolog\Handler\StreamHandler;

use App\Config;

class SyncProducts
{

    public static function execute(int $shopifyProductId = null)
    {

        $logger = new Logger('App_Services_OrderSync');
        $loggerFilename = storage_path(
            'logs/App_Services_Dolibarr_to_SyncProducts.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        $isCli = app()->runningInConsole();
        $cli = new ConsoleCommand;

        $filter = [];
        if ($shopifyProductId != null){
            $filter["ids"] = $shopifyProductId;
        }
        //dd($filter);
        $dolibarr_attributes = dolibarr()->fetchAllDolibarrVariantAttributes();
        dump($dolibarr_attributes);
        $shopify_products = shopify()->getAllProducts($filter);
        //dump($shopify_products);
        foreach($shopify_products as $shopify_product){
            $_dolibarr_product_id = null;

            $product_price = null;
            if (isset($shopify_product['variants']) && count($shopify_product['variants']) != 0) {
                foreach ($shopify_product['variants'] as $variant) {
                    if ($product_price == null || $variant['price'] < $product_price) {
                        $product_price = $variant['price'];
                    }
                }
            }
            if ($product_price == null) {
                $product_price = 0;
            }

            // search dolibarr for product...
            $cli->line("");
            $cli->line("starting...");
            $cli->info("searching dolibarr for ref:" . $shopify_product['handle']);
            $dolibarr_result = dolibarr()->getProductsByRef($shopify_product['handle']);
            if ($dolibarr_result["success"] == true) {
                $cli->info("already found this product - will need to update it");
                $dolibarr_products = json_decode($dolibarr_result['message'],true);
                $cli->info("dolibarr_prod_id:" . $dolibarr_products[0]['id']);
                $_dolibarr_product_id = $dolibarr_products[0]['id'];
            }  else {
                $cli->info("product not found in dolibarr, will create it now");
                $dolibarr_result = dolibarr()->createProduct([
                    'ref' => $shopify_product['handle'],
                    'label' => $shopify_product['title'],
                    'status' => 1,
                    'status_buy' => 0,
                    'description' => $shopify_product['body_html'],
                    'type' => 0,
                    'price' => $product_price,
                    'price_ttc' => $product_price
                ]);
                if ($dolibarr_result['success'] == true) {
                    $_dolibarr_product_id = $dolibarr_result['message'];
                    $cli->info("created product in dolibarr - product id: ". $dolibarr_result['message']);
                } else {
                    dump($dolibarr_result);
                }
            }
            if ($_dolibarr_product_id != null){
                if (isset($shopify_product['variants']) && count($shopify_product['variants']) != 0) {
                    $optionNameLookup = [];
                    foreach ($shopify_product['options'] as $option){
                        $optionNameLookup[$option['position']] = $option['name'];
                    }
                    dump($optionNameLookup);
                    foreach ($shopify_product['variants'] as $variant) {
                        $skip_variant = false;
                        try {
                            $features_array = [];
                            for ($i = 0; $i <= 5; $i++) {
                                if (isset($variant['option' . $i])) {
                                    $feature_name = strtoupper($optionNameLookup[$i]);
                                    echo "feature_name : " . $feature_name . "\n";

                                    if (!isset($dolibarr_attributes[$feature_name])){
                                        $logger->info("cannot look up attribute name ". $feature_name . " in variant : " .$variant['id']  . ' - product : ' . $variant['product_id']);
                                        $skip_variant = true;
                                        continue;
                                    }
                                    $feature_id = $dolibarr_attributes[$feature_name]['id'];
                                    echo "feature_id : " . $feature_id . "\n";

                                    $feature_value = strtoupper($variant['option' . $i]);
                                    echo "feature_value : " . $feature_value . "\n";

                                    if (!isset($dolibarr_attributes[$feature_name]['values'][$feature_value])){
                                        $logger->info("cannot look up attribute value ". $feature_name . " : " . $feature_value . ' in variant : ' .$variant['id']  . ' - product : ' . $variant['product_id']);
                                        $skip_variant = true;
                                        continue;
                                    }
                                    $feature_value_id = $dolibarr_attributes[$feature_name]['values'][$feature_value]['id'];
                                    echo "feature_value_id : " . $feature_value_id . "\n";

                                    $features_array[$feature_id] = $feature_value_id;
                                }
                            }
                            if ($skip_variant){
                                continue;
                            }
                            dump($features_array);
                            $variant_payload = [
                                'ref' => $variant['sku'],
                                'variation_price' => ($product_price - $variant['price']),
                                'variation_price_percentage' => 0,
                                'entity' => 1,
                                "combination_price_levels" => null,
                                "variation_ref_ext" => null,
                                "weight_impact" => 0,
                                "price_impact" => 0,
                                "price_impact_is_percent" => 0,
                                "features" => $features_array
                            ];
                            dump($variant_payload);

                            $dolibarr_variant_result = dolibarr()->createVariant($_dolibarr_product_id, $variant_payload);
                            if ($dolibarr_variant_result['success'] == true) {
                                $cli->info("created variant in dolibarr - variant id: " . $dolibarr_variant_result['message']);
                            } else {
                                dump($dolibarr_variant_result);
                            }

                        } catch (\Exception $e) {
                            $logger->error("error when attmepting to import product variant : " . $variant['id']  . ' - product : ' . $variant['product_id'] . ' - message: '. $e->getMessage());
                        }
                    }
                }
            }
        }
        /*
        dd(["dolibarr" => dolibarr()->getAllProducts(),
            "shopify" => shopify()->getAllProducts($filter)]);
        */
    }

}
