<?php

namespace App\Services;

use App\Console\ConsoleCommand;
use App\Logger;
use Monolog\Handler\StreamHandler;

class WebhookInit
{
    static public function manage()
    {
        $logger = self::getLogger();

        $isCli = app()->runningInConsole();
        $cli = new ConsoleCommand;

        $storeId = store()->id;

        if ($isCli) {
            $cli->line('');
            $cli->info("Commencing Shopify Webhook Management Process for Store ID {$storeId}");
        }

        // Retrieve a list of topics that this App requires event listening for
        $requiredShopifyTopics = getShopifyTopics();


        if ($isCli) {
            $cli->line('');
            if (is_array($requiredShopifyTopics) && count($requiredShopifyTopics) > 0) {
                $cli->info("The following Shopify Topic Webhook Listeners are required:");
                $tableHeaders = ['Topic'];

                $tableData = [];
                foreach ($requiredShopifyTopics as $topic) {
                    $tableData[] = [$topic];
                }

                $cli->table($tableHeaders, $tableData);
            } else {
                $cli->info("No Shopify Topic Webhook Listeners required");
            }
        }

        // Get the currently existing Webhook Subscriptions
        $currentShopifyWebhooks = shopify()->getAllWebhooks();
        if (is_object($currentShopifyWebhooks) && $currentShopifyWebhooks->errors) {
            if ($isCli) {
                $cli->line('');
                $cli->error("Processing of Webhook Management halted");
                $cli->error("Please review the error log");
                $cli->line('');
            }

            $errorObj = [
                'shopify_response' => $currentShopifyWebhooks,
                'message' => 'There was an issue while retrieving all webhooks from Shopify'
            ];
            $logger->error('Processing of Webhook Management halted', $errorObj);

            return;
        }

        if ($isCli) {
            $cli->line('');
            if (count($currentShopifyWebhooks) > 0) {
                $cli->info("Currently registered Shopify Topic Webhook Listeners:");

                $tableRows = [];
                foreach ($currentShopifyWebhooks as $shopifyWebhook) {
                    $tableRow = [
                        'id' => $shopifyWebhook['id'],
                        'topic' => $shopifyWebhook['topic'],
                        'format' => $shopifyWebhook['format'],
                        'address' => $shopifyWebhook['address'],
                    ];
                    $tableRows[] = $tableRow;
                }

                $tableHeaders = ['Shopify Webhook ID', 'Topic', 'Format', 'Address'];
                $cli->table($tableHeaders, $tableRows);
            } else {
                $cli->info("There are no Shopify Topic Webhook Listeners registered");
            }
        }

        // Check if Webhooks need to be deleted
        $deleteWebhooks = [];
        foreach ($currentShopifyWebhooks as $shopifyWebhook) {
            // Should a webhook be deleted?
            if (!in_array($shopifyWebhook['topic'], $requiredShopifyTopics)) {
                $deleteWebhooks[$shopifyWebhook['id']] = $shopifyWebhook['topic'];
            }
        }

        if ($isCli) {
            $cli->line('');
            if (count($deleteWebhooks) > 0) {
                $cli->info("The following Webhooks are redundant and are to be removed:");

                $tableRows = [];
                foreach ($deleteWebhooks as $webhookId => $webhookTopic) {
                    $tableRow = [
                        'id' => $webhookId,
                        'topic' => $webhookTopic
                    ];
                    $tableRows[] = $tableRow;
                }

                $tableHeaders = ['Shopify Webhook ID', 'Topic'];
                $cli->table($tableHeaders, $tableRows);
            } else {
                $cli->info("There are no Webhooks that require removal from Shopify");
            }
        }

        if (count($deleteWebhooks) > 0) {
            if ($isCli) {
                $cli->line('');
                $cli->info('Deleting Webhooks from Shopify');
                $bar = $cli->getOutput()->createProgressBar(count($deleteWebhooks));
                $bar->setFormat('debug');
                $bar->start();
            }

            foreach ($deleteWebhooks as $webhookId => $webhookTopic) {
                $response = shopify()->deleteWebhook($webhookId);

                if ((is_object($response) && $response->errors) || $response === false) {
                    if ($isCli) {
                        $cli->line('');
                        $cli->error("There was an error while removing the following Webhook from Shopify - {$webhookId} - {$webhookTopic}");
                        $cli->error("Please review the error log");
                        $cli->line('');
                    }

                    $errorObj = [
                        'webhook_id' => $webhookId,
                        'webhook_topic' => $webhookTopic,
                        'shopify_response' => $response
                    ];
                    $logger->error('There was an error while removing the following Webhook from Shopify', $errorObj);
                }

                if ($isCli) {
                    $bar->advance();
                }
            }

            if ($isCli) {
                $bar->finish();
                $cli->line('');
            }
        }

        // Check if Webhooks need to be created
        $createWebhooks = [];
        $cli->line('');
        $cli->info('topics:');
        if (is_array($requiredShopifyTopics)){
            foreach ($requiredShopifyTopics as $requiredTopic) {
                $isTopicRegistered = false;
                foreach ($currentShopifyWebhooks as $shopifyWebhook) {
                    if ($requiredTopic == $shopifyWebhook['topic']) {
                        $isTopicRegistered = true;
                        break;
                    }
                }

                if (!$isTopicRegistered) {
                    $createWebhooks[] = $requiredTopic;
                }
            }
        }

        if ($isCli) {
            $cli->line('');
            if (count($createWebhooks) > 0) {
                $cli->info("The following Webhooks are to be created in Shopify:");
                $tableHeaders = ['Topic'];

                $tableData = [];
                foreach ($createWebhooks as $topic) {
                    $tableData[] = [$topic];
                }

                $cli->table($tableHeaders, $tableData);
            } else {
                $cli->info("There are no Webhooks that require creation in Shopify");
            }
        }

        if (count($createWebhooks) > 0) {
            if ($isCli) {
                $cli->line('');
                $cli->info('Creating Webhooks in Shopify');
                $bar = $cli->getOutput()->createProgressBar(count($createWebhooks));
                $bar->setFormat('debug');
                $bar->start();
            }

            foreach ($createWebhooks as $webhookTopic) {
                $endpoint = route('webhook-handle', ['topic' => $webhookTopic]);

                $response = shopify()->createWebhookJson($webhookTopic, $endpoint);

                if (isset($response['errors'])) {
                    if ($isCli) {
                        $cli->line('');
                        $cli->error("There was an error while creating the following Webhook for Shopify - {$webhookTopic}");
                        $cli->error("Please review the error log");
                        $cli->line('');
                    }

                    $errorObj = [
                        'webhook_topic' => $webhookTopic,
                        'shopify_response' => $response
                    ];
                    $logger->error('There was an error while creating the following Webhook for Shopify', $errorObj);
                }

                if ($isCli) {
                    $bar->advance();
                }
            }

            if ($isCli) {
                $bar->finish();
                $cli->line('');
            }
        }

        if ($isCli) {
            $cli->line('');
            $cli->info("Shopify Webhook Management Process for Store ID {$storeId} Complete");
            $cli->line('');
        }
    }

    static private function getLogger()
    {
        $logger = new Logger('App_Services_WebhookInit');
        $loggerFilename = storage_path(
            'logs/App_Services_WebhookInit_manage.log'
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        return $logger;
    }
}
