<?php
// Mark treatment_updates migration as completed
// Run: php mark_treatment_migration.php

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'HMS';

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if table exists
$tableExists = $mysqli->query("SHOW TABLES LIKE 'treatment_updates'")->num_rows > 0;

if (!$tableExists) {
    echo "Table 'treatment_updates' does not exist. Creating it...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `treatment_updates` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `patient_id` int(11) unsigned NOT NULL,
      `time` varchar(20) DEFAULT NULL,
      `blood_pressure` varchar(50) DEFAULT NULL,
      `heart_rate` varchar(50) DEFAULT NULL,
      `temperature` varchar(50) DEFAULT NULL,
      `oxygen_saturation` varchar(50) DEFAULT NULL,
      `nurse_name` varchar(255) DEFAULT NULL,
      `notes` text DEFAULT NULL,
      `created_at` datetime DEFAULT NULL,
      `updated_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `patient_id` (`patient_id`),
      KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($mysqli->query($sql) === TRUE) {
        echo "Table created successfully!\n";
    } else {
        die("Error creating table: " . $mysqli->error . "\n");
    }
} else {
    echo "Table 'treatment_updates' already exists.\n";
}

// Check if migration is already recorded
$migrationVersion = '2025-11-11-120000';
$check = $mysqli->query("SELECT * FROM migrations WHERE version = '$migrationVersion'");

if ($check->num_rows == 0) {
    // Get the latest batch number
    $batchResult = $mysqli->query("SELECT MAX(batch) as max_batch FROM migrations");
    $batchRow = $batchResult->fetch_assoc();
    $nextBatch = ($batchRow['max_batch'] ?? 0) + 1;
    
    // Insert migration record
    $insertSql = "INSERT INTO migrations (version, class, `group`, namespace, time, batch) 
                  VALUES ('$migrationVersion', 'CreateTreatmentUpdatesTable', 'default', 'App', NOW(), $nextBatch)";
    
    if ($mysqli->query($insertSql) === TRUE) {
        echo "Migration marked as completed in migrations table!\n";
        echo "Now rollback will include this table.\n";
    } else {
        echo "Error marking migration: " . $mysqli->error . "\n";
    }
} else {
    echo "Migration is already recorded in migrations table.\n";
}

$mysqli->close();

