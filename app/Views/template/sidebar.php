<?php
$uri      = service('uri');
$segment  = strtolower((string) ($segment ?? (session()->get('role') ?: $uri->getSegment(1))));
$menus = [
    'admin' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Patients', 'url' => base_url('admin/patients')],
        ['label' => 'Appointments', 'url' => base_url('admin/appointments')],
        ['label' => 'Billing & Payments', 'url' => base_url('admin/billing')],
        ['label' => 'Laboratory', 'url' => base_url('laboratory')],
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
        ['label' => 'Appointments', 'url' => base_url('nurse/appointments')],
        ['label' => 'Tasks', 'url' => base_url('nurse/tasks')],
        ['label' => 'Schedule', 'url' => base_url('nurse/schedule')],
        ['label' => 'Settings', 'url' => base_url('settings')],
        ['label' => 'Logout', 'url' => base_url('auth/logout')],
    ],
];
$roleKey = in_array($segment, array_keys($menus), true) ? $segment : (session()->get('role') ?: 'admin');
$brandLabel = $roleKey === 'doctor' ? 'Doctor Portal' : ($roleKey === 'nurse' ? 'Nurse Portal' : 'Administrator');
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


