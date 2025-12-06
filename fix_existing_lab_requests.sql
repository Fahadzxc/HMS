-- Fix existing lab test requests to have billing_status = 'unbilled'
-- This ensures all lab tests (from doctor, walk-in, or lab staff) appear in billing

-- Step 1: Add billing_status column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'lab_test_requests';
SET @columnname = 'billing_status';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(20) NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 2: Set billing_status to 'unbilled' for ALL lab requests that don't have it set
-- This includes requests from doctors, walk-in, and lab staff
UPDATE lab_test_requests 
SET billing_status = 'unbilled' 
WHERE billing_status IS NULL OR billing_status = '';

-- Step 3: Verify - show all unbilled lab requests
SELECT 
    id, 
    patient_id, 
    test_type, 
    doctor_id,
    CASE 
        WHEN doctor_id IS NULL THEN 'Walk-in'
        ELSE 'From Doctor'
    END as request_type,
    status, 
    billing_status, 
    requested_at 
FROM lab_test_requests 
WHERE billing_status = 'unbilled' OR billing_status IS NULL
ORDER BY requested_at DESC
LIMIT 20;

