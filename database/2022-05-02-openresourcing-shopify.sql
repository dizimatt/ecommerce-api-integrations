-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: or-mysql
-- Generation Time: May 02, 2022 at 07:19 AM
-- Server version: 10.7.3-MariaDB-1:10.7.3+maria~focal
-- PHP Version: 8.0.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `openresourcing-shopify`
--

-- --------------------------------------------------------

--
-- Table structure for table `bigcommerce_stores`
--

CREATE TABLE `bigcommerce_stores` (
  `id` int(10) UNSIGNED NOT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `graphql_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_emails` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `graphql_token` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `webhook_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bigcommerce_stores`
--

INSERT INTO `bigcommerce_stores` (`id`, `domain`, `api_url`, `graphql_url`, `name`, `timezone`, `currency`, `contact_emails`, `api_token`, `graphql_token`, `scope`, `created_at`, `updated_at`, `webhook_signature`) VALUES
(1, 'https://open-resourcing.mybigcommerce.com', 'https://api.bigcommerce.com/stores/rpx9efkcf8/v3', 'https://open-resourcing.mybigcommerce.com/graphql', 'Open Resourcing', 'UTC', NULL, NULL, 't1s7g9k5rd3c16x3k65yio9h56jzpc0', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.eyJjaWQiOjEsImNvcnMiOlsiaHR0cHM6Ly9kZXZlbG9wZXIuYmlnY29tbWVyY2UuY29tIl0sImVhdCI6MTY1MTU0NjAxMywiaWF0IjoxNjUxMzczMjEzLCJpc3MiOiJCQyIsInNpZCI6MTAwMjQxMDkxNiwic3ViIjoiYmNhcHAubGlua2VyZCIsInN1Yl90eXBlIjowLCJ0b2tlbl90eXBlIjoxfQ.OlTy03lcJNhPoRYOr1ol17t6WNgm1B7zfyJcnzM2FF7Xlj9tLK0QDMOCZCctIev4WvT0zIR8JFq7OVtRW8l4Ug', NULL, '2022-05-02 01:52:16', '2022-05-02 01:52:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dolibarr_accounts`
--

CREATE TABLE `dolibarr_accounts` (
  `id` int(11) NOT NULL,
  `sandbox_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sandbox_login` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sandbox_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sandbox_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `dolibarr_accounts`
--

INSERT INTO `dolibarr_accounts` (`id`, `sandbox_url`, `sandbox_login`, `sandbox_password`, `sandbox_token`) VALUES
(1, 'https://dolibarr-dev.openresourcing.com.au/api/index.php', '', '', '10c319dce35ff924184409e434702781ae687cd4'),
(2, 'https://dolibarr-dev.openresourcing.com.au/api/index.php', '', '', '10c319dce35ff924184409e434702781ae687cd4');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` int(10) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `konakart_accounts`
--

CREATE TABLE `konakart_accounts` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `live_username` varchar(255) DEFAULT NULL,
  `live_password` varchar(255) DEFAULT NULL,
  `sandbox_username` varchar(255) DEFAULT NULL,
  `sandbox_password` varchar(255) DEFAULT NULL,
  `using_sandbox` tinyint(1) DEFAULT 1,
  `sandbox_url` varchar(255) DEFAULT NULL,
  `live_url` varchar(255) DEFAULT NULL,
  `sandbox_token` varchar(255) DEFAULT NULL,
  `live_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `konakart_accounts`
--

INSERT INTO `konakart_accounts` (`id`, `store_id`, `live_username`, `live_password`, `sandbox_username`, `sandbox_password`, `using_sandbox`, `sandbox_url`, `live_url`, `sandbox_token`, `live_token`) VALUES
(1, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shopify_app_keys`
--

CREATE TABLE `shopify_app_keys` (
  `id` int(10) UNSIGNED NOT NULL,
  `store_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `store_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_api_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shopify_app_keys`
--

INSERT INTO `shopify_app_keys` (`id`, `store_name`, `store_api_key`, `store_api_secret`) VALUES
(1, 'openresourcing.myshopify.com', '22d155352683f672fae593e2b05f3437', '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');

-- --------------------------------------------------------

--
-- Table structure for table `shopify_stores`
--

CREATE TABLE `shopify_stores` (
  `id` int(10) UNSIGNED NOT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hostname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_emails` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `orders_since_id` bigint(20) DEFAULT NULL,
  `webhook_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nonce_created_at` timestamp NULL DEFAULT NULL,
  `nonce` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shopify_stores`
--

INSERT INTO `shopify_stores` (`id`, `domain`, `hostname`, `name`, `timezone`, `currency`, `contact_emails`, `access_token`, `scope`, `created_at`, `updated_at`, `orders_since_id`, `webhook_signature`, `nonce_created_at`, `nonce`) VALUES
(1, 'openresourcing.myshopify.com', 'openresourcing.myshopify.com', 'openresourcing', 'Australia/Sydney', 'AUD', NULL, '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', 'write_products,write_customers,write_orders,write_inventory,read_locations,write_fulfillments,write_shipping,write_checkouts,write_price_rules,write_gift_cards,write_order_edits', '2022-04-17 02:58:33', '2022-04-17 02:58:39', NULL, NULL, NULL, NULL),
(2, 'vectorpunk-store.myshopify.com', 'vectorpunk-store.myshopify.com', 'vectorpunk-store', 'Australia/Sydney', 'AUD', NULL, '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', 'write_products,write_customers,write_orders,write_inventory,read_locations,write_fulfillments,write_shipping,write_checkouts,write_price_rules,write_gift_cards,write_order_edits', '2022-03-13 04:49:34', '2022-03-13 05:14:28', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wordpress_accounts`
--

CREATE TABLE `wordpress_accounts` (
  `id` int(11) NOT NULL,
  `store_id` int(11) DEFAULT NULL,
  `sandbox_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sandbox_login` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sandbox_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sandbox_key` varchar(255) DEFAULT NULL,
  `sandbox_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `wordpress_accounts`
--

INSERT INTO `wordpress_accounts` (`id`, `store_id`, `sandbox_url`, `sandbox_login`, `sandbox_password`, `sandbox_key`, `sandbox_secret`) VALUES
(1, 2, 'http://172.20.0.2/wp-json/wc/v3', 'mattd', 'D0z1n2ss', 'ck_0945b2430b57c802aea2f63c708b326670e53808', 'cs_71bec485139632125c2d425445a6d314b2d75dad');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bigcommerce_stores`
--
ALTER TABLE `bigcommerce_stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stores_id_uindex` (`id`);

--
-- Indexes for table `dolibarr_accounts`
--
ALTER TABLE `dolibarr_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_reserved_at_index` (`queue`,`reserved_at`);

--
-- Indexes for table `konakart_accounts`
--
ALTER TABLE `konakart_accounts`
  ADD UNIQUE KEY `konakart_accounts_id_uindex` (`id`);

--
-- Indexes for table `shopify_app_keys`
--
ALTER TABLE `shopify_app_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shopify_app_keys_id_uindex` (`id`);

--
-- Indexes for table `shopify_stores`
--
ALTER TABLE `shopify_stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stores_id_uindex` (`id`);

--
-- Indexes for table `wordpress_accounts`
--
ALTER TABLE `wordpress_accounts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bigcommerce_stores`
--
ALTER TABLE `bigcommerce_stores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dolibarr_accounts`
--
ALTER TABLE `dolibarr_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `konakart_accounts`
--
ALTER TABLE `konakart_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shopify_app_keys`
--
ALTER TABLE `shopify_app_keys`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shopify_stores`
--
ALTER TABLE `shopify_stores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `wordpress_accounts`
--
ALTER TABLE `wordpress_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
