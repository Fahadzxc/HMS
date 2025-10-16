<?= $this->extend('template/template') ?>

<?= $this->section('content') ?>

<?php
// Expecting $user_role set by controller
if (!isset($user_role)) {
    $user_role = session()->get('role');
}

switch ($user_role) {
    case 'admin':
        echo view('admin/dashboard');
        break;
    case 'doctor':
        echo view('doctor/dashboard');
        break;
    case 'nurse':
        echo view('nurse/dashboard');
        break;
    case 'receptionist':
        echo view('reception/dashboard');
        break;
    case 'lab':
        echo view('lab/dashboard');
        break;
    case 'pharmacist':
        echo view('pharmacy/dashboard');
        break;
    case 'accountant':
        echo view('accounts/dashboard');
        break;
    case 'it':
        echo view('it/dashboard');
        break;
    default:
        echo '<section class="panel"><header class="panel-header"><h2>Dashboard</h2></header><p>Role not supported.</p></section>';
}
?>

<?= $this->endSection() ?>


