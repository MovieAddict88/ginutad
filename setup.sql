DROP DATABASE IF EXISTS cornerst_vpn;
CREATE DATABASE cornerst_vpn;
USE cornerst_vpn;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `is_reseller` tinyint(1) NOT NULL DEFAULT 0,
  `account_id` int(11) DEFAULT NULL,
  `client_limit` int(11) NOT NULL DEFAULT 0,
  `reseller_id` int(11) DEFAULT NULL,
  `credits` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expiration_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `banned` tinyint(1) NOT NULL DEFAULT 0,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `payment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `login_code` varchar(255) UNIQUE,
  `device_id` varchar(255),
  `billing_month` varchar(255),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reseller_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reseller_id` (`reseller_id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`reseller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings` (`name`, `value`) VALUES
('site_name', 'VPN Panel'),
('site_icon', 'icon.png'),
('client_cost', '10.00');

CREATE TABLE `configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `os` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` varchar(10) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `languages` (`name`, `code`, `is_default`) VALUES
('English', 'en', 1),
('Spanish', 'es', 0);

CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` varchar(10) NOT NULL,
  `translation_key` varchar(255) NOT NULL,
  `translation_value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `language_translation` (`language_code`,`translation_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `vpn_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `vpn_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `commissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reseller_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `commission_earned` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `resellers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `primary_color` varchar(7) DEFAULT NULL,
  `secondary_color` varchar(7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `resellers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `reseller_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reseller_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
