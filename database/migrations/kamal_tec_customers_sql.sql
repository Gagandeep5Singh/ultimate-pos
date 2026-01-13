-- ============================================
-- KAMAL TEC CUSTOMERS - SQL QUERIES FOR phpMyAdmin
-- ============================================
-- Copy and paste these queries one by one in phpMyAdmin SQL tab
-- ============================================

-- ============================================
-- QUERY 1: Create kamal_tec_customers table
-- ============================================
CREATE TABLE IF NOT EXISTS `kamal_tec_customers` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL,
  `nif` varchar(50) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `dob_country` varchar(100) DEFAULT NULL,
  `address` text,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kamal_tec_customers_business_id_index` (`business_id`),
  KEY `kamal_tec_customers_first_name_index` (`first_name`),
  KEY `kamal_tec_customers_last_name_index` (`last_name`),
  KEY `kamal_tec_customers_nif_index` (`nif`),
  KEY `kamal_tec_customers_email_index` (`email`),
  CONSTRAINT `kamal_tec_customers_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kamal_tec_customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUERY 2: Make contact_id nullable in kamal_tec_sales table
-- ============================================
-- First, drop the foreign key constraint
ALTER TABLE `kamal_tec_sales` 
DROP FOREIGN KEY `kamal_tec_sales_contact_id_foreign`;

-- Make contact_id nullable
ALTER TABLE `kamal_tec_sales` 
MODIFY COLUMN `contact_id` int(10) UNSIGNED NULL;

-- Re-add the foreign key constraint with SET NULL on delete
ALTER TABLE `kamal_tec_sales` 
ADD CONSTRAINT `kamal_tec_sales_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

-- ============================================
-- QUERY 3: Add customer_id column to kamal_tec_sales table
-- ============================================
ALTER TABLE `kamal_tec_sales` 
ADD COLUMN `customer_id` int(10) UNSIGNED NULL AFTER `contact_id`,
ADD INDEX `kamal_tec_sales_customer_id_index` (`customer_id`),
ADD CONSTRAINT `kamal_tec_sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `kamal_tec_customers` (`id`) ON DELETE SET NULL;

