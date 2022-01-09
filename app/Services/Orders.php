<?php

namespace App\Services;

use App\Console\ConsoleCommand;
use App\Logger;
use App\ProductSkuMapper;
use Monolog\Handler\StreamHandler;

use App\Config;

class Orders
{
    public $order_endpoint = 'order';
    protected $shopifyGiftCard;
    protected $ap21customer;

    public static function SyncOrders(int $shopifyOrderId = null, $ignore_tags = false)
    {
        $logger = new Logger('App_Services_OrderSync');
        $loggerFilename = storage_path(
            'logs/App_Services_OrderSync.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        $isCli = app()->runningInConsole();
        $cli = new ConsoleCommand;

        $didWeGetOrders = true;
        if (!isset($shopifyOrderId)) {
            $filters = array();
            if (isset(store()->orders_since_id)) {
                $filters['since_id'] = store()->orders_since_id;
            }

            $offset = '-1 day';
            $filters['updated_at_min'] = date('Y-m-d H:i:s', strtotime($offset));


            if ($ignore_tags) {
                $shopify_orders = shopify()->getAllNonSyncedOrders($filters, '');
            } else {
                $shopify_orders = shopify()->getAllNonSyncedOrders($filters);
            }

            if (is_object($shopify_orders)) {
                $didWeGetOrders = false;
            }

        } else {
            $order = shopify()->getOrder($shopifyOrderId);

            if (is_object($order)) {

                $didWeGetOrders = false;
            }

            $shopify_orders = [$order];
        }

        if (!$didWeGetOrders) {
            echo "Failed to communicate with Shopify, please try again later.";
            return;
        }

        if (count($shopify_orders) <= 0) {
            echo 'Order already synced';
        }

        if ($isCli) {
            $cli->line('');
            $cli->info("There are " . count($shopify_orders) . " orders to fetch from Shopify");

            $bar = $cli->getOutput()->createProgressBar(count($shopify_orders));
            $bar->setFormat('debug');
            $bar->start();
        }

        $didErrorOccur = false;
        $syncedOrderList = array();

        $errorReports = [];
        foreach ($shopify_orders as $shopify_order) {
            if ($isCli) {
                $bar->advance();
            }
            $syncedOrderList[] = $shopify_order;
        }

        foreach ($syncedOrderList as $attachedOrder) {
            $shopifyOrderId = $attachedOrder['id'];
            $specialpromoQualify = false;
            foreach ($attachedOrder['line_items'] as $line_item) {
                
/*
 * unnecessary checking of line items
 * /
/*
    $graphQuery = '
    {
        shop {
        products(first: 1, query: "id:'.$line_item['product_id'].'") {
        edges {
            node {
              variants (first: 50){
              edges{
                  node {
                    sku
                    selectedOptions{
                      name
                      value
                    }
                    price
                    inventoryQuantity
                  }
                }
              }
              handle
              id
              title
            }
          }
        }
      }
    }
    ';
    $cli->line('graphql query:' . $graphQuery);
    $graphResult = shopify()->graph($graphQuery);
    $cli->line('');
    $cli->info('product details (from shopify graphql):');
//                    dump($graphResult);

    if (!$graphResult['errors']){
        foreach ($graphResult['body']->container['data']['shop']['products']['edges'][0]['node']['variants']['edges'] as $variant){
            $cli->line('');
            $cli->info('sku: ' . $variant['node']['sku']);
            $cli->info('price: ' . $variant['node']['price']);
            $cli->info('options: ' . json_encode($variant['node']['selectedOptions']));
            $cli->info('stock level: ' . $variant['node']['inventoryQuantity']);
        }

    } else {
        $cli->line('');
        $cli->info('failed:');
        dump($graphResult);
    }
*/


                if ($line_item['sku'] === 'h00d13') {
                    if ($isCli) {
                        $cli->line('');
                        $cli->info("this order (id: ".$shopifyOrderId.") qualifies for a free item!");
                    }
                    $specialpromoQualify = true;
                }
            }
            if ($specialpromoQualify){
                    if ($isCli){
                        $cli->line("");
                        $cli->info("about to edit order - id: " . $shopifyOrderId);
                    }
                    $calculatedOrderQuery = 'mutation beginEdit{
                         orderEditBegin(id: "gid://shopify/Order/'.$shopifyOrderId.'"){
                            calculatedOrder{
                              id
                            }
                          }
                        }
                    ';
                    $graphResult = shopify()->graph($calculatedOrderQuery);

                    if (!$graphResult['errors']){
                        if (isset($graphResult['body']->container['data']['orderEditBegin']['calculatedOrder']) &&
                            is_array($graphResult['body']->container['data']['orderEditBegin']['calculatedOrder'])){
                            if ($isCli) {
                                $cli->line("");
                                $cli->info("calculatedOrderId:" . $graphResult['body']->container['data']['orderEditBegin']['calculatedOrder']['id']);
                                $calculatedOrderId = $graphResult['body']->container['data']['orderEditBegin']['calculatedOrder']['id'];
                            }
                        } else {
                            if ($isCli) {
                                $cli->line("");
                                $cli->info("failed to get calculated order id!");
                            }
                            dump($graphResult['body']->container['data']);
                            return;
                        }
                    } else {
                        dump($graphResult['errors']);
                        return;
                    }

                //take the calculated order id - and add the new line item to it
                // $calculatedOrderId = "gid://shopify/CalculatedOrder/15972696248";

                $addVariantToOrderQuery =
                'mutation addVariantToOrder{
                    orderEditAddVariant(id: "'.$calculatedOrderId.'", variantId: "gid://shopify/ProductVariant/39557263851704", quantity: 1){
                    calculatedOrder {
                      id
                      addedLineItems(first:5) {
                      edges {
                          node {
                            id
                            quantity
                          }
                        }
                      }
                    }
                    userErrors {
                        field
                        message
                    }
                  }
                }';
                $graphResult = shopify()->graph($addVariantToOrderQuery);

                $addedLineItems = [];
                if (!$graphResult['errors']) {
                    if (isset($graphResult['body']->container['data']["orderEditAddVariant"]["calculatedOrder"]) &&
                        is_array($graphResult['body']->container['data']["orderEditAddVariant"]["calculatedOrder"]) ) {
                        $calculatedOrderId = $graphResult['body']->container['data']["orderEditAddVariant"]["calculatedOrder"]['id'];
                        foreach ($graphResult['body']->container['data']["orderEditAddVariant"]["calculatedOrder"]["addedLineItems"]["edges"] as $newLineItem) {
                            $addedLineItems[] = $newLineItem['node'];
                        }
                        $cli->line("");
                        $cli->info("new line items for calculated order:" . $calculatedOrderId);
                        dump($addedLineItems);
                    } else {
                        $cli->line("");
                        $cli->info("failed to add new line item to the order");
                        dump($graphResult['body']->container['data']);
                        return;
                    }
                } else {
                    $cli->line("");
                    $cli->info("failed to add new line item to the order");
                    dump($graphResult);
                    return;
                }

                $commitEditQuery = '
                mutation commitEdit {
                  orderEditCommit(id: "'.$calculatedOrderId.'", notifyCustomer: false, staffNote: "Added free item - based on FreebieItems Promo Rules!") {
                    order {
                      id
                    }
                    userErrors {
                      field
                      message
                    }
                  }
                }
                ';
                $graphResult = shopify()->graph($commitEditQuery);
//                dump($graphResult);

                if (!$graphResult['errors']) {
                    if (isset($graphResult['body']->container['data']['orderEditCommit']['order']) &&
                        is_array($graphResult['body']->container['data']['orderEditCommit']['order'])){
                        $cli->line("");
                        $cli->info("committed edited order - original order id: " . $graphResult['body']->container['data']['orderEditCommit']['order']['id']);
                    } else {
                        $cli->line("");
                        $cli->info("user error when committing the order!");
                        dump($graphResult['body']->container['data']);
                        return;
                    }
                } else {
                    dump($graphResult);
                    return;
                }
            }

            /*
             * tagging this order to show the special promo has been applied 
             */
            $tagsArray = explode(', ', $attachedOrder['tags']);
            $notes = $attachedOrder['note'];
            // Attach the minder sync tag
            if (!in_array(shopify()::ORDER_PROMO_TAG, $tagsArray)) {
                $tagsArray[] = shopify()::ORDER_PROMO_TAG;
            }

            $payload = [
                'id' => $shopifyOrderId,
                'tags' => implode(', ', $tagsArray),
                'note' => $notes
            ];

            $response = shopify()->updateOrder($shopifyOrderId, $payload);

}
//            echo '<pre>' . print_r($response, true) . '</pre>';
/*
if (!empty($errorReports)) {
$logger->error('Order Sync Error on AP21: ' . print_r($errorReports, true) . PHP_EOL);

//self::sendMail($errorReports);
}
*/
        if ($isCli) {
            $bar->finish();
            $cli->line('');

            if ($didErrorOccur) {
                $cli->line('');
                $cli->error("There were issues during the Order Sync Process. Please review the log - {$loggerFilename}");
            }

            $cli->line('');
            $cli->info("Shopify Order tag Updates is Complete");
            $cli->line('');
        }
    }

    public static function getPaymentData($order, $ap21OrderNumber)
    {
        $transactions = shopify()->getOrderTransactions($order['id']);

//        dd($transactions);

        // Calculate the true amount due (This is needed for correct tax calculation)
        $grandTotal = 0.0;

        foreach ($order['line_items'] as $lineItem) {
            $styleCode = null;
            $productId = null;
            $colourId = null;

            $sku_mapper = ProductSkuMapper::where('store_id', store()->id)->where('variant_id', $lineItem['variant_id'])->first();

            if ($sku_mapper) {
                $productId = $sku_mapper->ap21_product_id;
                $sku = $sku_mapper->ap21_size_id;
                $colourId = $sku_mapper->ap21_colour_id;
            }
            $qty = (int)$lineItem['quantity'];

            // CHECK FOR OVERRIDES HERE
            $product_override = ProductSkuOverride::where('sku', $lineItem['sku'])->first();

            // VOUCHERS
            $is_gift_card_product = false;
            $gift_card_product_voucher = null;

            if (!empty($product_override)) {
                $productId = $product_override->ap21_product_id;
                $sku = $product_override->ap21_size_id;
                $colourId = $product_override->ap21_colour_id;

                $gift_voucher_types = array('Gift Voucher', 'Gift Card', 'GV Email', 'Au Email1', 'Email AU', 'Email USA'); //for bcbr-120

                if (in_array($product_override->type, $gift_voucher_types)) {
                    $is_gift_card_product = true;
                }
            }

            if ($is_gift_card_product) {
                for ($counter = 1; $counter <= $qty; ++$counter) {
                    $subQty = 1;

                    $totalDiscount = 0.0;
                    foreach ($lineItem['discount_allocations'] as $discountItem) {
                        $totalDiscount += (float)$discountItem['amount'];
                    }

                    $discountForOne = $totalDiscount / $subQty;

                    $price = (float)$lineItem['price'];
                    $discountPrice = $price - $discountForOne;

                    // We do not handle the discount here because we remove the total discount below
//                    $netPrice = ($subQty * $price) - $totalDiscount;
                    $netPrice = ($subQty * $price);

                    if ($order['taxes_included']) {
                        $lineTaxRate = 0.1;
                        $lineTax = 0.0;
                    } else {
                        $lineTaxRate = 0.0;
                        $lineTax = 0.0;

                        foreach ($lineItem['tax_lines'] as $taxItem) {
                            $lineTaxRate += (float)$taxItem['rate'];
                            $lineTax += (float)$taxItem['price'];
                        }

//                        $trueLineTax = $netPrice * $lineTaxRate;
//                        $netPrice += $trueLineTax;
                    }

                    $netPrice = floor($netPrice * 100) / 100;
                    $grandTotal += $netPrice;
                }
            } else {
                $totalDiscount = 0.0;
                foreach ($lineItem['discount_allocations'] as $discountItem) {
                    $totalDiscount += (float)$discountItem['amount'];
                }

                $discountForOne = $totalDiscount / $qty;

                $price = (float)$lineItem['price'];
                $discountPrice = $price - $discountForOne;

                // We do not handle the discount here because we remove the total discount below
//                $netPrice = ($qty * $price) - $totalDiscount;
                $netPrice = ($qty * $price);

                if ($order['taxes_included']) {
                    $lineTaxRate = 0.1;
                    $lineTax = 0.0;
                } else {
                    $lineTaxRate = 0.0;
                    $lineTax = 0.0;

                    foreach ($lineItem['tax_lines'] as $taxItem) {
                        $lineTaxRate += (float)$taxItem['rate'];
                        $lineTax += (float)$taxItem['price'];
                    }

//                    $trueLineTax = $netPrice * $lineTaxRate;
//                    $netPrice += $trueLineTax;
                }

                $netPrice = round($netPrice, 2);
                $grandTotal += $netPrice;
            }
        }

        if (isset($order['total_discounts']) && !empty($order['total_discounts'])) {
            $grandTotal -= (float)$order['total_discounts'];
        }

        if ($order['taxes_included']) {
            $totalTaxRate = 0.1;
            $totalTax = 0.0;
        } else {
            $totalTaxRate = 0.0;
            $totalTax = 0.0;
            foreach ($order['tax_lines'] as $taxItem) {
                $totalTaxRate += (float)$taxItem['rate'];
                $totalTax += (float)$taxItem['price'];
            }

            $trueTotalTax = $grandTotal * $totalTaxRate;
            $grandTotal += $trueTotalTax;
        }

        // Add shipping after tax?
        if (isset($order['shipping_lines'][0]['price']) && !empty($order['shipping_lines'][0]['price'])) {
            $grandTotal += (float)$order['shipping_lines'][0]['price'];
        }

        $grandTotal = round($grandTotal, 2);

        $validTransactions = [];

        // Get all Successful Transactions
        foreach ($transactions as $transaction) {

            if ($transaction['kind'] == 'sale' &&
                $transaction['status'] == 'success'
            ) {
                $validTransactions[$transaction['id']] = $transaction;
            } else {
                // Added for handling accepted payments that are authorized and not captured
                if ($transaction['kind'] == 'authorization' &&
                    $transaction['status'] == 'success'
                ) {
                    $validTransactions[$transaction['id']] = $transaction;
                }
            }
        }

        self::validateGiftCards($transactions);

        // Remove any that are refunded
        foreach ($transactions as $transaction) {
            if ($transaction['kind'] == 'refund' &&
                $transaction['status'] == 'success'
            ) {
                unset($validTransactions[$transaction['parent_id']]);
            }
        }

        $data = ['PaymentDetail' => []];
        $currTransaction = 0;
        $numberOfTransactions = count($validTransactions);


        //BCBR-94 force insert a zero payment if grandtotal is zero and no valid transactions are detected
        if ((float)$grandTotal <= 0) {
            $paymentMap = PaymentMap::where('store_id', store()->id)->where('shopify_code', 'mindarc_zero_payment')->get();

            if ($paymentMap->count()) {
                $paymentMap = $paymentMap->first();
                $paymentDetail = [
                    'Origin' => "CreditCard",
                    'MerchantId' => $paymentMap->merchant_id,
                    'CardType' => $paymentMap->ap21_card_type,
                    'Amount' => 0,
                    'Stan' => $order['id'],
                    'Reference' => $ap21OrderNumber,
                    'Message' => "Zero Total Shopify Order ID: {$order['id']}"
                ];

                $data['PaymentDetail'][] = $paymentDetail;
            }
        }

        //END BCBR-94

        foreach ($validTransactions as $transaction) {
            ++$currTransaction;

            if ($transaction['status'] != 'success') {
                // Do not include transaction records that did not succeed
                continue;
            }

            $origin = 'CreditCard';
//            $merchantId = 'Shopify01';
            $paymentReference = $order['id'];

            switch ($transaction['gateway']) {
                case 'shopify_payments':
                    if ($order['payment_details']['credit_card_company'] == 'American Express') {
                        $cardName = 'AMEX';
                    } elseif ($order['payment_details']['credit_card_company'] == 'Visa') {
                        $cardName = 'VISA';
                    } elseif ($order['payment_details']['credit_card_company'] == 'Mastercard') {
                        $cardName = 'MASTERCARD';
                    } elseif ($order['payment_details']['credit_card_company'] == 'Diners Club') {
                        $cardName = 'DINER';
                    } else {
                        $cardName = 'SHOPIFYPAYMENTS';
                    }
                    break;
                case 'paypal':
                    $cardName = 'PP';
                    // PayPal Merchant ID for PayPal
//                    $merchantId = 'MINDARC';

                    // This needs the PayPal Transaction ID
                    $paymentReference = $transaction['authorization'];

                    break;

                //BCBR-116
                case 'afterpay_north_america':
                    $cardName = 'AFTERPAY US';

                    // This needs the AfterPay Transaction ID
                    $refCode = explode(':', $transaction['authorization']);
                    $paymentReference = $refCode[1];

                    break;

                case 'afterpay':
                    $cardName = 'AFTERPAY';
//                    $merchantId = '111111';

                    // This needs the AfterPay Transaction ID
                    $refCode = explode(':', $transaction['authorization']);
                    $paymentReference = $refCode[1];

                    break;

                case 'gift_card':
                    $origin = 'GiftVoucher';
                    $cardName = 'GiftVoucher';

                    $shopifyGiftCardId = $transaction['receipt']['gift_card_id'];
                    $giftCardObj = \App\Giftcard::where('shopify_id', $shopifyGiftCardId)->first();

                    if (empty($giftCardObj)) {
                        $origin = 'CreditCard';
                    }

                    break;
                case 'stripe':
                    $cardName = 'STRIPE';
//                    $merchantId = 'Shopify01';
                    break;
                case 'secure_pay_au':
                    $cardName = 'SECUREPAY';
//                    $merchantId = 'Shopify01';
                    break;

                case 'manual':
                    $paymentMap = PaymentMap::where('store_id', store()->id)->where('shopify_code', 'mindarc_zero_payment')->get();
                    if ($paymentMap->count()) {
                        $paymentMap = $paymentMap->first();
                        $cardName = $paymentMap->merchant_id;
                    } else {
                        $cardName = 'SHOPIFYPAYMENTS';
                    }
                    break;

                default:
                    $paymentMap = PaymentMap::where('store_id', store()->id)->where('shopify_code', 'mindarc_zero_payment')->get();
                    if ($paymentMap->count()) {
                        $paymentMap = $paymentMap->first();
                        $cardName = $paymentMap->merchant_id;
                    } else {
                        $cardName = 'SHOPIFYPAYMENTS';
                    }
//                    $merchantId = 'Shopify01';
                    break;
            }

            $transactionAmount = (float)$transaction['amount'];

            // Comment out becasue dont have currency on store.
            // Check the currency of the transaction
//             if ($transaction['currency'] != store()->currency) {
//                 // Get the exchange rate
//                 $exchangeRate = (float)$transaction['receipt']['charges']['data'][0]['balance_transaction']['exchange_rate'];
//                 $transactionAmount = $transactionAmount * $exchangeRate;
//                 $transactionAmount = round($transactionAmount, 2, PHP_ROUND_HALF_UP);

// //                dd($transactionAmount);
//             }

            // Special Logic for Tax Calculations
//            if (!$order['taxes_included']) {
//            $remainingAmount = $grandTotal - $transactionAmount;

            $remainingAmount = (float)bcsub($grandTotal, $transactionAmount);

            if (store()->hostname == 'bec-and-bridge-au.myshopify.com'
                || store()->hostname == 'bec-and-bridge-us.myshopify.com'
                || store()->hostname == 'bec-bridge-development.myshopify.com') {

                $remainingAmount = (float)bcsub($grandTotal, $transactionAmount, 2);
            }

            if ($remainingAmount < 0) {
                $transactionAmount = $grandTotal;
            }

            if ($currTransaction == $numberOfTransactions) {
                if ($remainingAmount > 0) {
                    $transactionAmount = $grandTotal;
                }
            }


            $grandTotal = $remainingAmount;
//            }

//            dd($transactionAmount, self::convert($transactionAmount));

            if (store()->hostname == 'us-billini.myshopify.com' ||
                store()->hostname == 'billini-inc.myshopify.com' ||
                store()->hostname == 'us-banbe-eyewear.myshopify.com' ||
                store()->hostname == 'bec-and-bridge-us.myshopify.com'
            ) {// US   Store
                $newTransactionAmount = 0.0;
                foreach ($order['line_items'] as $lineItem) {
                    $newTotalDiscount = 0.0;
                    $newQty = (int)$lineItem['quantity'];
                    foreach ($lineItem['discount_allocations'] as $discountItem) {
                        $newTotalDiscount += (float)$discountItem['amount'];
                    }
                    $newTotalDiscount = self::convert($newTotalDiscount);
                    $price = self::convert((float)$lineItem['price']);
                    $line_item_value = $price * (int)$newQty - $newTotalDiscount;
                    $newTransactionAmount = $newTransactionAmount + $line_item_value;
                }
                $shippingDetails = $order['shipping_lines'][0];
                $value = (float)$shippingDetails['price'];
                $discountTotal = 0.0;
                foreach ($shippingDetails['discount_allocations'] as $discountLine) {
                    $discountTotal += $discountLine['amount'];
                }

                if ($discountTotal > 0) {
                    $value -= $discountTotal;
                }
                $shipping_fee = self::convert($value);
                $newTransactionAmount = $newTransactionAmount + $shipping_fee;

                $paymentDetail = [
                    'Origin' => $origin,
//                'MerchantId' => $merchantId,
                    'CardType' => $cardName,
                    'Amount' => $newTransactionAmount,
                    'Stan' => $paymentReference,
                    'Reference' => $ap21OrderNumber,
                    'Message' => "Shopify Order ID: {$order['id']}"
                ];
            } else {

//                dump($transactionAmount, self::convert($transactionAmount));

                $paymentDetail = [
                    'Origin' => $origin,
//                'MerchantId' => $merchantId,
                    'CardType' => $cardName,
                    'Amount' => self::convert($transactionAmount),
                    'Stan' => $paymentReference,
                    'Reference' => $ap21OrderNumber,
                    'Message' => "Shopify Order ID: {$order['id']}"
                ];
            }

            $paymentMap = PaymentMap::where('store_id', store()->id)->get();

            if (count($paymentMap) > 0) {
                foreach ($paymentMap as $payment_map) {

//                    dump('here', $payment_map->shopify_code . ' = ' . $transaction['gateway']);

                    if ($payment_map->shopify_code == $transaction['gateway']) {
                        $paymentDetail['MerchantId'] = $payment_map->merchant_id;
                        $paymentDetail['CardType'] = $payment_map->ap21_card_type;
                    }
                }
            }

            if ($transaction['gateway'] == 'gift_card') {
                $shopifyGiftCardId = $transaction['receipt']['gift_card_id'];

                $giftCardObj = \App\Giftcard::where('shopify_id', $shopifyGiftCardId)->first();

                if (!empty($giftCardObj)) {
                    $giftCardObj = $giftCardObj->toArray();


                    $paymentDetail['VoucherNumber'] = $giftCardObj['voucher_number'];
                    $paymentDetail['ValidationId'] = $giftCardObj['validation_id'];
                }
            }

            $data['PaymentDetail'][] = $paymentDetail;
        }

        return $data;
    }

    public static function getBillingData($order)
    {
        $billingData = $order['billing_address'];

        $state = strtoupper($billingData['province_code']);
        if ($billingData['country_code'] == 'NZ') {
            $state = '';
        }

        $addLine1 = substr(strtoupper($billingData['address1']), 0, 50);
        $addLine2 = substr(strtoupper($billingData['address2']), 0, 50);

        $data = array(
            'ContactName' => strtoupper($billingData['first_name']) . ' ' . strtoupper($billingData['last_name']),
            'CompanyName' => strtoupper($billingData['company']),
            'AddressLine1' => $addLine1,
            'AddressLine2' => $addLine2,
            'City' => strtoupper($billingData['city']),
            'State' => $state,
            'Postcode' => $billingData['zip'],
            'Country' => strtoupper($billingData['country']),
        );

        return $data;
    }

    public static function getShippingData($order)
    {
        if (isset($order['shipping_address']) && !empty($order['shipping_address'])) {
            $shippingData = $order['shipping_address'];
        } else {
            $shippingData = $order['billing_address'];
        }

        $state = strtoupper($shippingData['province_code']);
        if ($shippingData['country_code'] == 'NZ') {
            $state = '';
        }

        $data = array(
            'ContactName' => strtoupper($shippingData['first_name']) . ' ' . strtoupper($shippingData['last_name']),
            'CompanyName' => strtoupper($shippingData['company']),
            'AddressLine1' => strtoupper($shippingData['address1']),
            'AddressLine2' => strtoupper($shippingData['address2']),
            'City' => strtoupper($shippingData['city']),
            'State' => $state,
            'Postcode' => $shippingData['zip'],
            'Country' => strtoupper($shippingData['country']),
        );

        return $data;
    }

    public static function getCourierData($order)
    {
        if (isset($order['shipping_lines'][0]) && !empty($order['shipping_lines'][0])) {
            $shippingDetails = $order['shipping_lines'][0];

            $code = strtolower($shippingDetails['code']);

            // Default Shipping Method
            $shippingId = 0;
            $shippingName = $shippingDetails['title'];

            $dCourierMap = CourierMap::where('store_id', store()->id)
                ->where('is_default', true)
                ->first();
            if ($dCourierMap) {
                $shippingId = $dCourierMap->courier_id;
                $shippingName = $dCourierMap->courier_name;
            }

            // Take the Shipping Method title and check if it exists in the map table
            $courierMap = CourierMap::where('store_id', store()->id)
                ->where('shopify_shipping_label', $shippingDetails['title'])
                ->first();
            if ($courierMap) {
                $shippingId = $courierMap->courier_id;
                $shippingName = $courierMap->courier_name;
            }

            if (!$shippingId) {
                return null;
            }

            $value = (float)$shippingDetails['price'];

            $discountTotal = 0.0;
            foreach ($shippingDetails['discount_allocations'] as $discountLine) {
                $discountTotal += $discountLine['amount'];
            }

            if ($discountTotal > 0) {
                $value -= $discountTotal;
            }

            $data = [
                'Id' => $shippingId,
                'Name' => $shippingName,
                'Value' => self::convert($value)
            ];
        } else {
            $data = [
                'Id' => '',
                'Name' => '',
                'Value' => 0
            ];
        }

        return $data;
    }

    public static function getOrderItems($order)
    {
        $data = ['OrderDetail' => []];

        foreach ($order['line_items'] as $lineItem) {
            $styleCode = null;
            $productId = null;
            $colourId = null;

            $sku_mapper = ProductSkuMapper::where('store_id', store()->id)->where('variant_id', $lineItem['variant_id'])->first();

            if ($sku_mapper) {
                $productId = $sku_mapper->ap21_product_id;
                $sku = $sku_mapper->ap21_size_id;
                $colourId = $sku_mapper->ap21_colour_id;
            }


            $qty = (int)$lineItem['quantity'];

            // CHECK FOR OVERRIDES HERE
            $product_override = ProductSkuOverride::where('sku', $lineItem['sku'])->where('store_id', store()->id)->first();

            // VOUCHERS
            $is_gift_card_product = false;
            $gift_card_product_voucher = null;
            $voucherTypeCode = '';

            if (!empty($product_override)) {
                $productId = $product_override->ap21_product_id;
                $sku = $product_override->ap21_size_id;
                $colourId = $product_override->ap21_colour_id;

                $gift_voucher_types = array('Gift Voucher', 'Gift Card', 'GV Email', 'Au Email1', 'Email AU', 'Email USA');

                if (in_array($product_override->type, $gift_voucher_types)) {
                    $is_gift_card_product = true;
                    $voucherTypeCode = $product_override->type;
                }
            }

            if (!isset($sku) || empty($sku)) {
                return false;
            }

            if ($is_gift_card_product) {
                for ($counter = 1; $counter <= $qty; ++$counter) {

                    $subQty = 1;

                    $totalDiscount = 0.0;
                    foreach ($lineItem['discount_allocations'] as $discountItem) {
                        $totalDiscount += (float)$discountItem['amount'];
                    }

                    $discountForOne = $totalDiscount / $subQty;

                    $price = (float)$lineItem['price'];
                    $discountPrice = $price - $discountForOne;

                    $netPrice = ($subQty * $price) - $totalDiscount;

                    if ($order['taxes_included']) {
                        $lineTaxRate = 0.1;
                        $lineTax = 0.0;
                    } else {
                        $lineTaxRate = 0.0;
                        $lineTax = 0.0;

                        foreach ($lineItem['tax_lines'] as $taxItem) {
                            $lineTaxRate += (float)$taxItem['rate'];
                            $lineTax += (float)$taxItem['price'];
                        }

                        $trueLineTax = $netPrice * $lineTaxRate;
                        $netPrice += $trueLineTax;
                    }

                    $netPrice = floor($netPrice * 100) / 100;

                    $orderItem = [
                        'SkuId' => $sku,
                        'Quantity' => $subQty,
                        'Price' => number_format((float)$price, 2, '.', ''),
                        'Value' => $netPrice
                    ];

                    // Modify line item for Gift Card Scenario
                    if ($is_gift_card_product) {
                        $recipientEmail = null;
                        $emailSubject = null;
                        $emailMessage = null;
                        $giftCardFrom = null;
                        $giftCardTo = null;

                        foreach ($lineItem['properties'] as $property) {
                            switch ($property['name']) {
                                case "gift card recipients email":
                                case "_gift card recipients email":
                                    $recipientEmail = $property['value'];
                                    break;

                                case "gift card email subject":
                                case "_gift card email subject":
                                    $emailSubject = $property['value'];
                                    break;

                                case "gift card message":
                                case "_gift card message":
                                    $emailMessage = $property['value'];
                                    break;

                                case "gift card from":
                                case "_gift card from":
                                    $giftCardFrom = $property['value'];
                                    break;

                                case "gift card to":
                                case "_gift card to":
                                    $giftCardTo = $property['value'];
                                    break;
                            }
                        }

                        if (empty($recipientEmail)) {
                            $recipientEmail = $order['email'];
                        }

                        $voucherInfo = [
                            'VoucherType' => $voucherTypeCode,
                            'Email' => $recipientEmail,
                            'PersonalisedMessage' => $emailMessage,
                            'TaxPercent' => ($lineTaxRate * 100),
                            'ReturnReasonId' => null
                        ];

                        if (!empty($emailSubject)) {
                            $voucherInfo['EmailSubject'] = $emailSubject;
                        }

                        $orderItem['ExtraVoucherInformation'] = $voucherInfo;
                        $orderItem['SenderName'] = $giftCardFrom;
                        $orderItem['ReceiverName'] = $giftCardTo;
                    }

                    $orderItem['TaxPercent'] = ($lineTaxRate * 100);

                    if ($totalDiscount > 0) {
                        $orderItem['Discounts'] = [
                            'Discount' => [
                                'DiscountTypeId' => 1,
                                'Value' => number_format((float)$totalDiscount, 2, '.', ''),
                            ]
                        ];
                    }

                    $data['OrderDetail'][] = $orderItem;
                }


            } else {
                $totalDiscount = 0.0;
                foreach ($lineItem['discount_allocations'] as $discountItem) {
                    $totalDiscount += (float)$discountItem['amount'];
                }

                $discountForOne = $totalDiscount / $qty;

                $price = (float)$lineItem['price'];
                $discountPrice = $price - $discountForOne;

                $netPrice = ($qty * $price) - $totalDiscount;

                if ($order['taxes_included']) {
                    $lineTaxRate = 0.1;
                    $lineTax = 0.0;
                } else {
                    $lineTaxRate = 0.0;
                    $lineTax = 0.0;

                    foreach ($lineItem['tax_lines'] as $taxItem) {
                        $lineTaxRate += (float)$taxItem['rate'];
                        $lineTax += (float)$taxItem['price'];
                    }

                    $trueLineTax = $netPrice * $lineTaxRate;
                    $netPrice += $trueLineTax;
                }

                if ($order['total_tax'] == 0) {
                    $lineTaxRate = 0.0;
                    $lineTax = 0.0;
                    foreach ($lineItem['tax_lines'] as $taxItem) {
                        $lineTaxRate += (float)$taxItem['rate'];
                        $lineTax += (float)$taxItem['price'];
                    }

                    $trueLineTax = $netPrice * $lineTaxRate;
                    $netPrice += $trueLineTax;
                }

//                $netPrice = floor($netPrice * 100) / 100;
                $netPrice = round($netPrice, 2);

                $totalDiscount = self::convert($totalDiscount);
                $price = self::convert($price);

                $line_item_value = $price * (int)$qty - $totalDiscount;

                $orderItem = [
                    'SkuId' => $sku,
                    'Quantity' => $qty,
                    'Price' => number_format((float)$price, 2, '.', ''),
                    'Value' => number_format((float)$line_item_value, 2, '.', ''),
                ];

                $orderItem['TaxPercent'] = ($lineTaxRate * 100);

                if ($totalDiscount > 0) {
                    $orderItem['Discounts'] = [
                        'Discount' => [
                            'DiscountTypeId' => 1,
                            'Value' => number_format((float)$totalDiscount, 2, '.', ''),
                        ]
                    ];
                }

                $data['OrderDetail'][] = $orderItem;
            }
        }

        return $data;
    }

    public static function convert($price)
    {
        //because everything is static, use environment config to store this currently
        if (empty(config('usd_exchange_rate'))) {
            $exchange_rate = Config::where('store_id', Store()->id)
                ->where('name', 'usd_conversion')
                ->first();

            if (!empty($exchange_rate)) {
                $exchange_rate = $exchange_rate->value;

                config(['usd_exchange_rate' => $exchange_rate]);
            }

        }

        $exchange_rate = config('usd_exchange_rate');
        //mutate the value into actual conversion rates
        if (!empty($exchange_rate)) {
            $exchange_rate = floor(1 / $exchange_rate * 1000) / 1000;
        }

        if (empty($exchange_rate)) {
            $exchange_rate = 1.587;
        }

        if (store()->hostname == 'bec-and-bridge-us.myshopify.com') // US   Store
        {
//            $result = (float)$price * $exchange_rate;

            // We do this to make sure we do not perform fractional rounding on numbers at 2 decimal places
            // Due to the nature of php fractional rounding, numbers to 2 decimal places round up
//            $numOfDecimals = strlen(substr(strrchr($result, "."), 1));
//            if ($numOfDecimals > 2) {
//                $result = ceil($result * 100) / 100;
//            }

//            $result = round($result, 2);

            $result = (float)bcmul($price, $exchange_rate, 4);
            $result = self::round_up($result, 2);

            return $result;
        }

        if (store()->hostname == 'billini-inc.myshopify.com' || store()->hostname == 'us-billini.myshopify.com' || store()->hostname == 'us-banbe-eyewear.myshopify.com') // US   Store
        {
            $result = (float)$price * $exchange_rate;

            // We do this to make sure we do not perform fractional rounding on numbers at 2 decimal places
            // Due to the nature of php fractional rounding, numbers to 2 decimal places round up
//            $numOfDecimals = strlen(substr(strrchr($result, "."), 1));
//            if ($numOfDecimals > 2) {
//                $result = ceil($result * 100) / 100;
//            }

            $result = round($result, 2);

            return $result;
        }

        return $price;
    }

    public static function round_up($value, $precision)
    {
        $pow = pow(10, $precision);
        return (ceil($pow * $value) + ceil($pow * $value - ceil($pow * $value))) / $pow;
    }

    public static function getAp21OrderNumberPrefix($store)
    {
        $prefix = "WAU";

        return $prefix;
    }

    public static function CreateOrderAP21($shopifyOrder)
    {
        $logger = new Logger('App_Services_OrderSync');
        $loggerFilename = storage_path(
            'logs/App_Services_OrderSync.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        $isCli = app()->runningInConsole();
        $cli = new ConsoleCommand;
        $didErrorOccur = false;

        if ($isCli) {
            $cli->line('');
            $cli->info("Creating Shopify Order ({$shopifyOrder['id']}) in AP21");
            $cli->line('');
        }

        if (isset($shopifyOrder['email']) && !empty($shopifyOrder['email'])) {
            $customerEmail = $shopifyOrder['email'];
        } else {
            if ($didErrorOccur) {
                $logger->error('There is no email address associated with the order. AP21 Requires an email address.');
                return false;
            }
        }

        // Get the customer data
        $shopifyCustomerId = $shopifyOrder['customer']['id'];
        $shopifyCustomer = shopify()->getCustomer($shopifyCustomerId);
        $shopifyCustomerMetaFields = shopify()->getCustomerMetaFields($shopifyCustomerId);

        $acceptsMarketing = false;
        $gender = 'ns';
        foreach ($shopifyOrder['note_attributes'] as $att) {
            if ($att['name'] === 'subscribe_to_newsletter') {
                switch ($att['value']) {
                    case 'female':
                        $acceptsMarketing = true;
                        $gender = 'female';
                        break;
                    case 'male':
                        $acceptsMarketing = true;
                        $gender = 'male';
                        break;
                    case 'optout':
                        $acceptsMarketing = false;
                        $gender = 'ns';
                        break;
                    default:
                        $acceptsMarketing = false;
                        $gender = 'ns';
                }
            }
        }

        // Get the new metafields for the customer
        $newShopifyMetafields = [];
        $genderMetafieldFound = false;
        foreach ($shopifyCustomerMetaFields as $shopifyMetafield) {
            if ($shopifyMetafield->namespace == 'information' && $shopifyMetafield->key == 'gender') {
                $shopifyMetafield->value = $gender;
                $newShopifyMetafields[] = $shopifyMetafield;
                $genderMetafieldFound = true;
            } else {
                $newShopifyMetafields[] = $shopifyMetafield;
            }
        }

        if ($genderMetafieldFound === false) {
            $newShopifyMetafields[] = [
                'namespace' => 'information',
                'key' => 'gender',
                'value' => $gender,
                'value_type' => 'string'
            ];
        }

        $customerPayload = [
            'id' => $shopifyCustomerId,
            'accepts_marketing' => $acceptsMarketing,
            'metafields' => $newShopifyMetafields
        ];
        $shopifyCustomer = shopify()->updateCustomer($shopifyCustomerId, $customerPayload);

        if ($isCli) {
            $cli->line('');
            $cli->info("Retrieving Customer From AP21");
        }

        $customer_data = json_decode(json_encode($shopifyCustomer), true);

//        if(store()->hostname == 'bassike-ows.myshopify.com'){
//            if(!isset($customer_data['email']) || empty($customer_data['email'])) {
//                if ($isCli) {
//                    $customer_data['email'] = 'ows@bassike.com';
//                    $customerEmail = 'ows@bassike.com';
//
//                }
//            }
//        }

        $result = \App\Services\Customers::CreateUpdateCustomer($customer_data);

        if (isset($result['success']) && $result['success'] === false) {
            return $result;
        }

        if ($result == false) {
            $logger->error('Unable to create/update customer in AP21');
            return false;
        }


        $ap21Customer = ap21()->getCustomersModel()->getCustomer($customerEmail);

        // Perform Logic to validate any gift cards that are being used
        $ap21OrderNumber = $shopifyOrder['name'];
        $ap21OrderNumber = "$ap21OrderNumber";

        $phone = null;
        if (isset($shopifyOrder['customer']['default_address']['phone']) &&
            !empty($shopifyOrder['customer']['default_address']['phone'])) {
            $phone = $shopifyOrder['customer']['default_address']['phone'];
        }

        $personId = (int)$ap21Customer['Id'];
        $data = array(
            'PersonId' => $personId,
            'OrderNumber' => $ap21OrderNumber,
            'Contacts' => array(
                'Email' => $customerEmail
            ),
        );

        if (!empty($phone)) {
            $data['Contacts']['Phones']['Home'] = $phone;
        }

        if ($shopifyOrder['taxes_included'] == false) {
            $data['PricesIncludeTax'] = 'false';
        }

        // Attach any notes as delivery notes
        $deliveryInstructionNote = '';
        $orderNotes = explode(PHP_EOL, $shopifyOrder['note']);
        if (count($orderNotes) > 0) {
            $isDeliveryLine = false;
            foreach ($orderNotes as $line) {
                if (substr($line, 0, strlen('DN:')) === 'DN:') {
                    $isDeliveryLine = true;
                    $tempLine = explode(':', $line);

                    if (!empty($deliveryInstructionNote)) {
                        $deliveryInstructionNote .= "\n";
                    }

                    $deliveryInstructionNote .= trim($tempLine[1]);

                    // move to the next line
                    continue;
                }

                if ($isDeliveryLine) {
                    if (substr($line, 0, strlen('Person ID:')) === 'Person ID:' ||
                        substr($line, 0, strlen('Order Number:')) === 'Order Number:' ||
                        substr($line, 0, strlen('Order ID:')) === 'Order ID:' ||
                        $line == 'AUTHORITY TO LEAVE') {
                        $isDeliveryLine = false;

                        // move to the next line
                        continue;
                    } else {
                        if (!empty($deliveryInstructionNote)) {
                            $deliveryInstructionNote .= "\n";
                        }

                        $deliveryInstructionNote .= trim($line);
                    }
                }
            }
        }

        if (!empty($deliveryInstructionNote)) {
            $deliveryInstructionNote = str_replace("\n", ' ', $deliveryInstructionNote);
            $deliveryInstructionNote = substr($deliveryInstructionNote, 0, 250);
            $data['DeliveryInstructions'] = $deliveryInstructionNote;
        }

        // Check if the customer has applied Authority to Leave
        $authorityToLeave = false;
        $orderNotes = explode(PHP_EOL, $shopifyOrder['note']);
        if (count($orderNotes) > 0) {
            foreach ($orderNotes as $line) {
                if (substr($line, 0, strlen('Person ID:')) === 'Person ID:' ||
                    substr($line, 0, strlen('Order Number:')) === 'Order Number:' ||
                    substr($line, 0, strlen('Order ID:')) === 'Order ID:' ||
                    substr($line, 0, strlen('DN:')) === 'DN:'
                ) {
                    continue;
                }

                if (strpos(strtoupper($line), 'AUTHORITY TO LEAVE') !== false) {
                    $authorityToLeave = true;
                    break;
                }
            }
        }

        if ($authorityToLeave) {
            $data['UnattendedDeliveryOption'] = 'AuthorityToLeave';
        }

        if (empty($shopifyOrder['shipping_address'])) {
            $shopifyOrder['shipping_address'] = $shopifyOrder['billing_address'];
        }

        $orderItems = self::getOrderItems($shopifyOrder);

        if ($orderItems === false) {
            $result = [
                'success' => false,
                'message' => 'Unable to find the order item in AP21'
            ];
            return $result;
        }

        $data['Addresses']['Billing'] = self::getBillingData($shopifyOrder);
        $data['Addresses']['Delivery'] = self::getShippingData($shopifyOrder);
        $data['OrderDetails'] = $orderItems;
        $data['SelectedFreightOption'] = self::getCourierData($shopifyOrder);
        $data['Payments'] = self::getPaymentData($shopifyOrder, $ap21OrderNumber);

        $ap21Account = store()->getAp21Account();
        $countryCode = $ap21Account->getAp21CountryCode($shopifyOrder['shipping_address']['country_code']);


        if ($shopifyOrder['shipping_address']['country_code'] == 'NZ') {
            $countryCode = 'NZ';
        }


        if ($isCli) {
            $cli->line('');
            $cli->info("Data Ready, Creating Order in AP21");
        }

//        dd(store()->hostname);

//        if (store()->hostname == 'us-banbe-eyewear.myshopify.com') {
//            dd( json_encode($data));
//        }

//        if ($isCli) {
//            dump($data);
//        }

//        echo '<pre> Payload' . print_r($data, true) . '</pre>';
        $response = ap21()->getOrdersModel()->createOrder($personId, $data, $countryCode);


        $ap21Data = array(
            'success' => $response['success'],
            'person_id' => $personId,
            'order_number' => $ap21OrderNumber,
            'message' => $response['message'],
        );

        if ($response['success'] == 'true') {
            $response['success'] = 'true';
        }
        if (isset($response['message']['created_order'])) {
            $ap21Data['order_id'] = $response['message']['created_order']['order_id'];
        }

//        echo 'AP21 DATA: ' . print_r($ap21Data, true) . PHP_EOL;

        if ($response['success'] == 'false') {
            $debugObj = [
                'store' => store()->toArray(),
                'shopify_order' => $shopifyOrder,
                'person_id' => $personId,
                'country_code' => $countryCode,
                'payload' => $data,
                'response' => $response
            ];
            $logger->error('Error on Order Create: ' . $response['message'], $debugObj);
        }
        return $ap21Data;
    }

    public static function validateGiftCards($transactions)
    {
//        $transactions = $this->shopify->getOrderHelper()->getOrderTransactions($order['id']);

        $giftCardTransactions = [];

        // Get all successful Gift Cards
        foreach ($transactions as $transaction) {
            if ($transaction['gateway'] == 'gift_card' &&
                $transaction['kind'] == 'sale' &&
                $transaction['status'] == 'success'
            ) {
                $giftCardTransactions[$transaction['id']] = $transaction;
            }
        }

        // Remove any that are refunded
        foreach ($transactions as $transaction) {
            if ($transaction['gateway'] == 'gift_card' &&
                $transaction['kind'] == 'refund' &&
                $transaction['status'] == 'success'
            ) {
                unset($giftCardTransactions[$transaction['parent_id']]);
            }
        }

        foreach ($giftCardTransactions as $transaction) {
            $shopifyGiftCardId = $transaction['receipt']['gift_card_id'];

            $giftCardObj = \App\Giftcard::where('shopify_id', $shopifyGiftCardId)->first();


            if (!empty($giftCardObj)) {
                $giftCardObj = $giftCardObj->toArray();
//            $giftCardObj = $this->integration::$db
//                ->where('shopify_id', $shopifyGiftCardId)->get('giftcards_sync')[0];

                $msg = "Validating Gift Card with AP21 - No: {$giftCardObj['voucher_number']} Pin: {$giftCardObj['voucher_pin']} Amount: {$transaction['amount']}";
//                echo $msg;

                // Validate Gift Card
                $ap21ValidatedGiftCard = ap21()->getGiftCardModel()
                    ->validateGiftVoucher(
                        $giftCardObj['voucher_number'],
                        $giftCardObj['voucher_pin'],
                        $transaction['amount']
                    );

//                $msg = "AP21 Gift Card: " . print_r($ap21ValidatedGiftCard, true);
//                echo $msg;

                $ap21ValidatedGiftCard = $ap21ValidatedGiftCard['message'];

                $giftCardObj = \App\Giftcard::where('shopify_id', $shopifyGiftCardId)->first();
                try {
                    $giftCardObj->validation_id = $ap21ValidatedGiftCard['ValidationId'];
                } catch (\Exception $e) {
                    $logger = new Logger('App_Services_OrderSync');
                    $logger->error($msg);
                    $logger->error('Error on Order Giftcard Validate: ' . $e->getMessage() . print_r($ap21ValidatedGiftCard, true));
                }

                $giftCardObj->save();
            }
        }
    }

    public static function sendMail(array $errorReports)
    {
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
        $headers .= "X-Priority: 1 (Highest)\n";
        $headers .= "X-MSMail-Priority: High\n";
        $headers .= "Importance: High\n";
        $headers .= 'From: ' . 'MindArc Integrations <notifications@shopifyap21app.testarc.com.au>' . "\r\n";

        $to = implode(',', store()->getContactEmails());

        if (empty($to)) {
            return;
        }

        $errorMsg = '';

        foreach ($errorReports as $errorReport) {
            $jsonString = '<pre>' . json_encode($errorReport['response'], JSON_PRETTY_PRINT) . '</pre>';

            $orderUrl = 'https://' . store()->hostname . '/admin/orders/' . $errorReport['shopify_order']['id'];

            $errorMsg .= "Failed to Sync Order <a href='{$orderUrl}' target='_blank'>{$errorReport['shopify_order']['name']}</a> to AP21" . '<br />';
            $errorMsg .= "Store: " . store()->hostname . '<br /><br />';
            $errorMsg .= $jsonString . PHP_EOL;
            $errorMsg .= '<br /><br /><hr><br /><br />';
        }

        $subject = 'Error has occured during a Order Sync to AP21.';


        $message = '
            <html>
            <head>
              <title>Integration Error has occured</title>
            </head>
            <body>
              <p>' . $errorMsg . '</p>
            </body>
            </html>
        ';
        $status = mail($to, $subject, $message, $headers);
    }
}