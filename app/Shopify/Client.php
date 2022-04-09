<?php

namespace App\Shopify;

//use OhMyBrew\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;
use App\Console\ConsoleCommand;

class Client extends BasicShopifyAPI
{
    protected $isCli = false;
    protected $cli;

    private $shop;
    private $accesstoken;

    const ORDER_SYNC_TAG = "testsync";
    const ORDER_PROMO_TAG = "PromoOrder";

    public function __construct()
    {
        $this->isCli = app()->runningInConsole();
        $this->cli = new ConsoleCommand();

        $options = new Options();
        $options->setVersion('2021-10');
        return parent::__construct($options);
    }

    public function setShop($hostname){
        $this->shop = $hostname;
    }
    public function setAccessToken($access_token)
    {
        $this->accesstoken = $access_token;
    }
    public function setVersion($version)
    {
        $this->options->setVersion($version);
    }
    public function startSession(){
        $this->setSession(new Session($this->shop, $this->accesstoken));
    }

    public function getAllNonSyncedOrders(array $filter = null, $tagToCheck = self::ORDER_SYNC_TAG)
    {
        if (is_array($filter) && !empty($filter)) {
            if (isset($filter['fields'])) {
                unset($filter['fields']);
            }
        } else {
            $filter = [];
        }

        $filter['fields'] = 'id,tags';
        $filter['status'] = 'any';
        $shopifyOrders = $this->getAllOrders($filter);

        if (is_object($shopifyOrders) && $shopifyOrders->errors) {
            return $shopifyOrders;
        }

        if ($this->isCli) {
            $this->cli->line('');
            $this->cli->info("Filtering by 'Synced with Integration' tag:");
            $bar = $this->cli->getOutput()->createProgressBar(count($shopifyOrders));
            $bar->setFormat('debug');
            $bar->start();
        }

        $nonSyncedShopifyOrderIds = [];
        foreach ($shopifyOrders as $shopifyOrder) {

            $tags = explode(', ', $shopifyOrder['tags']);
            if ($tagToCheck != '') {
                if (!in_array($tagToCheck, $tags)) {
                    $nonSyncedShopifyOrderIds[] = $shopifyOrder['id'];
                }
            }

            if ($this->isCli) {
                $bar->advance();
            }
        }

        if ($this->isCli) {
            $bar->finish();
            $this->cli->line('');
        }

        // Get the orders in lots of 50
        // This is to make sure we do not exceed any URL character limits imposed by servers
        $limit = 50;
        $nonSyncedShopifyOrderIdBlocks = [];
        $blockCounter = 0;
        $indexCounter = 0;
        foreach ($nonSyncedShopifyOrderIds as $nonSyncedShopifyOrderId) {
            if ($indexCounter >= $limit) {
                ++$blockCounter;
                $indexCounter = 0;
            }

            ++$indexCounter;

            $nonSyncedShopifyOrderIdBlocks[$blockCounter][] = $nonSyncedShopifyOrderId;
        }

        if ($this->isCli) {
            $this->cli->line('');
            $this->cli->info("Fetching available order details:");
            $bar = $this->cli->getOutput()->createProgressBar(count($nonSyncedShopifyOrderIdBlocks));
            $bar->setFormat('debug');
            $bar->start();
        }

        $orders = [];
        foreach ($nonSyncedShopifyOrderIdBlocks as $nonSyncedShopifyOrderIdBlock) {
            $subFilter = [
                'ids' => implode(',', $nonSyncedShopifyOrderIdBlock),
                'status' => 'any'
            ];

            $response = $this->getOrders($subFilter, true);

            // If the current request fails, stop fetching and return the error
            if ($response['errors'] === true) {
                return $response;
            }

            $responseOrders = $response['body']->container['orders'];

            $orders = array_merge($orders, $responseOrders);

            if ($this->isCli) {
                $bar->advance();
            }
        }

        if ($this->isCli) {
            $bar->finish();
            $this->cli->line('');
        }

        return $orders;
    }

    /**
     * Product Specific Shopify Endpoints
     */

