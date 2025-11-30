<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🩺</span>
                    Doctor Settings
                </h2>
                <p class="page-subtitle">
                    Configure clinic hours, telemedicine, and notifications
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

<form action="<?= base_url('doctor/settings/save') ?>" method="post">
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Clinic Hours</h2>
            <p>Default availability shown to reception and patients</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Start Time</label>
                <input type="time" name="doctor_clinic_start" value="<?= esc($settings['doctor_clinic_start'] ?? '09:00') ?>">
            </div>
            <div class="form-field">
                <label>End Time</label>
                <input type="time" name="doctor_clinic_end" value="<?= esc($settings['doctor_clinic_end'] ?? '17:00') ?>">
            </div>
            <div class="form-field">
                <label>Slot Duration (minutes)</label>
                <input type="number" min="5" step="5" name="doctor_slot_duration" value="<?= esc($settings['doctor_slot_duration'] ?? '30') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Telemedicine & Notifications</h2>
            <p>Enable video consults and automatic reminders</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Telemedicine Sessions</label>
                <select name="doctor_telemed_enabled">
                    <option value="1" <?= ($settings['doctor_telemed_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= ($settings['doctor_telemed_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
            <div class="form-field">
                <label>Auto Notify Patients</label>
                <select name="doctor_auto_notify_patient">
                    <option value="1" <?= ($settings['doctor_auto_notify_patient'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= ($settings['doctor_auto_notify_patient'] ?? '1') === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Signature Block</h2>
            <p>Used on prescriptions, reports, and referrals</p>
        </header>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <textarea name="doctor_signature_block" rows="4" style="resize:vertical; min-height:120px; border:1px solid #e2e8f0; border-radius:8px; padding:10px;"><?= esc($settings['doctor_signature_block'] ?? '') ?></textarea>
            </div>
        </div>
    </section>

    <div style="display:flex; gap:.5rem; padding:0 16px 16px 16px;">
        <button type="submit" class="btn-primary">Save Settings</button>
        <a href="<?= base_url('doctor/dashboard') ?>" class="btn-secondary">Cancel</a>
    </div>
</form>

<?= $this->endSection() ?>


