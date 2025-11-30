<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🧪</span>
                    Laboratory Settings
                </h2>
                <p class="page-subtitle">
                    Configure default priorities, staffing, and notifications
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

<form action="<?= base_url('lab/settings/save') ?>" method="post">
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Test Handling</h2>
            <p>Defaults for new lab requests</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Default Priority</label>
                <select name="lab_default_priority">
                    <?php foreach (['normal','high','critical'] as $priority): ?>
                        <option value="<?= $priority ?>" <?= ($settings['lab_default_priority'] ?? 'normal') === $priority ? 'selected' : '' ?>>
                            <?= ucfirst($priority) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label>Auto Assign Staff</label>
                <select name="lab_auto_assign_staff">
                    <option value="1" <?= ($settings['lab_auto_assign_staff'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= ($settings['lab_auto_assign_staff'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
            <div class="form-field">
                <label>Urgent Threshold (hours)</label>
                <input type="number" min="1" name="lab_urgent_threshold" value="<?= esc($settings['lab_urgent_threshold'] ?? '6') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Notifications</h2>
            <p>Escalation emails and reminders</p>
        </header>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <label>Notification Email</label>
                <input type="email" name="lab_notification_email" value="<?= esc($settings['lab_notification_email'] ?? '') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Report Signature</h2>
            <p>Footer used on printed lab results</p>
        </header>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <textarea name="lab_report_signature" rows="4" style="resize:vertical; min-height:120px; border:1px solid #e2e8f0; border-radius:8px; padding:10px;"><?= esc($settings['lab_report_signature'] ?? '') ?></textarea>
            </div>
        </div>
    </section>

    <div style="display:flex; gap:.5rem; padding:0 16px 16px 16px;">
        <button type="submit" class="btn-primary">Save Settings</button>
        <a href="<?= base_url('lab/dashboard') ?>" class="btn-secondary">Cancel</a>
    </div>
</form>

<?= $this->endSection() ?>