    public function countProductsAndVariants()
    {
        if ($this->isCli) {
            $this->cli->line('');
            $this->cli->info('Counting the total number of Products in Shopify');
        }

        $shopifyProducts = $this->getAllProducts();

        $variants = [];
        foreach ($shopifyProducts as $shopifyProduct) {
            foreach ($shopifyProduct['variants'] as $shopifyVariant) {
                $variants[] = $shopifyVariant;
            }
        }

        $result = [
            'product_count' => count($shopifyProducts),
            'variant_count' => count($variants)
        ];

        if ($this->isCli) {
            $this->cli->line('');
            $this->cli->info("There are {$result['product_count']} Products in Shopify");
            $this->cli->info("There are {$result['variant_count']} Variants in Shopify");
            $this->cli->line('');
        }

        return $result;
    }

    public function getAllProducts(array $filter = [])
    {
        // Remove any page indexing as it is no longer a valid filter parameter
        if (isset($filter['page']) && !empty($filter['page'])) {
            unset($filter['page']);
        }

        // Set the number of products per page
        $productsPerPage = 250;
        if (isset($filter['limit']) && !empty($filter['limit'])) {
            $productsPerPage = (int)$filter['limit'];
            unset($filter['limit']);
        }

        $numOfProducts = $this->getProductCount($filter);

        if (is_object($numOfProducts) && $numOfProducts->errors) {
            return $numOfProducts;
        }

        $numOfProductPages = ceil($numOfProducts / $productsPerPage);

        if ($this->isCli) {
            $this->cli->line('');
            $this->cli->info("Fetching products from Shopify by pages:");
            $bar = $this->cli->getOutput()->createProgressBar($numOfProductPages);
            $bar->setFormat('debug');
            $bar->start();
        }

        $products = [];
        $nextPageId = false;
        $count = 0;
        do {
            $data = ['limit' => $productsPerPage];

            if ($nextPageId) {
                $data = [
                    'page_info' => $nextPageId,
                    'limit' => $productsPerPage
                ];
            }

            $data += $filter;
            if ($count > 0) {
                if (isset($filter['published_status'])) {
                    unset($data['published_status']);
                }
            }
            $response = $this->getProducts($data, true);

            if (isset($response->link->next)) {
                $nextPageId = $response->link->next;
            } else {
                $nextPageId = false;
            }

            // If the current request fails, stop fetching and return the error
            if ($response['errors']) {
                return $response;
            }

            $responseProducts = $response['body']->container['products'];

            $products = array_merge($products, $responseProducts);

            if ($this->isCli) {
                $bar->advance();
            }
            $count++;
        } while ($nextPageId !== false);

        if ($this->isCli) {
            $bar->finish();
            $this->cli->line('');
        }

        return $products;
    }

    public function getProduct(int $id, bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/products/' . $id . '.json');

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }


        $responseProducts = json_decode(
            json_encode($response->body->product),
            true
        );

