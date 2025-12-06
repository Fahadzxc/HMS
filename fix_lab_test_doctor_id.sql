image.png-- Fix doctor_id column to allow NULL for walk-in lab test requests

-- Step 1: Drop foreign key constraint (adjust constraint name if different)
ALTER TABLE lab_test_requests DROP FOREIGN KEY IF EXISTS lab_test_requests_doctor_id_foreign;
ALTER TABLE lab_test_requests DROP FOREIGN KEY IF EXISTS lab_test_requests_ibfk_1;

-- Step 2: Temporarily disable foreign key checks
SET FOREIGN_KEY_CHECKS=0;

-- Step 3: Modify doctor_id column to allow NULL
ALTER TABLE lab_test_requests MODIFY doctor_id INT(11) UNSIGNED NULL;

-- Step 4: Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- Step 5: Re-add foreign key constraint (allows NULL values)
ALTER TABLE lab_test_requests ADD CONSTRAINT lab_test_requests_doctor_id_foreign 
FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

-- Verify
SHOW COLUMNS FROM lab_test_requests WHERE Field = 'doctor_id';
