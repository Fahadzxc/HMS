<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🧾</span>
                    Accountant Settings
                </h2>
                <p class="page-subtitle">
                    Configure invoicing, billing, and communication preferences
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<?php if (session()->getFlashdata('success')): ?>
    <div style="margin:1rem 0; padding:.75rem 1rem; background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; border-radius:.5rem;">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('accounts/settings/save') ?>" method="post">
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Invoice Settings</h2>
            <p>Control numbering and tax computation</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Invoice Prefix</label>
                <input type="text" name="accounts_invoice_prefix" value="<?= esc($settings['accounts_invoice_prefix'] ?? 'ACC') ?>">
            </div>
            <div class="form-field">
                <label>Starting Number</label>
                <input type="number" min="1" name="accounts_invoice_start" value="<?= esc($settings['accounts_invoice_start'] ?? '1000') ?>">
            </div>
            <div class="form-field">
                <label>Default Tax Rate (%)</label>
                <input type="number" step="0.01" min="0" name="accounts_tax_rate" value="<?= esc($settings['accounts_tax_rate'] ?? '12') ?>">
            </div>
            <div class="form-field">
                <label>Show Currency</label>
                <input type="text" name="accounts_show_currency" value="<?= esc($settings['accounts_show_currency'] ?? 'PHP') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Payment Policies</h2>
            <p>Manage payment terms and due dates</p>
        </header>
        <div class="form-grid">
            <div class="form-field">
                <label>Payment Terms</label>
                <input type="text" name="accounts_payment_terms" value="<?= esc($settings['accounts_payment_terms'] ?? 'Net 30') ?>">
            </div>
            <div class="form-field">
                <label>Default Due (days)</label>
                <input type="number" min="1" name="accounts_due_days" value="<?= esc($settings['accounts_due_days'] ?? '30') ?>">
            </div>
            <div class="form-field form-field--full">
                <label>Auto Reminders</label>
                <select name="accounts_auto_reminders">
                    <option value="1" <?= ($settings['accounts_auto_reminders'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= ($settings['accounts_auto_reminders'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Notifications</h2>
            <p>Where billing alerts will be sent</p>
        </header>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <label>Notification Email</label>
                <input type="email" name="accounts_notification_email" value="<?= esc($settings['accounts_notification_email'] ?? '') ?>">
            </div>
        </div>
    </section>

    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>Statements</h2>
            <p>Footer message shown on invoices and statements</p>
        </header>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <label>Statement Footer</label>
                <textarea name="accounts_statement_footer" rows="3" style="resize:vertical; min-height:100px; padding:10px; border:1px solid #e2e8f0; border-radius:8px;"><?= esc($settings['accounts_statement_footer'] ?? '') ?></textarea>
            </div>
        </div>
    </section>

    <div style="display:flex; gap:.5rem; padding:0 16px 16px 16px;">
        <button type="submit" class="btn-primary">Save Settings</button>
        <a href="<?= base_url('accounts/dashboard') ?>" class="btn-secondary">Cancel</a>
    </div>
</form>

<?= $this->endSection() ?>


