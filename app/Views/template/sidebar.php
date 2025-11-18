<?php
$uri      = service('uri');
$segment  = strtolower((string) ($segment ?? (session()->get('role') ?: $uri->getSegment(1))));
$menus = [
    'admin' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Patients', 'url' => base_url('admin/patients')],
        ['label' => 'Doctors', 'url' => base_url('admin/doctors')],
        ['label' => 'Nurses', 'url' => base_url('admin/nurses')],
        ['label' => 'Appointments', 'url' => base_url('admin/appointments')],
        ['label' => 'Billing & Payments', 'url' => base_url('admin/billing')],
        ['label' => 'Laboratory', 'url' => base_url('admin/lab')],
        ['label' => 'Pharmacy & Inventory', 'url' => base_url('pharmacy')],
        ['label' => 'Reports', 'url' => base_url('reports')],
        ['label' => 'User Management', 'url' => base_url('admin/users')],
        ['label' => 'Settings', 'url' => base_url('settings')],
        ['label' => 'Logout', 'url' => base_url('auth/logout')],
    ],
    'doctor' => [
        ['label' => 'Dashboard', 'url' => base_url('doctor/dashboard')],
        ['label' => 'Patient Records', 'url' => base_url('doctor/patients')],
        ['label' => 'Appointments', 'url' => base_url('doctor/appointments')],
        ['label' => 'Prescriptions', 'url' => base_url('doctor/prescriptions')],
        ['label' => 'Lab Requests', 'url' => base_url('doctor/labs')],
        ['label' => 'Consultations', 'url' => base_url('doctor/consultations')],
        ['label' => 'My Schedule', 'url' => base_url('doctor/schedule')],
        ['label' => 'Medical Reports', 'url' => base_url('doctor/reports')],
        ['label' => 'Settings', 'url' => base_url('settings')],
        ['label' => 'Logout', 'url' => base_url('auth/logout')],
    ],
    'nurse' => [
        ['label' => 'Dashboard', 'url' => base_url('nurse/dashboard')],
        ['label' => 'Patients', 'url' => base_url('nurse/patients')],
        ['label' => 'Treatment Updates', 'url' => base_url('nurse/treatment-updates')],
        ['label' => 'Settings', 'url' => base_url('settings')],
        ['label' => 'Logout', 'url' => base_url('auth/logout')],
    ],
    'receptionist' => [
        ['label' => 'Dashboard', 'url' => base_url('reception/dashboard')],
        ['label' => 'Patient Registration', 'url' => base_url('reception/patients')],
        ['label' => 'Appointments', 'url' => base_url('reception/appointments')],
        ['label' => 'Check-in', 'url' => base_url('reception/checkin')],
        ['label' => 'Billing', 'url' => base_url('reception/billing')],
        ['label' => 'Schedule', 'url' => base_url('reception/schedule')],
        ['label' => 'Settings', 'url' => base_url('settings')],
        ['label' => 'Logout', 'url' => base_url('auth/logout')],
    ],
    'lab' => [
        ['label' => 'Dashboard', 'url' => base_url('lab/dashboard')],
        ['label' => 'Test Requests', 'url' => base_url('lab/requests')],
        ['label' => 'Test Results', 'url' => base_url('lab/results')],
        ['label' => 'Settings', 'url' => base_url('settings')],
        ['label' => 'Logout', 'url' => base_url('auth/logout')],
    ],
    'pharmacist' => [
        ['label' => 'Dashboard', 'url' => base_url('pharmacy/dashboard')],
        ['label' => 'Prescriptions', 'url' => base_url('pharmacy/prescriptions')],
        ['label' => 'Inventory', 'url' => base_url('pharmacy/inventory')],
        ['label' => 'Dispense', 'url' => base_url('pharmacy/dispense')],
        ['label' => 'Stock Movement', 'url' => base_url('pharmacy/stockMovement')],
        ['label' => 'Orders', 'url' => base_url('pharmacy/orders')],
        ['label' => 'Reports', 'url' => base_url('pharmacy/reports')],
        ['label' => 'Settings', 'url' => base_url('settings')],
        ['label' => 'Logout', 'url' => base_url('auth/logout')],
    ],
    'accountant' => [
        ['label' => 'Dashboard', 'url' => base_url('accounts/dashboard')],
        ['label' => 'Billing', 'url' => base_url('accounts/billing')],
        ['label' => 'Payments', 'url' => base_url('accounts/payments')],
        ['label' => 'Insurance', 'url' => base_url('accounts/insurance')],
        ['label' => 'Reports', 'url' => base_url('accounts/reports')],
        ['label' => 'Financial', 'url' => base_url('accounts/financial')],
        ['label' => 'Settings', 'url' => base_url('settings')],
        ['label' => 'Logout', 'url' => base_url('auth/logout')],
    ],
    'it' => [
        ['label' => 'Dashboard', 'url' => base_url('it/dashboard')],
        ['label' => 'System Status', 'url' => base_url('it/system')],
        ['label' => 'User Management', 'url' => base_url('it/users')],
        ['label' => 'Backup', 'url' => base_url('it/backup')],
        ['label' => 'Security', 'url' => base_url('it/security')],
        ['label' => 'Support Tickets', 'url' => base_url('it/tickets')],
        ['label' => 'Settings', 'url' => base_url('settings')],
        ['label' => 'Logout', 'url' => base_url('auth/logout')],
    ],
];
$roleKey = in_array($segment, array_keys($menus), true) ? $segment : (session()->get('role') ?: 'admin');
$brandLabels = [
    'admin' => 'Administrator',
    'doctor' => 'Doctor Portal',
    'nurse' => 'Nurse Portal',
    'receptionist' => 'Reception Portal',
    'lab' => 'Lab Portal',
    'pharmacist' => 'Pharmacy Portal',
    'accountant' => 'Accounts Portal',
    'it' => 'IT Portal'
];
$brandLabel = $brandLabels[$roleKey] ?? 'HMS Portal';
$menuToRender = $menus[$roleKey];
$currentUrl = current_url();
?>

<aside class="sidebar">
    <div class="brand"><?= esc($brandLabel) ?></div>
    <nav>
        <ul class="menu">
            <?php foreach ($menuToRender as $item): ?>
                <?php $isActive = strpos($currentUrl, $item['url']) === 0; ?>
                <li><a class="<?= $isActive ? 'active' : '' ?>" href="<?= $item['url'] ?>"><?= esc($item['label']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>


