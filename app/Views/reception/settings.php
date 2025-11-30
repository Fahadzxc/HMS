<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>👋</span>
                    Reception Settings
                </h2>
                <p class="page-subtitle">
                    Manage front-desk preferences and reminders
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<?php if (session()->getFlashdata('success')): ?>
    <div style="margin:1rem 0; padding:.75rem 1rem; border-radius:.5rem; border:1px solid #a7f3d0; background:#ecfdf5; color:#065f46;">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('reception/settings/save') ?>" method="post">
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Queue Management</h2>
            <p>Notifications when the lobby gets busy</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Alert Threshold (people)</label>
                <input type="number" min="1" name="reception_queue_alert" value="<?= esc($settings['reception_queue_alert'] ?? '20') ?>">
            </div>
            <div class="form-field">
                <label>Auto Assign Rooms</label>
                <select name="reception_auto_assign_room">
                    <option value="1" <?= ($settings['reception_auto_assign_room'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= ($settings['reception_auto_assign_room'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
            <div class="form-field">
                <label>Default Room/Desk</label>
                <input type="text" name="reception_default_room" value="<?= esc($settings['reception_default_room'] ?? 'OPD-1') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Notifications</h2>
            <p>Where reminders and escalations are sent</p>
        </header>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <label>Notification Email</label>
                <input type="email" name="reception_notification_email" value="<?= esc($settings['reception_notification_email'] ?? '') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Check-in Message</h2>
            <p>Displayed in kiosks or SMS reminders</p>
        </header>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <textarea name="reception_checkin_message" rows="4" style="resize:vertical; min-height:120px; border:1px solid #e2e8f0; border-radius:8px; padding:10px;"><?= esc($settings['reception_checkin_message'] ?? '') ?></textarea>
            </div>
        </div>
    </section>

    <div style="display:flex; gap:.5rem; padding:0 16px 16px 16px;">
        <button type="submit" class="btn-primary">Save Settings</button>
        <a href="<?= base_url('reception/dashboard') ?>" class="btn-secondary">Cancel</a>
    </div>
</form>

<?= $this->endSection() ?>


