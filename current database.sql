-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 21, 2025 at 05:56 PM
-- Server version: 10.11.15-MariaDB
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cornerst_vpn`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `name`, `price`, `created_at`) VALUES
(1, 'CORNERSTONE-ITS PREMIUM MOBILE DATA', 50.00, '2025-12-21 17:11:44');

-- --------------------------------------------------------

--
-- Table structure for table `admob_ads`
--

CREATE TABLE `admob_ads` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ad_unit_id` varchar(255) NOT NULL,
  `ad_type` varchar(255) NOT NULL DEFAULT 'banner',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_updates`
--

CREATE TABLE `app_updates` (
  `id` int(11) NOT NULL,
  `version_code` varchar(50) NOT NULL,
  `version_name` varchar(50) NOT NULL,
  `apk_path` varchar(255) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `id` int(11) NOT NULL,
  `reseller_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `commission_earned` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `created_at`) VALUES
(1, '001_create_app_updates_table.php', '2025-12-21 07:23:05'),
(2, '20240726_add_filesize_to_app_updates.php', '2025-12-21 07:23:05'),
(3, '20240727_add_uploaddate_to_app_updates.php', '2025-12-21 07:23:05'),
(4, '20240728_add_promo_id_to_configurations.php', '2025-12-21 07:23:05'),
(5, '20240802_add_is_reseller_to_users_table.php', '2025-12-21 07:23:05'),
(6, '20240802_create_reseller_tables.php', '2025-12-21 07:23:05'),
(7, '20240728_migrate_configurations_to_promos.php', '2025-12-21 07:23:42'),
(8, '20240801_create_troubleshooting_guides_table.php', '2025-12-21 07:23:42'),
(9, '20240803_add_commission_rate_to_resellers.php', '2025-12-21 07:37:49'),
(10, '001_create_migrations_table.php', '2025-12-21 17:10:26'),
(11, '002_create_accounts_table.php', '2025-12-21 17:10:26'),
(12, '003_add_account_id_and_client_limit_to_users_table.php', '2025-12-21 17:10:26'),
(13, '20240802_create_admob_ads_table.php', '2025-12-21 17:10:26');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_time` time NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promos`
--

CREATE TABLE `promos` (
  `id` int(11) NOT NULL,
  `promo_name` varchar(255) NOT NULL,
  `icon_promo_path` varchar(255) NOT NULL,
  `carrier` varchar(255) DEFAULT NULL,
  `config_text` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `promos`
--

INSERT INTO `promos` (`id`, `promo_name`, `icon_promo_path`, `carrier`, `config_text`, `is_active`) VALUES
(1, 'GoSURF', 'assets/promo/default.png', 'Globe Telecom', '# Globe GoSURF Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 13)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Choose your proxy server\nhttp-proxy 110.78.141.147 8080\n# Alternative: http-proxy 203.177.135.129 80', 1),
(2, 'Smart Basic', 'assets/promo/default.png', 'Smart Communications', '# Smart Basic Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 14)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: internet.smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Smart Proxy\nhttp-proxy 10.102.61.1 8080\nhttp-proxy-timeout 30', 1),
(3, 'GoSURF', 'assets/promo/default.png', 'Globe Telecom', '# Globe GoSURF Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 13)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Choose your proxy server\nhttp-proxy 110.78.141.147 8080\n# Alternative: http-proxy 203.177.135.129 80', 1),
(4, 'Smart Basic', 'assets/promo/default.png', 'Smart Communications', '# Smart Basic Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 14)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: internet.smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Smart Proxy\nhttp-proxy 10.102.61.1 8080\nhttp-proxy-timeout 30', 1),
(5, 'GoSURF', 'assets/promo/default.png', 'Globe Telecom', '# Globe GoSURF Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 13)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Choose your proxy server\nhttp-proxy 110.78.141.147 8080\n# Alternative: http-proxy 203.177.135.129 80', 1),
(6, 'Smart Basic', 'assets/promo/default.png', 'Smart Communications', '# Smart Basic Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 14)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: internet.smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Smart Proxy\nhttp-proxy 10.102.61.1 8080\nhttp-proxy-timeout 30', 1),
(7, 'GoSURF', 'assets/promo/default.png', 'Globe Telecom', '# Globe GoSURF Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 13)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Choose your proxy server\nhttp-proxy 110.78.141.147 8080\n# Alternative: http-proxy 203.177.135.129 80', 1),
(8, 'Smart Basic', 'assets/promo/default.png', 'Smart Communications', '# Smart Basic Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 14)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: internet.smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Smart Proxy\nhttp-proxy 10.102.61.1 8080\nhttp-proxy-timeout 30', 1),
(9, 'GoSURF', 'assets/promo/default.png', 'Globe Telecom', '# Globe GoSURF Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 13)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: gosurf.globe.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Choose your proxy server\nhttp-proxy 110.78.141.147 8080\n# Alternative: http-proxy 203.177.135.129 80', 1),
(10, 'Smart Basic', 'assets/promo/default.png', 'Smart Communications', '# Smart Basic Configuration\nhttp-proxy-option AGENT \"Mozilla/5.0 (Linux; Android 14)\"\nhttp-proxy-option VERSION 1.1\nhttp-proxy-option CUSTOM-HEADER \"Host: internet.smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Online-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"X-Forward-Host: smart.com.ph\"\nhttp-proxy-option CUSTOM-HEADER \"Connection: Keep-Alive\"\n\n# Smart Proxy\nhttp-proxy 10.102.61.1 8080\nhttp-proxy-timeout 30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `resellers`
--

CREATE TABLE `resellers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `primary_color` varchar(7) DEFAULT NULL,
  `secondary_color` varchar(7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 0.10
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `resellers`
--

INSERT INTO `resellers` (`id`, `user_id`, `company_name`, `logo_path`, `primary_color`, `secondary_color`, `created_at`, `commission_rate`) VALUES
(15, 16, NULL, NULL, NULL, NULL, '2025-12-21 22:54:14', 0.10);

-- --------------------------------------------------------

--
-- Table structure for table `reseller_clients`
--

CREATE TABLE `reseller_clients` (
  `id` int(11) NOT NULL,
  `reseller_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `reseller_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `sale_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `reseller_id`, `client_id`, `amount`, `sale_date`) VALUES
(1, 10, 11, 50.00, '2025-12-21 17:17:56'),
(2, 10, 12, 50.00, '2025-12-21 17:18:24');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1, 'site_name', 'CS-MOBILE DATA'),
(2, 'site_icon', 'assets/icon_6947cbb4dc958.png'),
(3, 'language', 'en');

