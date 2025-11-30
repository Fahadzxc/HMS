<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📋</span>
                    Dispense Transaction Logs
                </h2>
                <p class="page-subtitle">
                    Complete history of all dispensed medications and prescriptions
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Statistics Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Dispensed</div>
                <div class="kpi-value"><?= number_format(count($dispenseLogs ?? [])) ?></div>
                <div class="kpi-change">All time</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Dispensed Today</div>
                <div class="kpi-value"><?= number_format(count(array_filter($dispenseLogs ?? [], fn($log) => date('Y-m-d', strtotime($log['dispensed_at'] ?? '')) === date('Y-m-d')))) ?></div>
                <div class="kpi-change kpi-positive">Today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">This Month</div>
                <div class="kpi-value"><?= number_format(count(array_filter($dispenseLogs ?? [], fn($log) => date('Y-m', strtotime($log['dispensed_at'] ?? '')) === date('Y-m')))) ?></div>
                <div class="kpi-change kpi-positive">Current month</div>
            </div>
        </div>
    </div>
</section>

<!-- Dispense Logs Table -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Transaction History</h2>
        <p>All dispensed medications recorded in the system</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Medicine</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Price</th>
                        <th>Prescription ID</th>
                        <th>Pharmacist</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dispenseLogs)): ?>
                        <?php foreach ($dispenseLogs as $log): ?>
                            <tr style="border-left: 3px solid #10b981;">
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <strong style="color: #1e293b; font-size: 0.9rem;"><?= !empty($log['dispensed_at']) ? date('M j, Y', strtotime($log['dispensed_at'])) : '—' ?></strong>
                                        <small style="color: #64748b; font-size: 0.8rem;"><?= !empty($log['dispensed_at']) ? date('g:i A', strtotime($log['dispensed_at'])) : '—' ?></small>
                                        <span class="badge badge-success" style="padding: 0.2rem 0.5rem; border-radius: 999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; width: fit-content; margin-top: 0.25rem;">
                                            ✓ Dispensed
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: #1e293b;"><?= esc($log['patient_name'] ?? 'N/A') ?></strong>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <strong style="color: #1e293b;"><?= esc($log['medicine_name'] ?? 'N/A') ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: #1e293b; font-size: 1rem;"><?= number_format($log['quantity'] ?? 0) ?></strong>
                                    <br><small style="color: #64748b; font-size: 0.75rem;">units</small>
                                </td>
                                <td>
                                    <strong style="color: #059669;">₱<?= number_format($log['unit_price'] ?? 0, 2) ?></strong>
                                </td>
                                <td>
                                    <strong style="color: #059669; font-size: 1.1rem;">₱<?= number_format($log['total_price'] ?? 0, 2) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($log['prescription_id'])): ?>
                                        <span style="color: #3b82f6; font-weight: 600; font-family: monospace;">RX#<?= str_pad((string)$log['prescription_id'], 6, '0', STR_PAD_LEFT) ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <strong style="color: #1e293b;"><?= esc($log['pharmacist_name'] ?? 'System') ?></strong>
                                        <small style="color: #64748b; font-size: 0.75rem;">Pharmacist</small>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($log['prescription_id'])): ?>
                                        <a href="<?= base_url('pharmacy/prescriptions?rx_id=' . $log['prescription_id']) ?>" style="color: #3b82f6; text-decoration: none; font-size: 0.875rem; font-weight: 500; padding: 0.35rem 0.75rem; border: 1px solid #3b82f6; border-radius: 0.375rem; display: inline-block; transition: all 0.2s;">
                                            View RX
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                                No dispense logs found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

