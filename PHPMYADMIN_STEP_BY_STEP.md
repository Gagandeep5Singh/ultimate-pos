# phpMyAdmin - Step by Step Database Setup Guide

## 📋 Quick Checklist

Follow these steps in order:

### ✅ Step 1: Check if Tables Exist

1. **In the left sidebar**, find your database (e.g., `u575880315_alphatec`)
2. **Click on the database name** to see all tables
3. **Look for these 3 tables:**
   - `kamal_tec_sales` ✅
   - `kamal_tec_sale_lines` ✅
   - `kamal_tec_payments` ✅

**What to do:**
- **If you see all 3 tables** → Go to Step 2
- **If any table is missing** → Go to Step 4 (Run SQL)

---

### ✅ Step 2: Check `kamal_tec_sales` Table Structure

1. **Click on `kamal_tec_sales`** in the left sidebar
2. **Click the "Structure" tab** (top menu)
3. **Scroll down and verify you see these columns:**

**Required Columns:**
- `id` (should be PRIMARY KEY)
- `business_id`
- `location_id`
- `contact_id`
- `invoice_no`
- **`kt_invoice_no`** ← **VERY IMPORTANT! Check this one!**
- `sale_date`
- `total_amount`
- `commission_type`
- `commission_value`
- `commission_amount`
- `paid_amount`
- `due_amount`
- `status`
- `notes`
- `created_by`
- `created_at`
- `updated_at`

**What to do:**
- **If `kt_invoice_no` exists** → ✅ Perfect! Go to Step 3
- **If `kt_invoice_no` is missing** → Go to Step 4, run SQL Query #4

---

### ✅ Step 3: Check Other Tables Structure

#### Check `kamal_tec_sale_lines`:
1. Click on `kamal_tec_sale_lines` in sidebar
2. Click "Structure" tab
3. Should have: `id`, `kamal_tec_sale_id`, `product_id`, `sku_snapshot`, `product_name_snapshot`, `qty`, `unit_price`, `line_total`, `imei_serial`, `created_at`, `updated_at`

#### Check `kamal_tec_payments`:
1. Click on `kamal_tec_payments` in sidebar
2. Click "Structure" tab
3. Should have: `id`, `kamal_tec_sale_id`, `amount`, `paid_on`, `payment_method`, `note`, `created_by`, `created_at`, `updated_at`

---

### ✅ Step 4: Run SQL Queries (If Tables/Columns Missing)

**Click the "SQL" tab** at the top of phpMyAdmin, then run these queries one by one:

#### SQL Query 1: Create `kamal_tec_sales` table
```sql
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
```

#### SQL Query 2: Create `kamal_tec_sale_lines` table
```sql
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
```

#### SQL Query 3: Create `kamal_tec_payments` table
```sql
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
```

#### SQL Query 4: Add `kt_invoice_no` column (IMPORTANT!)
```sql
ALTER TABLE `kamal_tec_sales` 
ADD COLUMN `kt_invoice_no` varchar(191) DEFAULT NULL AFTER `invoice_no`;
```

**Note:** Only run this if the `kt_invoice_no` column is missing from `kamal_tec_sales` table.

---

### ✅ Step 5: Verify Everything

1. **Go back to the main database view** (click database name in sidebar)
2. **You should see all 3 tables listed**
3. **Click on each table** → Click "Browse" → Should show empty table (or existing data)
4. **No errors?** → ✅ Database is ready!

---

### ✅ Step 6: Test (Optional)

1. **Click on `kamal_tec_sales`** table
2. **Click "Insert" tab**
3. **Try inserting a test row** (optional - just to verify it works)
4. **Then delete the test row** if you added one

---

## 🎯 Quick Action Summary

**In phpMyAdmin, do this:**

1. ✅ Check if 3 tables exist in left sidebar
2. ✅ Click `kamal_tec_sales` → "Structure" → Check for `kt_invoice_no` column
3. ✅ If missing, click "SQL" tab → Run SQL Query #4 above
4. ✅ Done!

---

## ✅ Final Checklist

- [ ] All 3 tables exist
- [ ] `kt_invoice_no` column exists in `kamal_tec_sales`
- [ ] Can browse all tables (even if empty)
- [ ] No errors in phpMyAdmin

**Once all checked → Your database is ready! 🎉**

---

## 🚨 Common Issues

### "Table already exists" error?
- ✅ That's fine! The table already exists, skip that query.

### "Column already exists" error?
- ✅ That's fine! The column already exists, skip that query.

### Foreign key errors?
- Make sure `business`, `contacts`, `users`, `products`, `business_locations` tables exist first.

### Can't see tables in sidebar?
- Refresh the page (F5)
- Make sure you clicked on the correct database name

---

**After completing these steps, go back to your application and test it!**
