-- ============================================
-- KAMAL TEC PHONE SALE - SQL QUERIES FOR phpMyAdmin
-- ============================================
-- Copy and paste these queries one by one in phpMyAdmin SQL tab
-- ============================================

-- ============================================
-- QUERY 1: Create kamal_tec_sales table
-- ============================================
-- Only run this if the table doesn't exist
CREATE TABLE IF NOT EXISTS `kamal_tec_sales` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id` int(10) UNSIGNED NOT NULL,
  `location_id` int(10) UNSIGNED DEFAULT NULL,
  `contact_id` int(10) UNSIGNED NOT NULL,
  `invoice_no` varchar(191) NOT NULL,
  `sale_date` date NOT NULL,
  `status` enum('open','closed','cancelled') NOT NULL DEFAULT 'open',
  `total_amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `commission_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `commission_value` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `commission_amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `paid_amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `due_amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `notes` text,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kamal_tec_sales_invoice_no_unique` (`invoice_no`),
  KEY `kamal_tec_sales_business_id_index` (`business_id`),
  KEY `kamal_tec_sales_contact_id_index` (`contact_id`),
  KEY `kamal_tec_sales_location_id_index` (`location_id`),
  KEY `kamal_tec_sales_sale_date_index` (`sale_date`),
  KEY `kamal_tec_sales_status_index` (`status`),
  CONSTRAINT `kamal_tec_sales_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kamal_tec_sales_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kamal_tec_sales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kamal_tec_sales_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `business_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUERY 2: Create kamal_tec_sale_lines table
-- ============================================
CREATE TABLE IF NOT EXISTS `kamal_tec_sale_lines` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kamal_tec_sale_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `sku_snapshot` varchar(191) DEFAULT NULL,
  `product_name_snapshot` varchar(191) DEFAULT NULL,
  `qty` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `unit_price` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `line_total` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `imei_serial` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kamal_tec_sale_lines_kamal_tec_sale_id_index` (`kamal_tec_sale_id`),
  KEY `kamal_tec_sale_lines_product_id_index` (`product_id`),
  CONSTRAINT `kamal_tec_sale_lines_kamal_tec_sale_id_foreign` FOREIGN KEY (`kamal_tec_sale_id`) REFERENCES `kamal_tec_sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kamal_tec_sale_lines_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUERY 3: Create kamal_tec_payments table
-- ============================================
CREATE TABLE IF NOT EXISTS `kamal_tec_payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kamal_tec_sale_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `paid_on` date NOT NULL,
  `payment_method` varchar(191) NOT NULL,
  `note` text,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kamal_tec_payments_kamal_tec_sale_id_index` (`kamal_tec_sale_id`),
  CONSTRAINT `kamal_tec_payments_kamal_tec_sale_id_foreign` FOREIGN KEY (`kamal_tec_sale_id`) REFERENCES `kamal_tec_sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kamal_tec_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUERY 4: Add kt_invoice_no column
-- ============================================
-- IMPORTANT: Only run this if kt_invoice_no column is missing!
-- First check the Structure tab of kamal_tec_sales table
ALTER TABLE `kamal_tec_sales` 
ADD COLUMN `kt_invoice_no` varchar(191) DEFAULT NULL AFTER `invoice_no`;

-- ============================================
-- QUERY 5: Check if tables exist (Verification)
-- ============================================
-- Run this to see which tables exist
SHOW TABLES LIKE 'kamal_tec%';

-- ============================================
-- QUERY 6: Check kt_invoice_no column exists
-- ============================================
-- Run this to verify the column exists
SHOW COLUMNS FROM `kamal_tec_sales` LIKE 'kt_invoice_no';

-- ============================================
-- QUERY 7: Check table structure (Full)
-- ============================================
-- Run this to see all columns in kamal_tec_sales
DESCRIBE `kamal_tec_sales`;
