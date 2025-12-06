-- Fix doctor_id column to allow NULL for lab test appointments

-- Step 1: Drop foreign key constraint (adjust constraint name if different)
ALTER TABLE appointments DROP FOREIGN KEY IF EXISTS appointments_doctor_id_foreign;
ALTER TABLE appointments DROP FOREIGN KEY IF EXISTS appointments_ibfk_2;

-- Step 2: Temporarily disable foreign key checks
SET FOREIGN_KEY_CHECKS=0;

-- Step 3: Modify doctor_id column to allow NULL
ALTER TABLE appointments MODIFY doctor_id INT(11) UNSIGNED NULL;

-- Step 4: Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- Step 5: Re-add foreign key constraint (allows NULL values)
ALTER TABLE appointments ADD CONSTRAINT appointments_doctor_id_foreign 
FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

-- Verify
SHOW COLUMNS FROM appointments WHERE Field = 'doctor_id';
