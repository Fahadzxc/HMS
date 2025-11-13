<?php
// Simple script to create treatment_updates table
// Run: php create_treatment_table.php

// Default XAMPP MySQL settings - adjust if needed
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'HMS'; // Note: uppercase as per Database.php config

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

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
    echo "Table 'treatment_updates' created successfully!\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

$mysqli->close();

