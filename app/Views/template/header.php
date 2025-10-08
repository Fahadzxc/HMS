<?php
// Header partial. When $useSidebar is true, render the admin topbar; otherwise render the public header navbar.
$useSidebar = $useSidebar ?? false;
?>

<?php if ($useSidebar): ?>
    <header class="topbar">
        <div class="page-title"><?= esc($pageTitle ?? 'Dashboard') ?></div>
        <div class="profile"><span class="avatar"></span><span><?= esc(session()->get('role') ? ucfirst(session()->get('role')) : 'Admin') ?></span></div>
    </header>
<?php else: ?>
    <header class="header">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-hospital"></i>
                </div>
                <h1>MediCare Hospital</h1>
            </div>
            <nav class="navbar">
                <ul class="navbar-nav">
                    <li><a href="<?= base_url('home') ?>" class="nav-link">Home</a></li>
                    <li><a href="<?= base_url('about') ?>" class="nav-link">About</a></li>
                    <li><a href="<?= base_url('contact') ?>" class="nav-link">Contact</a></li>
                    <li><a href="<?= base_url('login') ?>" class="nav-link">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>
<?php endif; ?>


