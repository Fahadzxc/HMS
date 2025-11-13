<?php
// Run only the treatment_updates migration
// Usage: php run_treatment_migration.php

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(FCPATH . '../') . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'system' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);

require_once SYSTEMPATH . 'bootstrap.php';

$migration = \Config\Services::migrations();
$migration->setNamespace('App');

// Run specific migration
$migrationFile = '2025-11-11-120000_CreateTreatmentUpdatesTable';
$migration->version($migrationFile);

echo "Migration completed!\n";