-- --------------------------------------------------------

--
-- Table structure for table `troubleshooting_guides`
--

CREATE TABLE `troubleshooting_guides` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `login_code` varchar(255) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `banned` tinyint(1) NOT NULL DEFAULT 0,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `daily_limit` bigint(20) UNSIGNED DEFAULT 0,
  `data_usage` bigint(20) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `promo_id` int(11) DEFAULT NULL,
  `reseller_id` int(11) DEFAULT NULL,
  `credits` decimal(10,2) DEFAULT 0.00,
  `expiration_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Unpaid',
  `payment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `billing_month` date DEFAULT NULL,
  `is_reseller` tinyint(1) NOT NULL DEFAULT 0,
  `account_id` int(11) DEFAULT NULL,
  `client_limit` int(11) NOT NULL DEFAULT 0,
  `address` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `login_code`, `device_id`, `banned`, `role`, `daily_limit`, `data_usage`, `created_at`, `promo_id`, `reseller_id`, `credits`, `expiration_date`, `status`, `payment`, `first_name`, `last_name`, `contact_number`, `billing_month`, `is_reseller`, `account_id`, `client_limit`, `address`) VALUES
(8, 'admin', '$2y$10$lj1VP1rI0oFxuqypd8T3WOFZQinl6VHyf/gduTrMm0lC6Ttme/zsW', NULL, NULL, 0, 'admin', 0, 0, '2025-12-21 17:16:03', NULL, NULL, 0.00, NULL, 'Unpaid', 0.00, 'Admin', 'User', '0', NULL, 0, NULL, 0, ''),
(9, 'admin1', '$2y$10$F4ovFtD881a6QtXL1dm4TeKscusntd4SUzgvNmUls6tP85Zbl6myK', '27683884', NULL, 0, 'user', 1073741824, 0, '2025-12-21 17:16:45', NULL, NULL, 0.00, NULL, 'Unpaid', 0.00, 'Ronald', 'Torrejos', '09663016917', '2025-12-31', 0, NULL, 0, ''),
(5, 'cornerstone', '$2y$10$anwZ8COroUfjEvhJEjfA4eeODBNbbxcrQzZCNBZYPnZp6EF5Netc6', NULL, NULL, 0, 'admin', 0, 0, '2025-12-21 08:39:55', NULL, NULL, 0.00, NULL, 'Unpaid', 0.00, 'Admin', 'User', '0', NULL, 1, NULL, 0, ''),
(11, 'Client 1', '$2y$10$Rk3r7Hf1k61CXvB7Z2SQXuJUdcznICICTbJIMe/wRPCLciNtGYOaq', NULL, NULL, 0, 'user', 0, 0, '2025-12-21 17:17:56', NULL, 10, 0.00, '2025-12-22', 'Unpaid', 0.00, 'Client', 'User', 'N/A', NULL, 0, NULL, 0, 'N/A'),
(12, 'Client 2', '$2y$10$Oot1RvAY0R9CAWDgDoMWwuwVKIhZAgpwgOHaVxuYqDjNO1h8jS.ia', NULL, NULL, 0, 'user', 0, 0, '2025-12-21 17:18:24', NULL, 10, 0.00, '2025-12-22', 'Unpaid', 0.00, 'Client', 'User', 'N/A', NULL, 0, NULL, 0, 'N/A'),
(16, 'Reseller1', '$2y$10$b.KfDhaWfZSW99ip32S3v.XfOFcD8Q7dyic7JesWMqHjEfDzt41pK', '56946578', NULL, 0, '', 0, 0, '2025-12-21 22:54:14', NULL, NULL, 50.00, NULL, 'Unpaid', 0.00, 'Fagmmmu', '', '09663016917', NULL, 1, 1, 1, 'Zone 9, Bonbon'),
(13, 'Ronald', '$2y$10$PHsQYtKrBH8B8srX8/vFROKf1K5Jz2bvGmf30CucyRhzrDrGEz.p.', NULL, NULL, 0, 'admin', 0, 0, '2025-12-21 19:11:07', NULL, NULL, 0.00, NULL, 'Unpaid', 0.00, 'Admin', 'User', '0', NULL, 0, NULL, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `vpn_profiles`
--

CREATE TABLE `vpn_profiles` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ovpn_config` text NOT NULL,
  `type` enum('Premium','Freemium') NOT NULL DEFAULT 'Premium',
  `promo_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `icon_path` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `vpn_profiles`
--

INSERT INTO `vpn_profiles` (`id`, `name`, `ovpn_config`, `type`, `promo_id`, `created_at`, `icon_path`) VALUES
(1, 'CORNERSTONE-ITS', '###############################################################################\r\n# OpenVPN 2.0 Sample Configuration File\r\n# for PacketiX VPN / SoftEther VPN Server\r\n# \r\n# !!! AUTO-GENERATED BY SOFTETHER VPN SERVER MANAGEMENT TOOL !!!\r\n# \r\n# !!! YOU HAVE TO REVIEW IT BEFORE USE AND MODIFY IT AS NECESSARY !!!\r\n# \r\n# This configuration file is auto-generated. You might use this config file\r\n# in order to connect to the PacketiX VPN / SoftEther VPN Server.\r\n# However, before you try it, you should review the descriptions of the file\r\n# to determine the necessity to modify to suitable for your real environment.\r\n# If necessary, you have to modify a little adequately on the file.\r\n# For example, the IP address or the hostname as a destination VPN Server\r\n# should be confirmed.\r\n# \r\n# Note that to use OpenVPN 2.0, you have to put the certification file of\r\n# the destination VPN Server on the OpenVPN Client computer when you use this\r\n# config file. Please refer the below descriptions carefully.\r\n\r\n\r\n###############################################################################\r\n# Specify the type of the layer of the VPN connection.\r\n# \r\n# To connect to the VPN Server as a \"Remote-Access VPN Client PC\",\r\n#  specify \'dev tun\'. (Layer-3 IP Routing Mode)\r\n#\r\n# To connect to the VPN Server as a bridging equipment of \"Site-to-Site VPN\",\r\n#  specify \'dev tap\'. (Layer-2 Ethernet Bridgine Mode)\r\n\r\ndev tun\r\n\r\n\r\n###############################################################################\r\n# Specify the underlying protocol beyond the Internet.\r\n# Note that this setting must be correspond with the listening setting on\r\n# the VPN Server.\r\n# \r\n# Specify either \'proto tcp\' or \'proto udp\'.\r\n\r\nproto tcp\r\n\r\n\r\n###############################################################################\r\n# The destination hostname / IP address, and port number of\r\n# the target VPN Server.\r\n# \r\n# You have to specify as \'remote <HOSTNAME> <PORT>\'. You can also\r\n# specify the IP address instead of the hostname.\r\n# \r\n# Note that the auto-generated below hostname are a \"auto-detected\r\n# IP address\" of the VPN Server. You have to confirm the correctness\r\n# beforehand.\r\n# \r\n# When you want to connect to the VPN Server by using TCP protocol,\r\n# the port number of the destination TCP port should be same as one of\r\n# the available TCP listeners on the VPN Server.\r\n# \r\n# When you use UDP protocol, the port number must same as the configuration\r\n# setting of \"OpenVPN Server Compatible Function\" on the VPN Server.\r\n\r\nremote 178.45.104.108 1822\r\n\r\n\r\n###############################################################################\r\n# The HTTP/HTTPS proxy setting.\r\n# \r\n# Only if you have to use the Internet via a proxy, uncomment the below\r\n# two lines and specify the proxy address and the port number.\r\n# In the case of using proxy-authentication, refer the OpenVPN manual.\r\n\r\n;http-proxy-retry\r\n;http-proxy [proxy server] [proxy port]\r\n\r\n\r\n###############################################################################\r\n# The encryption and authentication algorithm.\r\n# \r\n# Default setting is good. Modify it as you prefer.\r\n# When you specify an unsupported algorithm, the error will occur.\r\n# \r\n# The supported algorithms are as follows:\r\n#  cipher: [NULL-CIPHER] NULL AES-128-CBC AES-192-CBC AES-256-CBC BF-CBC\r\n#          CAST-CBC CAST5-CBC DES-CBC DES-EDE-CBC DES-EDE3-CBC DESX-CBC\r\n#          RC2-40-CBC RC2-64-CBC RC2-CBC\r\n#  auth:   SHA SHA1 MD5 MD4 RMD160\r\n\r\ncipher AES-128-CBC\r\nauth SHA1\r\n\r\n\r\n###############################################################################\r\n# Other parameters necessary to connect to the VPN Server.\r\n# \r\n# It is not recommended to modify it unless you have a particular need.\r\n\r\nresolv-retry infinite\r\nnobind\r\npersist-key\r\npersist-tun\r\nclient\r\nverb 3\r\n#auth-user-pass\r\n\r\n\r\n###############################################################################\r\n# The certificate file of the destination VPN Server.\r\n# \r\n# The CA certificate file is embedded in the inline format.\r\n# You can replace this CA contents if necessary.\r\n# Please note that if the server certificate is not a self-signed, you have to\r\n# specify the signer\'s root certificate (CA) here.\r\n\r\n<ca>\r\n-----BEGIN CERTIFICATE-----\r\nMIIFazCCA1OgAwIBAgIRAIIQz7DSQONZRGPgu2OCiwAwDQYJKoZIhvcNAQELBQAw\r\nTzELMAkGA1UEBhMCVVMxKTAnBgNVBAoTIEludGVybmV0IFNlY3VyaXR5IFJlc2Vh\r\ncmNoIEdyb3VwMRUwEwYDVQQDEwxJU1JHIFJvb3QgWDEwHhcNMTUwNjA0MTEwNDM4\r\nWhcNMzUwNjA0MTEwNDM4WjBPMQswCQYDVQQGEwJVUzEpMCcGA1UEChMgSW50ZXJu\r\nZXQgU2VjdXJpdHkgUmVzZWFyY2ggR3JvdXAxFTATBgNVBAMTDElTUkcgUm9vdCBY\r\nMTCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBAK3oJHP0FDfzm54rVygc\r\nh77ct984kIxuPOZXoHj3dcKi/vVqbvYATyjb3miGbESTtrFj/RQSa78f0uoxmyF+\r\n0TM8ukj13Xnfs7j/EvEhmkvBioZxaUpmZmyPfjxwv60pIgbz5MDmgK7iS4+3mX6U\r\nA5/TR5d8mUgjU+g4rk8Kb4Mu0UlXjIB0ttov0DiNewNwIRt18jA8+o+u3dpjq+sW\r\nT8KOEUt+zwvo/7V3LvSye0rgTBIlDHCNAymg4VMk7BPZ7hm/ELNKjD+Jo2FR3qyH\r\nB5T0Y3HsLuJvW5iB4YlcNHlsdu87kGJ55tukmi8mxdAQ4Q7e2RCOFvu396j3x+UC\r\nB5iPNgiV5+I3lg02dZ77DnKxHZu8A/lJBdiB3QW0KtZB6awBdpUKD9jf1b0SHzUv\r\nKBds0pjBqAlkd25HN7rOrFleaJ1/ctaJxQZBKT5ZPt0m9STJEadao0xAH0ahmbWn\r\nOlFuhjuefXKnEgV4We0+UXgVCwOPjdAvBbI+e0ocS3MFEvzG6uBQE3xDk3SzynTn\r\njh8BCNAw1FtxNrQHusEwMFxIt4I7mKZ9YIqioymCzLq9gwQbooMDQaHWBfEbwrbw\r\nqHyGO0aoSCqI3Haadr8faqU9GY/rOPNk3sgrDQoo//fb4hVC1CLQJ13hef4Y53CI\r\nrU7m2Ys6xt0nUW7/vGT1M0NPAgMBAAGjQjBAMA4GA1UdDwEB/wQEAwIBBjAPBgNV\r\nHRMBAf8EBTADAQH/MB0GA1UdDgQWBBR5tFnme7bl5AFzgAiIyBpY9umbbjANBgkq\r\nhkiG9w0BAQsFAAOCAgEAVR9YqbyyqFDQDLHYGmkgJykIrGF1XIpu+ILlaS/V9lZL\r\nubhzEFnTIZd+50xx+7LSYK05qAvqFyFWhfFQDlnrzuBZ6brJFe+GnY+EgPbk6ZGQ\r\n3BebYhtF8GaV0nxvwuo77x/Py9auJ/GpsMiu/X1+mvoiBOv/2X/qkSsisRcOj/KK\r\nNFtY2PwByVS5uCbMiogziUwthDyC3+6WVwW6LLv3xLfHTjuCvjHIInNzktHCgKQ5\r\nORAzI4JMPJ+GslWYHb4phowim57iaztXOoJwTdwJx4nLCgdNbOhdjsnvzqvHu7Ur\r\nTkXWStAmzOVyyghqpZXjFaH3pO3JLF+l+/+sKAIuvtd7u+Nxe5AW0wdeRlN8NwdC\r\njNPElpzVmbUq4JUagEiuTDkHzsxHpFKVK7q4+63SM1N95R1NbdWhscdCb+ZAJzVc\r\noyi3B43njTOQ5yOf+1CceWxG1bQVs5ZufpsMljq4Ui0/1lvh+wjChP4kqKOJ2qxq\r\n4RgqsahDYVvTH9w7jXbyLeiNdd8XM2w9U/t7y0Ff/9yi0GE44Za4rF2LN9d11TPA\r\nmRGunUHBcnWEvgJBQl9nJEiU0Zsnvgc/ubhPgXRR4Xq37Z0j4r7g1SgEEzwxA57d\r\nemyPxgcYxn/eR44/KJ4EBs+lVDR3veyJm+kXQ99b21/+jh5Xos1AnX5iItreGCc=\r\n-----END CERTIFICATE-----\r\n\r\n</ca>\r\n\r\n\r\n###############################################################################\r\n# The client certificate file (dummy).\r\n# \r\n# In some implementations of OpenVPN Client software\r\n# (for example: OpenVPN Client for iOS),\r\n# a pair of client certificate and private key must be included on the\r\n# configuration file due to the limitation of the client.\r\n# So this sample configuration file has a dummy pair of client certificate\r\n# and private key as follows.\r\n\r\n<cert>\r\n-----BEGIN CERTIFICATE-----\r\nMIICxjCCAa4CAQAwDQYJKoZIhvcNAQEFBQAwKTEaMBgGA1UEAxMRVlBOR2F0ZUNs\r\naWVudENlcnQxCzAJBgNVBAYTAkpQMB4XDTEzMDIxMTAzNDk0OVoXDTM3MDExOTAz\r\nMTQwN1owKTEaMBgGA1UEAxMRVlBOR2F0ZUNsaWVudENlcnQxCzAJBgNVBAYTAkpQ\r\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5h2lgQQYUjwoKYJbzVZA\r\n5VcIGd5otPc/qZRMt0KItCFA0s9RwReNVa9fDRFLRBhcITOlv3FBcW3E8h1Us7RD\r\n4W8GmJe8zapJnLsD39OSMRCzZJnczW4OCH1PZRZWKqDtjlNca9AF8a65jTmlDxCQ\r\nCjntLIWk5OLLVkFt9/tScc1GDtci55ofhaNAYMPiH7V8+1g66pGHXAoWK6AQVH67\r\nXCKJnGB5nlQ+HsMYPV/O49Ld91ZN/2tHkcaLLyNtywxVPRSsRh480jju0fcCsv6h\r\np/0yXnTB//mWutBGpdUlIbwiITbAmrsbYnjigRvnPqX1RNJUbi9Fp6C2c/HIFJGD\r\nywIDAQABMA0GCSqGSIb3DQEBBQUAA4IBAQChO5hgcw/4oWfoEFLu9kBa1B//kxH8\r\nhQkChVNn8BRC7Y0URQitPl3DKEed9URBDdg2KOAz77bb6ENPiliD+a38UJHIRMqe\r\nUBHhllOHIzvDhHFbaovALBQceeBzdkQxsKQESKmQmR832950UCovoyRB61UyAV7h\r\n+mZhYPGRKXKSJI6s0Egg/Cri+Cwk4bjJfrb5hVse11yh4D9MHhwSfCOH+0z4hPUT\r\nFku7dGavURO5SVxMn/sL6En5D+oSeXkadHpDs+Airym2YHh15h0+jPSOoR6yiVp/\r\n6zZeZkrN43kuS73KpKDFjfFPh8t4r1gOIjttkNcQqBccusnplQ7HJpsk\r\n-----END CERTIFICATE-----\r\n\r\n</cert>\r\n\r\n<key>\r\n-----BEGIN RSA PRIVATE KEY-----\r\nMIIEpAIBAAKCAQEA5h2lgQQYUjwoKYJbzVZA5VcIGd5otPc/qZRMt0KItCFA0s9R\r\nwReNVa9fDRFLRBhcITOlv3FBcW3E8h1Us7RD4W8GmJe8zapJnLsD39OSMRCzZJnc\r\nzW4OCH1PZRZWKqDtjlNca9AF8a65jTmlDxCQCjntLIWk5OLLVkFt9/tScc1GDtci\r\n55ofhaNAYMPiH7V8+1g66pGHXAoWK6AQVH67XCKJnGB5nlQ+HsMYPV/O49Ld91ZN\r\n/2tHkcaLLyNtywxVPRSsRh480jju0fcCsv6hp/0yXnTB//mWutBGpdUlIbwiITbA\r\nmrsbYnjigRvnPqX1RNJUbi9Fp6C2c/HIFJGDywIDAQABAoIBAERV7X5AvxA8uRiK\r\nk8SIpsD0dX1pJOMIwakUVyvc4EfN0DhKRNb4rYoSiEGTLyzLpyBc/A28Dlkm5eOY\r\nfjzXfYkGtYi/Ftxkg3O9vcrMQ4+6i+uGHaIL2rL+s4MrfO8v1xv6+Wky33EEGCou\r\nQiwVGRFQXnRoQ62NBCFbUNLhmXwdj1akZzLU4p5R4zA3QhdxwEIatVLt0+7owLQ3\r\nlP8sfXhppPOXjTqMD4QkYwzPAa8/zF7acn4kryrUP7Q6PAfd0zEVqNy9ZCZ9ffho\r\nzXedFj486IFoc5gnTp2N6jsnVj4LCGIhlVHlYGozKKFqJcQVGsHCqq1oz2zjW6LS\r\noRYIHgECgYEA8zZrkCwNYSXJuODJ3m/hOLVxcxgJuwXoiErWd0E42vPanjjVMhnt\r\nKY5l8qGMJ6FhK9LYx2qCrf/E0XtUAZ2wVq3ORTyGnsMWre9tLYs55X+ZN10Tc75z\r\n4hacbU0hqKN1HiDmsMRY3/2NaZHoy7MKnwJJBaG48l9CCTlVwMHocIECgYEA8jby\r\ndGjxTH+6XHWNizb5SRbZxAnyEeJeRwTMh0gGzwGPpH/sZYGzyu0SySXWCnZh3Rgq\r\n5uLlNxtrXrljZlyi2nQdQgsq2YrWUs0+zgU+22uQsZpSAftmhVrtvet6MjVjbByY\r\nDADciEVUdJYIXk+qnFUJyeroLIkTj7WYKZ6RjksCgYBoCFIwRDeg42oK89RFmnOr\r\nLymNAq4+2oMhsWlVb4ejWIWeAk9nc+GXUfrXszRhS01mUnU5r5ygUvRcarV/T3U7\r\nTnMZ+I7Y4DgWRIDd51znhxIBtYV5j/C/t85HjqOkH+8b6RTkbchaX3mau7fpUfds\r\nFq0nhIq42fhEO8srfYYwgQKBgQCyhi1N/8taRwpk+3/IDEzQwjbfdzUkWWSDk9Xs\r\nH/pkuRHWfTMP3flWqEYgW/LW40peW2HDq5imdV8+AgZxe/XMbaji9Lgwf1RY005n\r\nKxaZQz7yqHupWlLGF68DPHxkZVVSagDnV/sztWX6SFsCqFVnxIXifXGC4cW5Nm9g\r\nva8q4QKBgQCEhLVeUfdwKvkZ94g/GFz731Z2hrdVhgMZaU/u6t0V95+YezPNCQZB\r\nwmE9Mmlbq1emDeROivjCfoGhR3kZXW1pTKlLh6ZMUQUOpptdXva8XxfoqQwa3enA\r\nM7muBbF0XN7VO80iJPv+PmIZdEIAkpwKfi201YB+BafCIuGxIF50Vg==\r\n-----END RSA PRIVATE KEY-----\r\n\r\n</key>\r\n\r\n', 'Freemium', 1, '2025-12-21 07:58:28', 'assets/au.png'),
(2, 'CORNERSTONE-ITS', '###############################################################################\r\n# OpenVPN 2.0 Sample Configuration File\r\n# for PacketiX VPN / SoftEther VPN Server\r\n# \r\n# !!! AUTO-GENERATED BY SOFTETHER VPN SERVER MANAGEMENT TOOL !!!\r\n# \r\n# !!! YOU HAVE TO REVIEW IT BEFORE USE AND MODIFY IT AS NECESSARY !!!\r\n# \r\n# This configuration file is auto-generated. You might use this config file\r\n# in order to connect to the PacketiX VPN / SoftEther VPN Server.\r\n# However, before you try it, you should review the descriptions of the file\r\n# to determine the necessity to modify to suitable for your real environment.\r\n# If necessary, you have to modify a little adequately on the file.\r\n# For example, the IP address or the hostname as a destination VPN Server\r\n# should be confirmed.\r\n# \r\n# Note that to use OpenVPN 2.0, you have to put the certification file of\r\n# the destination VPN Server on the OpenVPN Client computer when you use this\r\n# config file. Please refer the below descriptions carefully.\r\n\r\n\r\n###############################################################################\r\n# Specify the type of the layer of the VPN connection.\r\n# \r\n# To connect to the VPN Server as a \"Remote-Access VPN Client PC\",\r\n#  specify \'dev tun\'. (Layer-3 IP Routing Mode)\r\n#\r\n# To connect to the VPN Server as a bridging equipment of \"Site-to-Site VPN\",\r\n#  specify \'dev tap\'. (Layer-2 Ethernet Bridgine Mode)\r\n\r\ndev tun\r\n\r\n\r\n###############################################################################\r\n# Specify the underlying protocol beyond the Internet.\r\n# Note that this setting must be correspond with the listening setting on\r\n# the VPN Server.\r\n# \r\n# Specify either \'proto tcp\' or \'proto udp\'.\r\n\r\nproto tcp\r\n\r\n\r\n###############################################################################\r\n# The destination hostname / IP address, and port number of\r\n# the target VPN Server.\r\n# \r\n# You have to specify as \'remote <HOSTNAME> <PORT>\'. You can also\r\n# specify the IP address instead of the hostname.\r\n# \r\n# Note that the auto-generated below hostname are a \"auto-detected\r\n# IP address\" of the VPN Server. You have to confirm the correctness\r\n# beforehand.\r\n# \r\n# When you want to connect to the VPN Server by using TCP protocol,\r\n# the port number of the destination TCP port should be same as one of\r\n# the available TCP listeners on the VPN Server.\r\n# \r\n# When you use UDP protocol, the port number must same as the configuration\r\n# setting of \"OpenVPN Server Compatible Function\" on the VPN Server.\r\n\r\nremote 178.45.104.108 1822\r\n\r\n\r\n###############################################################################\r\n# The HTTP/HTTPS proxy setting.\r\n# \r\n# Only if you have to use the Internet via a proxy, uncomment the below\r\n# two lines and specify the proxy address and the port number.\r\n# In the case of using proxy-authentication, refer the OpenVPN manual.\r\n\r\n;http-proxy-retry\r\n;http-proxy [proxy server] [proxy port]\r\n\r\n\r\n###############################################################################\r\n# The encryption and authentication algorithm.\r\n# \r\n# Default setting is good. Modify it as you prefer.\r\n# When you specify an unsupported algorithm, the error will occur.\r\n# \r\n# The supported algorithms are as follows:\r\n#  cipher: [NULL-CIPHER] NULL AES-128-CBC AES-192-CBC AES-256-CBC BF-CBC\r\n#          CAST-CBC CAST5-CBC DES-CBC DES-EDE-CBC DES-EDE3-CBC DESX-CBC\r\n#          RC2-40-CBC RC2-64-CBC RC2-CBC\r\n#  auth:   SHA SHA1 MD5 MD4 RMD160\r\n\r\ncipher AES-128-CBC\r\nauth SHA1\r\n\r\n\r\n###############################################################################\r\n# Other parameters necessary to connect to the VPN Server.\r\n# \r\n# It is not recommended to modify it unless you have a particular need.\r\n\r\nresolv-retry infinite\r\nnobind\r\npersist-key\r\npersist-tun\r\nclient\r\nverb 3\r\n#auth-user-pass\r\n\r\n\r\n###############################################################################\r\n# The certificate file of the destination VPN Server.\r\n# \r\n# The CA certificate file is embedded in the inline format.\r\n# You can replace this CA contents if necessary.\r\n# Please note that if the server certificate is not a self-signed, you have to\r\n# specify the signer\'s root certificate (CA) here.\r\n\r\n<ca>\r\n-----BEGIN CERTIFICATE-----\r\nMIIFazCCA1OgAwIBAgIRAIIQz7DSQONZRGPgu2OCiwAwDQYJKoZIhvcNAQELBQAw\r\nTzELMAkGA1UEBhMCVVMxKTAnBgNVBAoTIEludGVybmV0IFNlY3VyaXR5IFJlc2Vh\r\ncmNoIEdyb3VwMRUwEwYDVQQDEwxJU1JHIFJvb3QgWDEwHhcNMTUwNjA0MTEwNDM4\r\nWhcNMzUwNjA0MTEwNDM4WjBPMQswCQYDVQQGEwJVUzEpMCcGA1UEChMgSW50ZXJu\r\nZXQgU2VjdXJpdHkgUmVzZWFyY2ggR3JvdXAxFTATBgNVBAMTDElTUkcgUm9vdCBY\r\nMTCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBAK3oJHP0FDfzm54rVygc\r\nh77ct984kIxuPOZXoHj3dcKi/vVqbvYATyjb3miGbESTtrFj/RQSa78f0uoxmyF+\r\n0TM8ukj13Xnfs7j/EvEhmkvBioZxaUpmZmyPfjxwv60pIgbz5MDmgK7iS4+3mX6U\r\nA5/TR5d8mUgjU+g4rk8Kb4Mu0UlXjIB0ttov0DiNewNwIRt18jA8+o+u3dpjq+sW\r\nT8KOEUt+zwvo/7V3LvSye0rgTBIlDHCNAymg4VMk7BPZ7hm/ELNKjD+Jo2FR3qyH\r\nB5T0Y3HsLuJvW5iB4YlcNHlsdu87kGJ55tukmi8mxdAQ4Q7e2RCOFvu396j3x+UC\r\nB5iPNgiV5+I3lg02dZ77DnKxHZu8A/lJBdiB3QW0KtZB6awBdpUKD9jf1b0SHzUv\r\nKBds0pjBqAlkd25HN7rOrFleaJ1/ctaJxQZBKT5ZPt0m9STJEadao0xAH0ahmbWn\r\nOlFuhjuefXKnEgV4We0+UXgVCwOPjdAvBbI+e0ocS3MFEvzG6uBQE3xDk3SzynTn\r\njh8BCNAw1FtxNrQHusEwMFxIt4I7mKZ9YIqioymCzLq9gwQbooMDQaHWBfEbwrbw\r\nqHyGO0aoSCqI3Haadr8faqU9GY/rOPNk3sgrDQoo//fb4hVC1CLQJ13hef4Y53CI\r\nrU7m2Ys6xt0nUW7/vGT1M0NPAgMBAAGjQjBAMA4GA1UdDwEB/wQEAwIBBjAPBgNV\r\nHRMBAf8EBTADAQH/MB0GA1UdDgQWBBR5tFnme7bl5AFzgAiIyBpY9umbbjANBgkq\r\nhkiG9w0BAQsFAAOCAgEAVR9YqbyyqFDQDLHYGmkgJykIrGF1XIpu+ILlaS/V9lZL\r\nubhzEFnTIZd+50xx+7LSYK05qAvqFyFWhfFQDlnrzuBZ6brJFe+GnY+EgPbk6ZGQ\r\n3BebYhtF8GaV0nxvwuo77x/Py9auJ/GpsMiu/X1+mvoiBOv/2X/qkSsisRcOj/KK\r\nNFtY2PwByVS5uCbMiogziUwthDyC3+6WVwW6LLv3xLfHTjuCvjHIInNzktHCgKQ5\r\nORAzI4JMPJ+GslWYHb4phowim57iaztXOoJwTdwJx4nLCgdNbOhdjsnvzqvHu7Ur\r\nTkXWStAmzOVyyghqpZXjFaH3pO3JLF+l+/+sKAIuvtd7u+Nxe5AW0wdeRlN8NwdC\r\njNPElpzVmbUq4JUagEiuTDkHzsxHpFKVK7q4+63SM1N95R1NbdWhscdCb+ZAJzVc\r\noyi3B43njTOQ5yOf+1CceWxG1bQVs5ZufpsMljq4Ui0/1lvh+wjChP4kqKOJ2qxq\r\n4RgqsahDYVvTH9w7jXbyLeiNdd8XM2w9U/t7y0Ff/9yi0GE44Za4rF2LN9d11TPA\r\nmRGunUHBcnWEvgJBQl9nJEiU0Zsnvgc/ubhPgXRR4Xq37Z0j4r7g1SgEEzwxA57d\r\nemyPxgcYxn/eR44/KJ4EBs+lVDR3veyJm+kXQ99b21/+jh5Xos1AnX5iItreGCc=\r\n-----END CERTIFICATE-----\r\n\r\n</ca>\r\n\r\n\r\n###############################################################################\r\n# The client certificate file (dummy).\r\n# \r\n# In some implementations of OpenVPN Client software\r\n# (for example: OpenVPN Client for iOS),\r\n# a pair of client certificate and private key must be included on the\r\n# configuration file due to the limitation of the client.\r\n# So this sample configuration file has a dummy pair of client certificate\r\n# and private key as follows.\r\n\r\n<cert>\r\n-----BEGIN CERTIFICATE-----\r\nMIICxjCCAa4CAQAwDQYJKoZIhvcNAQEFBQAwKTEaMBgGA1UEAxMRVlBOR2F0ZUNs\r\naWVudENlcnQxCzAJBgNVBAYTAkpQMB4XDTEzMDIxMTAzNDk0OVoXDTM3MDExOTAz\r\nMTQwN1owKTEaMBgGA1UEAxMRVlBOR2F0ZUNsaWVudENlcnQxCzAJBgNVBAYTAkpQ\r\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5h2lgQQYUjwoKYJbzVZA\r\n5VcIGd5otPc/qZRMt0KItCFA0s9RwReNVa9fDRFLRBhcITOlv3FBcW3E8h1Us7RD\r\n4W8GmJe8zapJnLsD39OSMRCzZJnczW4OCH1PZRZWKqDtjlNca9AF8a65jTmlDxCQ\r\nCjntLIWk5OLLVkFt9/tScc1GDtci55ofhaNAYMPiH7V8+1g66pGHXAoWK6AQVH67\r\nXCKJnGB5nlQ+HsMYPV/O49Ld91ZN/2tHkcaLLyNtywxVPRSsRh480jju0fcCsv6h\r\np/0yXnTB//mWutBGpdUlIbwiITbAmrsbYnjigRvnPqX1RNJUbi9Fp6C2c/HIFJGD\r\nywIDAQABMA0GCSqGSIb3DQEBBQUAA4IBAQChO5hgcw/4oWfoEFLu9kBa1B//kxH8\r\nhQkChVNn8BRC7Y0URQitPl3DKEed9URBDdg2KOAz77bb6ENPiliD+a38UJHIRMqe\r\nUBHhllOHIzvDhHFbaovALBQceeBzdkQxsKQESKmQmR832950UCovoyRB61UyAV7h\r\n+mZhYPGRKXKSJI6s0Egg/Cri+Cwk4bjJfrb5hVse11yh4D9MHhwSfCOH+0z4hPUT\r\nFku7dGavURO5SVxMn/sL6En5D+oSeXkadHpDs+Airym2YHh15h0+jPSOoR6yiVp/\r\n6zZeZkrN43kuS73KpKDFjfFPh8t4r1gOIjttkNcQqBccusnplQ7HJpsk\r\n-----END CERTIFICATE-----\r\n\r\n</cert>\r\n\r\n<key>\r\n-----BEGIN RSA PRIVATE KEY-----\r\nMIIEpAIBAAKCAQEA5h2lgQQYUjwoKYJbzVZA5VcIGd5otPc/qZRMt0KItCFA0s9R\r\nwReNVa9fDRFLRBhcITOlv3FBcW3E8h1Us7RD4W8GmJe8zapJnLsD39OSMRCzZJnc\r\nzW4OCH1PZRZWKqDtjlNca9AF8a65jTmlDxCQCjntLIWk5OLLVkFt9/tScc1GDtci\r\n55ofhaNAYMPiH7V8+1g66pGHXAoWK6AQVH67XCKJnGB5nlQ+HsMYPV/O49Ld91ZN\r\n/2tHkcaLLyNtywxVPRSsRh480jju0fcCsv6hp/0yXnTB//mWutBGpdUlIbwiITbA\r\nmrsbYnjigRvnPqX1RNJUbi9Fp6C2c/HIFJGDywIDAQABAoIBAERV7X5AvxA8uRiK\r\nk8SIpsD0dX1pJOMIwakUVyvc4EfN0DhKRNb4rYoSiEGTLyzLpyBc/A28Dlkm5eOY\r\nfjzXfYkGtYi/Ftxkg3O9vcrMQ4+6i+uGHaIL2rL+s4MrfO8v1xv6+Wky33EEGCou\r\nQiwVGRFQXnRoQ62NBCFbUNLhmXwdj1akZzLU4p5R4zA3QhdxwEIatVLt0+7owLQ3\r\nlP8sfXhppPOXjTqMD4QkYwzPAa8/zF7acn4kryrUP7Q6PAfd0zEVqNy9ZCZ9ffho\r\nzXedFj486IFoc5gnTp2N6jsnVj4LCGIhlVHlYGozKKFqJcQVGsHCqq1oz2zjW6LS\r\noRYIHgECgYEA8zZrkCwNYSXJuODJ3m/hOLVxcxgJuwXoiErWd0E42vPanjjVMhnt\r\nKY5l8qGMJ6FhK9LYx2qCrf/E0XtUAZ2wVq3ORTyGnsMWre9tLYs55X+ZN10Tc75z\r\n4hacbU0hqKN1HiDmsMRY3/2NaZHoy7MKnwJJBaG48l9CCTlVwMHocIECgYEA8jby\r\ndGjxTH+6XHWNizb5SRbZxAnyEeJeRwTMh0gGzwGPpH/sZYGzyu0SySXWCnZh3Rgq\r\n5uLlNxtrXrljZlyi2nQdQgsq2YrWUs0+zgU+22uQsZpSAftmhVrtvet6MjVjbByY\r\nDADciEVUdJYIXk+qnFUJyeroLIkTj7WYKZ6RjksCgYBoCFIwRDeg42oK89RFmnOr\r\nLymNAq4+2oMhsWlVb4ejWIWeAk9nc+GXUfrXszRhS01mUnU5r5ygUvRcarV/T3U7\r\nTnMZ+I7Y4DgWRIDd51znhxIBtYV5j/C/t85HjqOkH+8b6RTkbchaX3mau7fpUfds\r\nFq0nhIq42fhEO8srfYYwgQKBgQCyhi1N/8taRwpk+3/IDEzQwjbfdzUkWWSDk9Xs\r\nH/pkuRHWfTMP3flWqEYgW/LW40peW2HDq5imdV8+AgZxe/XMbaji9Lgwf1RY005n\r\nKxaZQz7yqHupWlLGF68DPHxkZVVSagDnV/sztWX6SFsCqFVnxIXifXGC4cW5Nm9g\r\nva8q4QKBgQCEhLVeUfdwKvkZ94g/GFz731Z2hrdVhgMZaU/u6t0V95+YezPNCQZB\r\nwmE9Mmlbq1emDeROivjCfoGhR3kZXW1pTKlLh6ZMUQUOpptdXva8XxfoqQwa3enA\r\nM7muBbF0XN7VO80iJPv+PmIZdEIAkpwKfi201YB+BafCIuGxIF50Vg==\r\n-----END RSA PRIVATE KEY-----\r\n\r\n</key>\r\n\r\n', 'Freemium', 1, '2025-12-21 07:58:35', 'assets/au.png');

-- --------------------------------------------------------

--
-- Table structure for table `vpn_sessions`
--

CREATE TABLE `vpn_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `ip_address` varchar(255) NOT NULL,
  `bytes_in` bigint(20) DEFAULT NULL,
  `bytes_out` bigint(20) DEFAULT NULL,
  `session_status` varchar(20) NOT NULL DEFAULT 'active',
  `profile_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admob_ads`
--
ALTER TABLE `admob_ads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_updates`
--
ALTER TABLE `app_updates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reseller_id` (`reseller_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `promos`
--
ALTER TABLE `promos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resellers`
--
ALTER TABLE `resellers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reseller_clients`
--
ALTER TABLE `reseller_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reseller_id` (`reseller_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reseller_id` (`reseller_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `troubleshooting_guides`
--
ALTER TABLE `troubleshooting_guides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `login_code` (`login_code`),
  ADD KEY `promo_id` (`promo_id`);

--
-- Indexes for table `vpn_profiles`
--
ALTER TABLE `vpn_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promo_id` (`promo_id`);

--
-- Indexes for table `vpn_sessions`
--
ALTER TABLE `vpn_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admob_ads`
--
ALTER TABLE `admob_ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_updates`
--
ALTER TABLE `app_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promos`
--
ALTER TABLE `promos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `resellers`
--
ALTER TABLE `resellers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `reseller_clients`
--
ALTER TABLE `reseller_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `troubleshooting_guides`
--
ALTER TABLE `troubleshooting_guides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `vpn_profiles`
--
ALTER TABLE `vpn_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vpn_sessions`
--
ALTER TABLE `vpn_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
