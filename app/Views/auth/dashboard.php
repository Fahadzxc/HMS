<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<?php
// Expecting $user_role set by controller
if (!isset($user_role)) {
    $user_role = session()->get('role');
}

switch ($user_role) {
    case 'admin':
        echo view('admin');
        break;
    case 'doctor':
        echo view('doctor');
        break;
    case 'nurse':
        echo view('nurse');
        break;
    default:
        echo '<section class="panel"><header class="panel-header"><h2>Dashboard</h2></header><p>Role not supported.</p></section>';
}
?>

<?= $this->endSection() ?>