        return $responseProducts;
    }

    public function getProductMetafields(int $id, bool $getFullResponse = false)
    {
        $response = $this->rest('GET', "/admin/products/{$id}/metafields.json");

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $responseMetafields = json_decode(
            json_encode($response->body->metafields),
            true
        );

        return $responseMetafields;
    }

    public function getProductVariant(int $id, bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/variants/' . $id . '.json');
        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }
        $responseProducts = json_decode(
            json_encode($response->body->variant),
            true
        );

        return $responseProducts;
    }

    public function getProducts(array $filter = [], bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/products.json', $filter);

        if ($getFullResponse) {
            return $response;
        }

        if ($response["errors"]) {
            return $response;
        }

        $responseProducts = json_decode(
            json_encode($response->body),
            true
        );

        return $responseProducts;
    }

    public function getProductCount(array $filter = [], bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/products/count.json', $filter);

        if ($getFullResponse) {
            return $response;
        }

        if ($response['errors']) {
            return $response;
        }
        return $response['body']->container['count'];
    }

    /**
     * Inventory Specific Endpoints
     */

    public function setInventoryLevel(int $locId, int $invItemId, int $qty, bool $getFullResponse = false)
    {
        $payload = [
            'location_id' => $locId,
            'inventory_item_id' => $invItemId,
            'available' => $qty
        ];
        $response = $this->rest('POST', '/admin/inventory_levels/set.json', $payload);


        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $responseInventoryLevel = json_decode(
            json_encode($response->body->inventory_level),
            true
        );

        return $responseInventoryLevel;
    }

    public function updateInventoryItem($id, $data, bool $getFullResponse = false)
    {
        $inventoryData['inventory_item'] = $data;

        $response = $this->rest('PUT', '/admin/inventory_items/' . $id . '.json', $inventoryData);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $responseInventoryItem = json_decode(
            json_encode($response->body->inventory_item),
            true
        );

        return $responseInventoryItem;
    }

    public function getLocations(bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/locations.json');

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $responseLocations = json_decode(
            json_encode($response->body->locations),
            true
        );

        return $responseLocations;
    }

    /**
     * Order Specific Endpoints
     */

    public function updateOrder(int $id, array $payload, bool $getFullResponse = false)
    {
        $data = ['order' => $payload];
        $response = $this->rest('PUT', '/admin/orders/' . $id . '.json', $data);

        if ($getFullResponse) {
            return $response;
        }

        if ($response['errors']) {
            return $response;
        }

        $responseOrder = $response['body']->container['order'];
        return $responseOrder;
    }

    public function getAllOrders(array $filter = [])
    {
        // Remove any page indexing as it is no longer a valid filter parameter
        if (isset($filter['page']) && !empty($filter['page'])) {
            unset($filter['page']);
        }

        // Set the number of products per page
        $ordersPerPage = 250;
        if (isset($filter['limit']) && !empty($filter['limit'])) {
            $ordersPerPage = (int)$filter['limit'];
            unset($filter['limit']);
        }

        $numOfOrders = $this->getOrdersCount($filter);

        if (is_object($numOfOrders) && $numOfOrders->errors) {
            return $numOfOrders;
        }

        $numOfOrderPages = ceil($numOfOrders / $ordersPerPage);

        if ($this->isCli) {
            $this->cli->line('');
            $this->cli->info("Fetching orders from Shopify by pages:");
            $bar = $this->cli->getOutput()->createProgressBar($numOfOrderPages);
            $bar->setFormat('debug');
            $bar->start();
        }

        $orders = [];
        $nextPageId = false;
        $count = 0;
        do {
            $data = ['limit' => $ordersPerPage];

            if ($nextPageId) {
                $data = ['page_info' => $nextPageId];
            }

            $data += $filter;
            if ($count > 0){
                if (isset($filter['created_at_min'])) {
                    unset($data['created_at_min']);
                }

                if (isset($filter['updated_at_min'])) {
                    unset($data['updated_at_min']);
                }

                if (isset($filter['created_at_max'])) {
                    unset($data['created_at_max']);
                }
                if (isset($filter['since_id'])) {
                    unset($data['since_id']);
                }
                if (isset($filter['ids'])) {
                    unset($data['ids']);
                }
                if (isset($filter['financial_status'])) {
                    unset($data['financial_status']);
                }
                if (isset($filter['fulfillment_status'])) {
                    unset($data['fulfillment_status']);
                }
                if (isset($filter['status'])) {
                    unset($data['status']);
                }
            }


            $response = $this->getOrders($data, true);

            if (isset($response->link->next)) {
                $nextPageId = $response->link->next;
            } else {
                $nextPageId = false;
            }

            // If the current request fails, stop fetching and return the error
            if ($response['errors'] === true) {
                return $response;
            }

            $responseOrders = $response['body']->container['orders'];

            $orders = array_merge($orders, $responseOrders);

            if ($this->isCli) {
                $bar->advance();
            }
            $count++;
        } while ($nextPageId !== false);

        if ($this->isCli) {
            $bar->finish();
            $this->cli->line('');
        }

        return $orders;
    }

    public function getOrders(array $filter = [], bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/orders.json', $filter);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->orders;
    }

    public function getOrder(int $id, bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/orders/' . $id . '.json');

        if ($getFullResponse) {
            return $response;
        }

        if ($response['errors']) {
            return $response;
        }
        $responseOrder = $response['body']->container['order'];

        return $responseOrder;
    }

    public function getOrdersCount(array $filter = [], bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/orders/count.json', $filter);

        if ($getFullResponse) {
            return $response;
        }

        if ($response['errors'] === true) {
            return $response;
        }

        return $response['body']->container['count'];
    }

    public function getOrderTransactions(int $id, bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/orders/' . $id . '/transactions.json');

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $array_data = json_decode(json_encode($response->body->transactions), true);

        return $array_data;
    }

    public function setFulfillment($data, bool $getFullResponse = false)
    {
        $orderId = $data['id'];
        unset($data['id']);

        $fulfillmentPayload = ['fulfillment' => $data];

        $response = $this->rest('POST', '/admin/orders/' . $orderId . '/fulfillments.json', $fulfillmentPayload);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $array_data = json_decode(json_encode($response->body->fulfillment), true);

        return $array_data;
    }

    /**
     * Customer Specific Endpoints
     */

    public function searchCustomers(array $filter = [], bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/customers/search.json', $filter);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->customers;
    }

    public function getAllCustomers(array $filter = [])
    {
        // Remove any page indexing as it is no longer a valid filter parameter
        if (isset($filter['page']) && !empty($filter['page'])) {
            unset($filter['page']);
        }

        // Set the number of products per page
        $customersPerPage = 250;
        if (isset($filter['limit']) && !empty($filter['limit'])) {
            $customersPerPage = (int)$filter['limit'];
            unset($filter['limit']);
        }

        $numOfCustomers = $this->getCustomerCount($filter);

        if (is_object($numOfCustomers) && $numOfCustomers->errors) {
            return $numOfCustomers;
        }

        $numOfCustomerPages = ceil($numOfCustomers / $customersPerPage);

        if ($this->isCli) {
            $this->cli->line('');
            $this->cli->info("Fetching customers from Shopify by pages:");
            $bar = $this->cli->getOutput()->createProgressBar($numOfCustomerPages);
            $bar->setFormat('debug');
            $bar->start();
        }

        $customers = [];
        $nextPageId = false;
        do {
            $data = ['limit' => $customersPerPage];

            if ($nextPageId) {
                $data = ['page_info' => $nextPageId];
            }

            $data += $filter;

            $response = $this->getCustomers($data, true);

            if (isset($response->link->next)) {
                $nextPageId = $response->link->next;
            } else {
                $nextPageId = false;
            }

            // If the current request fails, stop fetching and return the error
            if ($response->errors) {
                return $response;
            }

            $responseCustomers = json_decode(
                json_encode($response->body->customers),
                true
            );

            $customers = array_merge($customers, $responseCustomers);

            if ($this->isCli) {
                $bar->advance();
            }
        } while ($nextPageId !== false);

        if ($this->isCli) {
            $bar->finish();
            $this->cli->line('');
        }

        return $customers;
    }

    public function createCustomerMetaField($id, array $data = [], bool $getFullResponse = false)
    {
        $data = ['metafield' => $data];
        $response = $this->rest('POST', "/admin/customers/$id/metafields.json",$data);


        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->metafield;
    }

    public function updateCustomerMetaField(int $customerId, int $metaFieldId, $data, bool $getFullResponse = false)
    {
        $data = ['metafield' => $data];
        $response = $this->rest('PUT', "/admin/customers/$customerId/metafields/$metaFieldId.json",$data);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->metafield;
    }

    public function sendCustomerInvite($customer_id, $getFullResponse = false){
        $filter = array();
        $filter['customer_invite'] = '';

        $response = $this->rest('POST', '/admin/customers/' . $customer_id . '/send_invite.json', $filter);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->customer_invite;
    }


    public function getCustomers(array $filter = [], bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/customers.json', $filter);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->customers;
    }

    public function getCustomerByEmail($email, bool $getFullResponse = false)
    {
        $data = array('query' => 'email:' . $email);
        $response = $this->rest('GET', '/admin/customers/search.json', $data);

        $customers = $response->body->customers;
        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }
        foreach ($customers as $customer) {
            if ($customer->email == $email) {
                return $customer;
            }
        }

        return false;
    }

    public function getCustomerCount(array $filter = [], bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/customers/count.json', $filter);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->count;
    }

    public function getCustomer(int $id, bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/customers/' . $id . '.json');

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->customer;
    }

    public function createCustomer(array $payload, bool $getFullResponse = false)
    {
        $data = ['customer' => $payload];
        $response = $this->rest('POST', '/admin/customers.json', $data);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->customer;
    }

    public function updateCustomer(int $id, array $payload, bool $getFullResponse = false)
    {
        $data = ['customer' => $payload];
        $response = $this->rest('PUT', '/admin/customers/' . $id . '.json', $data);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->customer;
    }

    public function getCustomerMetaFields(int $id, bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/customers/' . $id . '/metafields.json');

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->body->metafields;
    }

    /**
     * Shop Specific Endpoints
     */

    public function getShop(array $filter = null, bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/shop.json', $filter);

        if ($getFullResponse) {
            return $response;
        }

        if (isset($response["errors"]) && $response["errors"] === true) {
            return $response;
        }
        /*
        $responseShop = json_decode(
            json_encode($response->body->shop),
            true
        );
        */

        $responseShop = $response["body"]->container["shop"];

        return $responseShop;
    }

    /**
     * Webhook Specific Endpoints
     */

    public function getAllWebhooks(array $filter = [])
    {
        // Remove any page indexing because we are fetching everything
        if (isset($filter['page']) && !empty($filter['page'])) {
            unset($filter['page']);
        }

        // Set the number of products per page
        $webhooksPerPage = 250;
        if (isset($filter['limit']) && !empty($filter['limit'])) {
            $webhooksPerPage = (int)$filter['limit'];
            unset($filter['limit']);
        }

        $numOfWebhooks = $this->getWebhookCount($filter);
        if (is_object($numOfWebhooks) && $numOfWebhooks->errors) {
            return $numOfWebhooks;
        }
        $numOfWebhookPages = ceil($numOfWebhooks / $webhooksPerPage);

        if ($this->isCli) {
            $this->cli->line('');
            $this->cli->info("Fetching webhooks from Shopify by pages:");
            $bar = $this->cli->getOutput()->createProgressBar($numOfWebhookPages);
            $bar->setFormat('debug');
            $bar->start();
        }

        $webhooks = [];
        $nextPageId = false;
        do {
            $data = ['limit' => $webhooksPerPage];

            if ($nextPageId) {
                $data = ['page_info' => $nextPageId];
            }

            $data += $filter;

            $response = $this->getWebhooks($data, true);

            if (isset($response->link->next)) {
                $nextPageId = $response->link->next;
            } else {
                $nextPageId = false;
            }

            // If the current request fails, stop fetching and return the error
            if ($response['errors']) {
                return $response;
            }
            $responseWebhooks = $response['body']->container['webhooks'];
            $webhooks = array_merge($webhooks, $responseWebhooks);

            if ($this->isCli) {
                $bar->advance();
            }
        } while ($nextPageId !== false);

        if ($this->isCli) {
            $bar->finish();
            $this->cli->line('');
        }

        return $webhooks;
    }

    public function getWebhooks(array $filter = [], bool $getFullResponse = false)
    {
        $response = $this->rest('GET', "/admin/webhooks.json", $filter);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $responseWebhooks = json_decode(
            json_encode($response->body->webhooks),
            true
        );

        return $responseWebhooks;
    }

    public function getWebhookCount(array $filter = [], bool $getFullResponse = false)
    {
        $response = $this->rest('GET', "/admin/webhooks/count.json", $filter);
        if ($getFullResponse) {
            return $response;
        }

        if ($response['errors']) {
            return $response;
        }

        return $response['body']->container['count'];
    }

    public function getWebhook(int $id, bool $getFullResponse = false)
    {
        $response = $this->rest('GET', "/admin/webhooks/{$id}.json");

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $responseWebhook = json_decode(
            json_encode($response->body->webhook),
            true
        );

        return $responseWebhook;
    }

    public function createWebhookJson(string $topic, string $endpoint, bool $getFullResponse = false)
    {
        $payload = [
            'topic' => $topic,
            'address' => $endpoint,
            'format' => 'json'
        ];
        return $this->createWebhook($payload, $getFullResponse);
    }

    public function createWebhook(array $payload, bool $getFullResponse = false)
    {
        $data = ['webhook' => $payload];
        $response = $this->rest('POST', '/admin/webhooks.json', $data);

        if ($getFullResponse) {
            return $response;
        }

        if ($response['errors']) {
            return $response;
        }

        $responseWebhook = $response['body']->container['webhook'];

        return $responseWebhook;
    }

    public function updateWebhook(int $id, array $payload, bool $getFullResponse = false)
    {
        $data = ['webhook' => $payload];
        $response = $this->rest('PUT', "/admin/webhooks/{$id}.json", $data);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $responseWebhook = json_decode(
            json_encode($response->body->webhook),
            true
        );

        return $responseWebhook;
    }

    public function deleteWebhook(int $id, bool $getFullResponse = false)
    {
        $response = $this->rest('DELETE', "/admin/webhooks/{$id}.json");

        if ($getFullResponse) {
            return $response;
        }

        if ($response['errors']) {
            return $response;
        }

        if ($response['response']->getStatusCode() === 200) {
            return true;
        }

        return false;
    }


    /* GIFT CARDS */
    public function getAllGiftCards(array $data = null, bool $getFullResponse = false)
    {
        $giftCardsPerPage = 250;
        $numOfGiftCards = $this->getGiftCardCount();
        $numOfGiftCardPages = ceil($numOfGiftCards / $giftCardsPerPage);

        $giftCards = [];

        for ($i = 1; $i <= $numOfGiftCardPages; ++$i) {
            $data = [
                'limit' => $giftCardsPerPage,
                'page' => $i
            ];

            $response = (array)$this->rest('GET', '/admin/gift_cards.json', $data);

            if ($getFullResponse) {
                return $response;
            }

            if ($response->errors) {
                return $response;
            }

            $response = json_decode(json_encode($response), true);
            $response = $response['gift_cards'];


            $giftCards = array_merge($giftCards, $response);
        }

        return $giftCards;
    }

    public function getGiftCard(int $id, bool $getFullResponse = false)
    {

        $response = $this->rest('GET', "/admin/gift_cards/{$id}.json");

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }


        $responseData = json_decode(
            json_encode($response->body->gift_card),
            true
        );

        return $responseData;
    }

    public function getGiftCardCount(bool $getFullResponse = false)
    {
        $response = $this->rest('GET', '/admin/gift_cards/count.json');

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        return $response->count;
    }

    public function createGiftCard(array $payload, bool $getFullResponse = false)
    {
        $data = ['gift_card' => $payload];
        $response = $this->rest('POST', '/admin/gift_cards.json', $data);

        if ($getFullResponse) {
            return $response;
        }

        if ($response->errors) {
            return $response;
        }

        $responseGiftCard = json_decode(
            json_encode($response->body->gift_card),
            true
        );

        return $responseGiftCard;
    }

    public function updateGiftCard($id, $data)
    {
        $giftCardSent['gift_card'] = $data;

        try {
            $result = $this->rest('PUT', '/admin/gift_cards/' . $id . '.json', $giftCardSent);
            $result = json_decode(json_encode($result), true);

            if (isset($result['gift_card']) && !empty($result['gift_card'])) {
                return $result['gift_card'];
            }
        } catch (\Exception $e) {
            $response = $e->getResponse();
            $result = json_decode($response->getBody()->getContents());
        }

        return $result;
    }

    public function disableGiftCard(int $id)
    {
        $giftCardSent = [
            'gift_card' => ['id' => $id]
        ];

        $result = $this->rest('POST', "/admin/gift_cards/$id/disable.json", $giftCardSent);
        $result = json_decode(json_encode($result), true);

        return $result;
    }

}
