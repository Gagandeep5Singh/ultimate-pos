-- ============================================
-- Add 'pending' Status to Kamal Tec Sales
-- ============================================
-- Run these SQL commands in phpMyAdmin SQL tab
-- ============================================

-- Step 1: Alter the status ENUM column to include 'pending'
ALTER TABLE `kamal_tec_sales` 
MODIFY COLUMN `status` ENUM('pending', 'open', 'closed', 'cancelled') NOT NULL DEFAULT 'pending';

-- Step 2: Update all sales without Floa Ref to 'pending' status
-- This updates sales that are currently 'open' but have no Floa Ref
UPDATE `kamal_tec_sales` 
SET `status` = 'pending' 
WHERE `status` = 'open' 
AND (`floa_ref` IS NULL OR `floa_ref` = '' OR `floa_ref` = '-');

-- Step 3: Verify the update
-- Run this to see the results:
SELECT `id`, `invoice_no`, `status`, `floa_ref` 
FROM `kamal_tec_sales` 
ORDER BY `id` DESC 
LIMIT 20;

-- ============================================
-- Expected Result:
-- - All sales without Floa Ref should now have status = 'pending'
-- - Sales with Floa Ref can remain 'open', 'closed', or 'cancelled'
-- ============================================
