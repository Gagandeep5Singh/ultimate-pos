-- Fix Kamal Tec Sales Status: Set to 'pending' if status is 'open' and Floa Ref is empty
-- This script updates existing sales to use the new 'pending' status

UPDATE kamal_tec_sales 
SET status = 'pending' 
WHERE status = 'open' 
AND (floa_ref IS NULL OR floa_ref = '' OR floa_ref = '-');

-- Also set any sales without Floa Ref to pending (regardless of current status, except cancelled)
UPDATE kamal_tec_sales 
SET status = 'pending' 
WHERE status != 'cancelled' 
AND (floa_ref IS NULL OR floa_ref = '' OR floa_ref = '-');

