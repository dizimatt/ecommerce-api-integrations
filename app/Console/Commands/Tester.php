<?php

namespace App\Console\Commands;

use App\Console\AbstractCommand;

class Tester extends AbstractCommand
{
    const PROGRESS_BAR_FORMAT = 'debug';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test
                                {store_id : The integrations Store ID for the Shopify Store}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Function Test command';

    /**
     * Execute the console command.
     *
     * @param  \App\DripEmailer $drip
     * @return mixed
     */
    public function handle()
    {
        $time_start = microtime(true);

        parent::handle();

        echo "\n\n";
        $this->info('Tester::handle() EXECUTED');
        echo "\n";

        // ----------------------------------------------------------------------
        // Test code here
        // ----------------------------------------------------------------------

        \App\Services\Orders::SyncOrders('4318034854072');
/*
//  testing event/listeners for order creations
        $eventClass = "\App\Events\Shopify\\OrdersCreate";
        $eventData = '{"id":4317890642104,"admin_graphql_api_id":"gid://shopify/Order/4317890642104","app_id":580111,"browser_ip":"122.150.48.181","buyer_accepts_marketing":false,"cancel_reason":null,"cancelled_at":null,"cart_token":null,"checkout_id":24929737638072,"checkout_token":"13c4bf9fb2856b5e63c0edc4fc275024","client_details":{"accept_language":"en-AU,en-US;q=0.9,en;q=0.8,zh-CN;q=0.7,zh;q=0.6,en-GB;q=0.5","browser_height":758,"browser_ip":"122.150.48.181","browser_width":1425,"session_hash":null,"user_agent":"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36"},"closed_at":null,"confirmed":true,"contact_email":null,"created_at":"2022-01-07T14:53:27+11:00","currency":"AUD","current_subtotal_price":"50.00","current_subtotal_price_set":{"shop_money":{"amount":"50.00","currency_code":"AUD"},"presentment_money":{"amount":"50.00","currency_code":"AUD"}},"current_total_discounts":"0.00","current_total_discounts_set":{"shop_money":{"amount":"0.00","currency_code":"AUD"},"presentment_money":{"amount":"0.00","currency_code":"AUD"}},"current_total_duties_set":null,"current_total_price":"60.00","current_total_price_set":{"shop_money":{"amount":"60.00","currency_code":"AUD"},"presentment_money":{"amount":"60.00","currency_code":"AUD"}},"current_total_tax":"0.00","current_total_tax_set":{"shop_money":{"amount":"0.00","currency_code":"AUD"},"presentment_money":{"amount":"0.00","currency_code":"AUD"}},"customer_locale":"en","device_id":null,"discount_codes":[],"email":"","estimated_taxes":false,"financial_status":"paid","fulfillment_status":null,"gateway":"bogus","landing_site":"/wallets/checkouts.json","landing_site_ref":null,"location_id":null,"name":"#1005","note":null,"note_attributes":[],"number":5,"order_number":1005,"order_status_url":"https://openresourcing.myshopify.com/51282378936/orders/909d5d6ffef8d15ec89c5cf21ca2e894/authenticate?key=07b1870eb934d4381a03a94d1ecccefc","original_total_duties_set":null,"payment_gateway_names":["bogus"],"phone":"+61413824449","presentment_currency":"AUD","processed_at":"2022-01-07T14:53:26+11:00","processing_method":"direct","reference":null,"referring_site":"https://openresourcing.myshopify.com/products/test-product-number-1","source_identifier":null,"source_name":"web","source_url":null,"subtotal_price":"50.00","subtotal_price_set":{"shop_money":{"amount":"50.00","currency_code":"AUD"},"presentment_money":{"amount":"50.00","currency_code":"AUD"}},"tags":"","tax_lines":[],"taxes_included":false,"test":true,"token":"909d5d6ffef8d15ec89c5cf21ca2e894","total_discounts":"0.00","total_discounts_set":{"shop_money":{"amount":"0.00","currency_code":"AUD"},"presentment_money":{"amount":"0.00","currency_code":"AUD"}},"total_line_items_price":"50.00","total_line_items_price_set":{"shop_money":{"amount":"50.00","currency_code":"AUD"},"presentment_money":{"amount":"50.00","currency_code":"AUD"}},"total_outstanding":"0.00","total_price":"60.00","total_price_set":{"shop_money":{"amount":"60.00","currency_code":"AUD"},"presentment_money":{"amount":"60.00","currency_code":"AUD"}},"total_price_usd":"43.33","total_shipping_price_set":{"shop_money":{"amount":"10.00","currency_code":"AUD"},"presentment_money":{"amount":"10.00","currency_code":"AUD"}},"total_tax":"0.00","total_tax_set":{"shop_money":{"amount":"0.00","currency_code":"AUD"},"presentment_money":{"amount":"0.00","currency_code":"AUD"}},"total_tip_received":"0.00","total_weight":5000,"updated_at":"2022-01-07T14:53:28+11:00","user_id":null,"billing_address":{"first_name":"matt","address1":"97 station street","phone":null,"city":"newtown","zip":"2042","province":"New South Wales","country":"Australia","last_name":"Dilley","address2":"","company":null,"latitude":-33.9011934,"longitude":151.1773588,"name":"matt Dilley","country_code":"AU","province_code":"NSW"},"customer":{"id":5729692811448,"email":null,"accepts_marketing":false,"created_at":"2022-01-07T14:52:59+11:00","updated_at":"2022-01-07T14:53:28+11:00","first_name":"matt","last_name":"Dilley","orders_count":0,"state":"disabled","total_spent":"0.00","last_order_id":null,"note":null,"verified_email":true,"multipass_identifier":null,"tax_exempt":false,"phone":"+61413824449","tags":"","last_order_name":null,"currency":"AUD","accepts_marketing_updated_at":"2022-01-07T14:53:28+11:00","marketing_opt_in_level":null,"tax_exemptions":[],"sms_marketing_consent":{"state":"not_subscribed","opt_in_level":"single_opt_in","consent_updated_at":null,"consent_collected_from":"OTHER"},"admin_graphql_api_id":"gid://shopify/Customer/5729692811448","default_address":{"id":6952848720056,"customer_id":5729692811448,"first_name":"matt","last_name":"Dilley","company":null,"address1":"97 station street","address2":"","city":"newtown","province":"New South Wales","country":"Australia","zip":"2042","phone":null,"name":"matt Dilley","province_code":"NSW","country_code":"AU","country_name":"Australia","default":true}},"discount_applications":[],"fulfillments":[],"line_items":[{"id":10841399754936,"admin_graphql_api_id":"gid://shopify/LineItem/10841399754936","fulfillable_quantity":1,"fulfillment_service":"manual","fulfillment_status":null,"gift_card":false,"grams":5000,"name":"\"Sydney E-Riders\" print onto Lazy Rolling Armoured Hoody - S","origin_location":{"id":2870940238008,"country_code":"AU","province_code":"NSW","name":"Openresourcing","address1":"97 station street","address2":"","city":"newtown","zip":"2042"},"price":"50.00","price_set":{"shop_money":{"amount":"50.00","currency_code":"AUD"},"presentment_money":{"amount":"50.00","currency_code":"AUD"}},"product_exists":true,"product_id":6086253772984,"properties":[],"quantity":1,"requires_shipping":true,"sku":"h00d13","taxable":false,"title":"\"Sydney E-Riders\" print onto Lazy Rolling Armoured Hoody","total_discount":"0.00","total_discount_set":{"shop_money":{"amount":"0.00","currency_code":"AUD"},"presentment_money":{"amount":"0.00","currency_code":"AUD"}},"variant_id":37723470495928,"variant_inventory_management":"shopify","variant_title":"S","vendor":"Openresourcing","tax_lines":[],"duties":[],"discount_allocations":[]}],"payment_details":{"credit_card_bin":"1","avs_result_code":null,"cvv_result_code":null,"credit_card_number":"•••• •••• •••• 1","credit_card_company":"Bogus"},"payment_terms":null,"refunds":[],"shipping_address":{"first_name":"matt","address1":"97 station street","phone":null,"city":"newtown","zip":"2042","province":"New South Wales","country":"Australia","last_name":"Dilley","address2":"","company":null,"latitude":-33.9011934,"longitude":151.1773588,"name":"matt Dilley","country_code":"AU","province_code":"NSW"},"shipping_lines":[{"id":3593608593592,"carrier_identifier":null,"code":"Standard","delivery_category":null,"discounted_price":"10.00","discounted_price_set":{"shop_money":{"amount":"10.00","currency_code":"AUD"},"presentment_money":{"amount":"10.00","currency_code":"AUD"}},"phone":null,"price":"10.00","price_set":{"shop_money":{"amount":"10.00","currency_code":"AUD"},"presentment_money":{"amount":"10.00","currency_code":"AUD"}},"requested_fulfillment_service_id":null,"source":"shopify","title":"Standard","tax_lines":[{"channel_liable":false,"price":"0.00","price_set":{"shop_money":{"amount":"0.00","currency_code":"AUD"},"presentment_money":{"amount":"0.00","currency_code":"AUD"}},"rate":0.1,"title":"GST"}],"discount_allocations":[]}]}';
        $postData = json_decode($eventData, true);
        dump($postData);
        \Event::dispatch(new $eventClass($postData));
*/
/*
        $products = shopify()->getAllProducts();
        foreach ($products as $product) {
            $this->line("");
            $this->info("id: " . $product['id']
                . ", title: ". $product['title']
                . ", sku: ". $product['variants'][0]['sku']
                . ",tags: ". $product['tags']
                . ", price: " . $product['variants'][0]['price']);
        }
*/
            /*
    //        $shopifyStore = shopify()->getShop();
            $orders = shopify()->getAllNonSyncedOrders();
            foreach ($orders as $order){
                $this->line("");
                $this->info("id: ". $order['id'] . ", order number: " . $order['order_number'] . ", tags:" . $order['tags']);
            }
            */


        // ----------------------------------------------------------------------
        // Test code finished
        // ----------------------------------------------------------------------

        echo "\n\n";

        $time_end = microtime(true);
        $execution_time = $time_end - $time_start;

        $this->info("Tester::handle() COMPLETED in {$execution_time}");

        echo "\n\n";

        return;
    }
}