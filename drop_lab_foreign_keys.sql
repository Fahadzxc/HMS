-- SQL Commands to Drop Foreign Keys and Tables
-- Run these commands ONE BY ONE in phpMyAdmin SQL tab
-- If a table doesn't exist, skip that command and continue

-- ============================================
-- PART 1: Drop Foreign Keys (only if tables exist)
-- ============================================

-- Drop foreign key from lab_test_requests (run only if table exists)
ALTER TABLE `lab_test_requests` DROP FOREIGN KEY `fk_lab_requests_staff`;

-- Drop foreign key from lab_test_results (run only if table exists)
ALTER TABLE `lab_test_results` DROP FOREIGN KEY `fk_lab_results_released_by`;

-- Drop foreign keys from lab_equipment_logs (run only if table exists)
-- Skip this if you get "table doesn't exist" error
ALTER TABLE `lab_equipment_logs` DROP FOREIGN KEY `fk_lab_equipment_logs_staff`;
ALTER TABLE `lab_equipment_logs` DROP FOREIGN KEY `fk_lab_equipment_logs_equipment`;

-- Drop foreign key from lab_inventory_logs (run only if table exists)
ALTER TABLE `lab_inventory_logs` DROP FOREIGN KEY `fk_lab_inventory_logs_item`;

-- Drop foreign key from lab_sample_transfers (run only if table exists)
ALTER TABLE `lab_sample_transfers` DROP FOREIGN KEY `fk_lab_transfer_request`;

-- ============================================
-- PART 2: Drop Tables (safe - will skip if doesn't exist)
-- ============================================

DROP TABLE IF EXISTS `lab_sample_transfers`;
DROP TABLE IF EXISTS `lab_inventory_logs`;
DROP TABLE IF EXISTS `lab_inventory_items`;
DROP TABLE IF EXISTS `lab_equipment_logs`;
DROP TABLE IF EXISTS `lab_equipment`;
DROP TABLE IF EXISTS `lab_staff`;
DROP TABLE IF EXISTS `lab_departments`;

