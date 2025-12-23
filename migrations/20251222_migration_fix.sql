-- Comprehensive migration script to fix database schema inconsistencies.

-- Set session variables to avoid issues with foreign key constraints during migration.
SET FOREIGN_KEY_CHECKS=0;

-- 1. Modify the `users` table.
-- This table is missing columns for tracking data usage (`bytes_in`, `bytes_out`),
-- linking to a VPN profile (`profile_id`), and storing user email.
-- The `role` column type is changed to VARCHAR for flexibility, and `data_usage` is dropped
-- as it is superseded by the new byte tracking columns.
ALTER TABLE `users`
  ADD COLUMN `bytes_in` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN `bytes_out` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN `profile_id` INT(11) DEFAULT NULL,
  ADD COLUMN `email` VARCHAR(255) DEFAULT NULL,
  MODIFY COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'user',
  DROP COLUMN `data_usage`;

-- 2. Modify the `vpn_profiles` table.
-- 2. Create the `profile_promos` table (if it doesn't exist).
-- This table is required for the new many-to-many relationship. It's created here so it's
-- available for the data migration step that follows.
CREATE TABLE IF NOT EXISTS `profile_promos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `profile_id` INT(11) NOT NULL,
  `promo_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`),
  KEY `promo_id` (`promo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Migrate existing promo relationships.
-- This step copies the `promo_id` from `vpn_profiles` into the new `profile_promos`
-- junction table, preserving the existing relationships before the old column is dropped.
INSERT INTO `profile_promos` (profile_id, promo_id)
SELECT id, promo_id FROM `vpn_profiles` WHERE promo_id IS NOT NULL;

-- 4. Modify the `vpn_profiles` table.
-- This table is missing columns for remote management (`management_ip`, `management_port`).
-- The `name` column is renamed to `profile_name` to match the column name expected in several PHP files.
-- The existing `promo_id` is dropped now that the data has been migrated.
ALTER TABLE `vpn_profiles`
  ADD COLUMN `management_ip` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN `management_port` INT(11) DEFAULT NULL,
  CHANGE COLUMN `name` `profile_name` VARCHAR(255) NOT NULL,
  DROP COLUMN `promo_id`;

-- 5. Create the `zip_password` table.
-- This table is missing and is required by `get_password.php`.
CREATE TABLE IF NOT EXISTS `zip_password` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Create the `admob_settings` table.
-- This table is missing and is required by `settings.php`.
CREATE TABLE IF NOT EXISTS `admob_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `value` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Create the `carriers` table.
-- This table is missing and is required for managing promos by carrier.
CREATE TABLE IF NOT EXISTS `carriers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Modify the `promos` table.
-- A `carrier_id` is added to associate promos with a specific carrier.
ALTER TABLE `promos`
  ADD COLUMN `carrier_id` INT(11) DEFAULT NULL;

-- 8. Modify the `vpn_sessions` table.
-- A `session_id` column is added as it is expected by the API for status checks.
ALTER TABLE `vpn_sessions`
  ADD COLUMN `session_id` VARCHAR(255) DEFAULT NULL;

-- 9. Standardize table engines to InnoDB.
-- All tables are converted to InnoDB to ensure support for foreign keys and transactions,
-- improving data integrity.
ALTER TABLE `users` ENGINE=InnoDB;
ALTER TABLE `accounts` ENGINE=InnoDB;
ALTER TABLE `admob_ads` ENGINE=InnoDB;
ALTER TABLE `app_updates` ENGINE=InnoDB;
ALTER TABLE `commissions` ENGINE=InnoDB;
ALTER TABLE `migrations` ENGINE=InnoDB;
ALTER TABLE `payments` ENGINE=InnoDB;
ALTER TABLE `promos` ENGINE=InnoDB;
ALTER TABLE `resellers` ENGINE=InnoDB;
ALTER TABLE `reseller_clients` ENGINE=InnoDB;
ALTER TABLE `sales` ENGINE=InnoDB;
ALTER TABLE `settings` ENGINE=InnoDB;
ALTER TABLE `troubleshooting_guides` ENGINE=InnoDB;
ALTER TABLE `vpn_profiles` ENGINE=InnoDB;
ALTER TABLE `vpn_sessions` ENGINE=InnoDB;

-- Re-enable foreign key checks.
SET FOREIGN_KEY_CHECKS=1;

-- Log that the migration was successful.
INSERT INTO `migrations` (`migration`) VALUES ('20251222_migration_fix.sql');
