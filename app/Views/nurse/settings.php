<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🩺</span>
                    Nurse Settings
                </h2>
                <p class="page-subtitle">
                    Personalize shift schedules and care preferences
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<?php if (session()->getFlashdata('success')): ?>
    <div style="margin:1rem 0; padding:.75rem 1rem; border:1px solid #a7f3d0; background:#ecfdf5; color:#065f46; border-radius:.5rem;">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('nurse/settings/save') ?>" method="post">
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Shift Preferences</h2>
            <p>Set your default working hours</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Shift Start</label>
                <input type="time" name="nurse_shift_start" value="<?= esc($settings['nurse_shift_start'] ?? '07:00') ?>">
            </div>
            <div class="form-field">
                <label>Shift End</label>
                <input type="time" name="nurse_shift_end" value="<?= esc($settings['nurse_shift_end'] ?? '19:00') ?>">
            </div>
            <div class="form-field">
                <label>Max Patients Per Shift</label>
                <input type="number" min="1" name="nurse_max_patients" value="<?= esc($settings['nurse_max_patients'] ?? '10') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Care Requirements</h2>
            <p>Safety and quality controls</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Require Vitals Before Treatment Update</label>
                <select name="nurse_require_vitals">
                    <option value="1" <?= ($settings['nurse_require_vitals'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= ($settings['nurse_require_vitals'] ?? '1') === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="form-field">
                <label>Task Reminders</label>
                <select name="nurse_task_reminders">
                    <option value="1" <?= ($settings['nurse_task_reminders'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= ($settings['nurse_task_reminders'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Handover Template</h2>
            <p>Default notes checklist for next shift</p>
        </header>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <textarea name="nurse_handover_template" rows="4" style="resize:vertical; min-height:120px; border:1px solid #e2e8f0; border-radius:8px; padding:10px;"><?= esc($settings['nurse_handover_template'] ?? '') ?></textarea>
            </div>
        </div>
    </section>

    <div style="display:flex; gap:.5rem; padding:0 16px 16px 16px;">
        <button type="submit" class="btn-primary">Save Settings</button>
        <a href="<?= base_url('nurse/dashboard') ?>" class="btn-secondary">Cancel</a>
    </div>
</form>

<?= $this->endSection() ?>


