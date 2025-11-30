<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>⚙️</span>
                    System Settings
                </h2>
                <p class="page-subtitle">
                    Configure global options across modules
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<?php if (session()->getFlashdata('success')): ?>
    <div style="margin: 1rem 0; padding: .75rem 1rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: .5rem;">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div style="margin: 1rem 0; padding: .75rem 1rem; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: .5rem;">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('admin/settings/save') ?>" method="post" enctype="multipart/form-data">

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>General</h2>
            <p>Hospital information and basic preferences</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Hospital Name</label>
                <input type="text" name="hospital_name" value="<?= esc($settings['hospital_name'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label>Hospital Email</label>
                <input type="email" name="hospital_email" value="<?= esc($settings['hospital_email'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label>Phone</label>
                <input type="text" name="hospital_phone" value="<?= esc($settings['hospital_phone'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label>Address</label>
                <input type="text" name="hospital_address" value="<?= esc($settings['hospital_address'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label>Currency</label>
                <input type="text" name="currency" value="<?= esc($settings['currency'] ?? 'PHP') ?>">
            </div>
            <div class="form-field">
                <label>Timezone</label>
                <input type="text" name="timezone" value="<?= esc($settings['timezone'] ?? 'Asia/Manila') ?>">
            </div>
        </div>
        <div class="form-field form-field--full" style="margin-top: 4px; padding: 0 16px 16px 16px;">
            <label>Logo</label>
            <div style="display:flex; gap:12px; align-items:center;">
                <input type="file" name="logo_file" accept="image/*">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?= base_url($settings['logo_path']) ?>" alt="Logo" style="height:40px; border-radius:8px; border:1px solid #e2e8f0; background:#fff;">
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Modules</h2>
            <p>Enable or disable application modules</p>
        </header>
        <div class="stack" style="padding-top: 0;">
            <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <input id="mod_appointments" type="checkbox" name="module_appointments" value="1" <?= ($settings['module_appointments'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label for="mod_appointments" style="margin:0; font-weight:500;">Appointments</label>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <input id="mod_laboratory" type="checkbox" name="module_laboratory" value="1" <?= ($settings['module_laboratory'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label for="mod_laboratory" style="margin:0; font-weight:500;">Laboratory</label>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <input id="mod_pharmacy" type="checkbox" name="module_pharmacy" value="1" <?= ($settings['module_pharmacy'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label for="mod_pharmacy" style="margin:0; font-weight:500;">Pharmacy</label>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <input id="mod_accounts" type="checkbox" name="module_accounts" value="1" <?= ($settings['module_accounts'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label for="mod_accounts" style="margin:0; font-weight:500;">Billing & Accounts</label>
                </div>
                <div style="display:flex; align-items:center; gap:10px; grid-column: 1 / -1;">
                    <input id="mod_reports" type="checkbox" name="module_reports" value="1" <?= ($settings['module_reports'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label for="mod_reports" style="margin:0; font-weight:500;">Reports</label>
                </div>
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Appointments</h2>
            <p>Booking behavior and limits</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Allow Overbooking</label>
                <select name="appointments_overbook">
                    <option value="0" <?= ($settings['appointments_overbook'] ?? '0') === '0' ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= ($settings['appointments_overbook'] ?? '0') === '1' ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>
            <div class="form-field">
                <label>Booking Window (days)</label>
                <input type="number" min="1" name="appointments_window_days" value="<?= esc($settings['appointments_window_days'] ?? '30') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Nurse</h2>
            <p>Care workflows</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Require Vitals Before Treatment Update</label>
                <select name="nurse_require_vitals">
                    <option value="1" <?= ($settings['nurse_require_vitals'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= ($settings['nurse_require_vitals'] ?? '1') === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Laboratory</h2>
            <p>Result handling</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Auto-approve Results</label>
                <select name="lab_auto_approve">
                    <option value="0" <?= ($settings['lab_auto_approve'] ?? '0') === '0' ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= ($settings['lab_auto_approve'] ?? '0') === '1' ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Pharmacy</h2>
            <p>Inventory thresholds</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Low Stock Threshold</label>
                <input type="number" min="0" name="pharmacy_low_stock" value="<?= esc($settings['pharmacy_low_stock'] ?? '10') ?>">
            </div>
            <div class="form-field">
                <label>Expiry Warning (days)</label>
                <input type="number" min="0" name="pharmacy_expiry_warn" value="<?= esc($settings['pharmacy_expiry_warn'] ?? '90') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Billing</h2>
            <p>Finance configuration</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Tax Rate (%)</label>
                <input type="number" step="0.01" min="0" name="billing_tax_rate" value="<?= esc($settings['billing_tax_rate'] ?? '0') ?>">
            </div>
        </div>
    </section>

    <div style="display:flex; gap: .5rem; padding: 0 16px 16px 16px;">
        <button type="submit" class="btn-primary">Save Settings</button>
        <a href="<?= base_url('admin/dashboard') ?>" class="btn-secondary">Cancel</a>
    </div>

</form>

<?= $this->endSection() ?>


