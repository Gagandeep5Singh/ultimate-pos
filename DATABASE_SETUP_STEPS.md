# Database Setup Steps - phpMyAdmin Guide

## Step-by-Step Instructions

### Step 1: Verify Tables Exist

1. **In the left sidebar**, look for your database name (e.g., `u575880315_alphatec`)
2. **Click on the database name** to expand it
3. **Look for these 3 tables:**
   - `kamal_tec_sales`
   - `kamal_tec_sale_lines`
   - `kamal_tec_payments`

**If you see all 3 tables** → ✅ Tables exist, go to Step 2
**If tables are missing** → Go to Step 4 (Run Migrations)

---

### Step 2: Verify Table Structure

For each table, verify the structure:

#### A. Check `kamal_tec_sales` table:

1. **Click on `kamal_tec_sales`** in the left sidebar (or find it in the main table list)
2. **Click the "Structure" tab** at the top
3. **Verify these columns exist:**
   - `id` (primary key)
   - `business_id`
   - `location_id`
   - `contact_id`
   - `invoice_no`
   - **`kt_invoice_no`** ← Important! This should exist
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

#### B. Check `kamal_tec_sale_lines` table:

1. **Click on `kamal_tec_sale_lines`** in the left sidebar
2. **Click the "Structure" tab**
3. **Verify these columns exist:**
   - `id`
   - `kamal_tec_sale_id`
   - `product_id`
   - `sku_snapshot`
   - `product_name_snapshot`
   - `qty`
   - `unit_price`
   - `line_total`
   - `imei_serial`
   - `created_at`
   - `updated_at`

#### C. Check `kamal_tec_payments` table:

1. **Click on `kamal_tec_payments`** in the left sidebar
2. **Click the "Structure" tab**
3. **Verify these columns exist:**
   - `id`
   - `kamal_tec_sale_id`
   - `amount`
   - `paid_on`
   - `payment_method`
   - `note`
   - `created_by`
   - `created_at`
   - `updated_at`

---

### Step 3: Check if `kt_invoice_no` Column Exists

**This is the most important check!**

1. **Click on `kamal_tec_sales`** table
2. **Click "Structure" tab**
3. **Scroll down and look for `kt_invoice_no` column**

**If `kt_invoice_no` exists** → ✅ Perfect! Go to Step 5
**If `kt_invoice_no` is missing** → Go to Step 4 (Run Migration)

---

### Step 4: Run Migrations (If Tables/Columns Missing)

#### Option A: Run via Command Line (Recommended)

If you have SSH/terminal access:

```bash
cd /path/to/your/project
php artisan migrate
```

#### Option B: Run SQL Manually in phpMyAdmin

If you can't use command line, run these SQL queries:

1. **Click the "SQL" tab** at the top of phpMyAdmin
2. **Copy and paste each SQL statement below** and click "Go"

**SQL 1: Create kamal_tec_sales table**
```sql
CREATE TABLE IF NOT EXISTS `kamal_tec_sales` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `contact_id` int(11) NOT NULL,
  `invoice_no` varchar(191) NOT NULL,
  `kt_invoice_no` varchar(191) DEFAULT NULL,
  `sale_date` date NOT NULL,
  `total_amount` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `commission_type` enum('percent','fixed') NOT NULL,
  `commission_value` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `commission_amount` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `paid_amount` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `due_amount` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `status` enum('open','closed','cancelled') NOT NULL DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kamal_tec_sales_business_id_index` (`business_id`),
  KEY `kamal_tec_sales_contact_id_index` (`contact_id`),
  KEY `kamal_tec_sales_location_id_index` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**SQL 2: Create kamal_tec_sale_lines table**
```sql
CREATE TABLE IF NOT EXISTS `kamal_tec_sale_lines` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kamal_tec_sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku_snapshot` varchar(191) DEFAULT NULL,
  `product_name_snapshot` varchar(191) DEFAULT NULL,
  `qty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `unit_price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `line_total` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `imei_serial` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kamal_tec_sale_lines_kamal_tec_sale_id_index` (`kamal_tec_sale_id`),
  KEY `kamal_tec_sale_lines_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**SQL 3: Create kamal_tec_payments table**
```sql
CREATE TABLE IF NOT EXISTS `kamal_tec_payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kamal_tec_sale_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `paid_on` date NOT NULL,
  `payment_method` varchar(191) NOT NULL,
  `note` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kamal_tec_payments_kamal_tec_sale_id_index` (`kamal_tec_sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**SQL 4: Add kt_invoice_no column (if table exists but column is missing)**
```sql
ALTER TABLE `kamal_tec_sales` 
ADD COLUMN `kt_invoice_no` varchar(191) DEFAULT NULL AFTER `invoice_no`;
```

---

### Step 5: Verify Everything Works

1. **Click on `kamal_tec_sales`** table
2. **Click "Browse" tab** (should show empty or existing data)
3. **Click "Insert" tab** to test if you can add data (optional test)

---

### Step 6: Check Migration Status (Optional)

If you want to verify which migrations have run:

1. **Click the "SQL" tab**
2. **Run this query:**
```sql
SELECT * FROM `migrations` WHERE `migration` LIKE '%kamal_tec%' ORDER BY `id`;
```

You should see 4 rows:
- `2025_01_15_120000_create_kamal_tec_sales_table`
- `2025_01_15_120001_create_kamal_tec_sale_lines_table`
- `2025_01_15_120002_create_kamal_tec_payments_table`
- `2025_01_20_120000_add_kt_invoice_no_to_kamal_tec_sales_table`

---

## ✅ Success Checklist

- [ ] All 3 tables exist (`kamal_tec_sales`, `kamal_tec_sale_lines`, `kamal_tec_payments`)
- [ ] `kt_invoice_no` column exists in `kamal_tec_sales` table
- [ ] All tables have correct structure (columns match the list above)
- [ ] Can browse tables (even if empty)
- [ ] No errors when viewing table structure

---

## 🚨 If Something is Wrong

### Tables Don't Exist?
- Run the SQL queries from Step 4 (Option B)
- Or run `php artisan migrate` via command line

### `kt_invoice_no` Column Missing?
- Run SQL 4 from Step 4
- Or run: `php artisan migrate` (it should add it automatically)

### Getting Errors?
- Check the error message
- Verify you're in the correct database
- Make sure you have permission to create tables

---

## 🎉 Once Everything is Verified

After confirming all tables exist and have correct structure:

1. **Go back to your application**
2. **Log out and log back in**
3. **Check the sidebar menu** for "Kamal Tec Phone Sale"
4. **Test creating a sale**

Your database is ready! ✅
