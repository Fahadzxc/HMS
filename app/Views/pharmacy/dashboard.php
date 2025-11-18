<!-- Pharmacy dashboard partial (inner content only) -->

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>💊</span>
                    Pharmacy Dashboard
                </h2>
                <p class="page-subtitle">
                    Welcome back, <?= esc($user_name ?? 'Pharmacist') ?>. Here's your pharmacy overview for today.
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- KPI Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Prescriptions</div>
                <div class="kpi-value"><?= number_format($pendingPrescriptions ?? 0) ?></div>
                <div class="kpi-change kpi-warning">Awaiting dispense</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Dispensed Today</div>
                <div class="kpi-value"><?= number_format($dispensedToday ?? 0) ?></div>
                <div class="kpi-change kpi-positive">Today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Low Stock Items</div>
                <div class="kpi-value"><?= number_format($lowStockItems ?? 0) ?></div>
                <div class="kpi-change kpi-warning">Needs attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Expiring Soon</div>
                <div class="kpi-value"><?= number_format($expiringSoon ?? 0) ?></div>
                <div class="kpi-change kpi-warning">Within 30 days</div>
            </div>
        </div>
    </div>
</section>

<!-- Pending Prescriptions -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Pending Prescriptions</h2>
        <a href="<?= base_url('pharmacy/prescriptions') ?>" style="color: #3b82f6; text-decoration: none; font-weight: 500;">View all</a>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Medication</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($prescriptions)): ?>
                        <?php foreach (array_slice($prescriptions, 0, 5) as $rx): ?>
                            <tr>
                                <td><strong><?= esc($rx['rx_number'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($rx['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($rx['doctor_name'] ?? 'N/A') ?></td>
                                <td><?= esc($rx['medication'] ?? 'N/A') ?></td>
                                <td><strong><?= number_format($rx['quantity'] ?? 0) ?></strong></td>
                                <td>
                                    <?php
                                    $status = strtolower($rx['status'] ?? 'pending');
                                    $badgeClass = $status === 'pending' ? 'badge-warning' : 'badge-success';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: <?= $status === 'pending' ? '#fef3c7' : '#dcfce7' ?>; color: <?= $status === 'pending' ? '#b45309' : '#15803d' ?>;">
                                        <?= strtoupper($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('pharmacy/prescriptions') ?>" style="color: #3b82f6; text-decoration: none; font-size: 0.9rem; font-weight: 500;">Dispense</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                                No pending prescriptions
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
