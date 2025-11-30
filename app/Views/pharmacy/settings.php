<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>💊</span>
                    Pharmacy Settings
                </h2>
                <p class="page-subtitle">
                    Control inventory alerts, restocking, and dispensing rules
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

<form action="<?= base_url('pharmacy/settings/save') ?>" method="post">
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Inventory Alerts</h2>
            <p>Thresholds for low stock and expiring items</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Default Reorder Level</label>
                <input type="number" min="1" name="pharmacy_reorder_level" value="<?= esc($settings['pharmacy_reorder_level'] ?? '15') ?>">
            </div>
            <div class="form-field">
                <label>Expiry Warning (days)</label>
                <input type="number" min="1" name="pharmacy_expiry_warning" value="<?= esc($settings['pharmacy_expiry_warning'] ?? '60') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Restocking & Suppliers</h2>
            <p>Automate purchase orders and notifications</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Auto Restock Requests</label>
                <select name="pharmacy_auto_restock">
                    <option value="1" <?= ($settings['pharmacy_auto_restock'] ?? '0') === '1' ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= ($settings['pharmacy_auto_restock'] ?? '0') === '0' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
            <div class="form-field">
                <label>Supplier Notification Email</label>
                <input type="email" name="pharmacy_supplier_email" value="<?= esc($settings['pharmacy_supplier_email'] ?? '') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Dispensing Safety</h2>
            <p>Reinforce double-checking before release</p>
        </header>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <label>Require Double-Check Before Dispense</label>
                <select name="pharmacy_dispense_doublecheck">
                    <option value="1" <?= ($settings['pharmacy_dispense_doublecheck'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= ($settings['pharmacy_dispense_doublecheck'] ?? '1') === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
        </div>
    </section>

    <div style="display:flex; gap:.5rem; padding:0 16px 16px 16px;">
        <button type="submit" class="btn-primary">Save Settings</button>
        <a href="<?= base_url('pharmacy/dashboard') ?>" class="btn-secondary">Cancel</a>
    </div>
</form>

<?= $this->endSection() ?>


