<!-- Admin dashboard partial (inner content only) -->
<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Admin Dashboard
                </h2>
                <p class="page-subtitle">
                    High-level overview of hospital operations and system status
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- KPI Summary Cards -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Key Performance Indicators</h2>
        <p>System metrics and statistics</p>
    </header>
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Patients</div>
                <div class="kpi-value"><?= number_format($totalPatients ?? 0) ?></div>
                <div class="kpi-change">Updated today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Doctors</div>
                <div class="kpi-value"><?= number_format($totalDoctors ?? 0) ?></div>
                <div class="kpi-change">Updated today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Nurses</div>
                <div class="kpi-value"><?= number_format($totalNurses ?? 0) ?></div>
                <div class="kpi-change">Updated today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Today's Appointments</div>
                <div class="kpi-value"><?= number_format($todayAppointments ?? 0) ?></div>
                <div class="kpi-change">Updated today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Appointments</div>
                <div class="kpi-value"><?= number_format($pendingAppointments ?? 0) ?></div>
                <div class="kpi-change kpi-warning">Updated today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Active Lab Tests</div>
                <div class="kpi-value"><?= number_format($activeLabTests ?? 0) ?></div>
                <div class="kpi-change">Updated today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Low Stock Medicines</div>
                <div class="kpi-value"><?= number_format($lowStockMedicines ?? 0) ?></div>
                <div class="kpi-change kpi-warning">Updated today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Unpaid Bills</div>
                <div class="kpi-value"><?= number_format($unpaidBills ?? 0) ?></div>
                <div class="kpi-change kpi-negative">Updated today</div>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Grid -->
<div class="page-grid">
    
    <!-- Appointments Overview -->
    <section class="panel">
        <header class="panel-header">
            <h2>Appointments Overview</h2>
            <p>Appointment statistics and recent bookings</p>
        </header>
        <div class="stack">
            <div class="admin-stat-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Upcoming</div>
                    <div class="admin-stat-value"><?= number_format($upcomingAppointments ?? 0) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">This Week</div>
                    <div class="admin-stat-value"><?= number_format($appointmentsThisWeek ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($latestAppointments)): ?>
                            <?php foreach ($latestAppointments as $apt): ?>
                                <tr>
                                    <td><?= esc($apt['patient_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($apt['doctor_name'] ?? 'N/A') ?></td>
                                    <td><?= !empty($apt['appointment_date']) ? date('M j, Y', strtotime($apt['appointment_date'])) : '—' ?></td>
                                    <td><?= !empty($apt['appointment_time']) ? date('g:i A', strtotime($apt['appointment_time'])) : '—' ?></td>
                                    <td>
                                        <span class="badge <?= $apt['status'] === 'pending' ? 'badge-warning' : ($apt['status'] === 'completed' ? 'badge-success' : 'badge-secondary') ?>">
                                            <?= strtoupper($apt['status'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="admin-empty-message">No appointments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Laboratory Overview -->
    <section class="panel">
        <header class="panel-header">
            <h2>Laboratory Overview</h2>
            <p>Lab test statistics and recent requests</p>
        </header>
        <div class="stack">
            <div class="admin-stat-grid-3">
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Pending</div>
                    <div class="admin-stat-value"><?= number_format($pendingLabTests ?? 0) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Completed Today</div>
                    <div class="admin-stat-value"><?= number_format($completedTestsToday ?? 0) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Critical</div>
                    <div class="admin-stat-value" style="color: #ef4444;"><?= number_format($criticalResults ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Test Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($latestLabRequests)): ?>
                            <?php foreach ($latestLabRequests as $lab): ?>
                                <tr>
                                    <td><?= esc($lab['patient_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($lab['test_type'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge <?= strtolower($lab['priority'] ?? 'normal') === 'urgent' ? 'badge-danger' : ($lab['priority'] === 'high' ? 'badge-warning' : 'badge-info') ?>">
                                            <?= strtoupper($lab['priority'] ?? 'NORMAL') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $lab['status'] === 'pending' ? 'badge-warning' : ($lab['status'] === 'completed' ? 'badge-success' : 'badge-info') ?>">
                                            <?= strtoupper($lab['status'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="admin-empty-message">No lab requests found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Pharmacy & Inventory Overview -->
    <section class="panel">
        <header class="panel-header">
            <h2>Pharmacy & Inventory Overview</h2>
            <p>Stock levels and inventory movements</p>
        </header>
        <div class="stack">
            <div class="admin-stat-grid-3">
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Low Stock</div>
                    <div class="admin-stat-value" style="color: #f59e0b;"><?= number_format($lowStockCount ?? 0) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Expiring Soon</div>
                    <div class="admin-stat-value" style="color: #ef4444;"><?= number_format($expiringSoonCount ?? 0) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Movements Today</div>
                    <div class="admin-stat-value"><?= number_format($stockMovementsToday ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Movement Type</th>
                            <th>Quantity Change</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($latestStockMovements)): ?>
                            <?php foreach ($latestStockMovements as $movement): ?>
                                <?php
                                $movementType = strtolower($movement['movement_type'] ?? '');
                                $typeLabel = $movementType === 'add' ? 'ADDITION' : ($movementType === 'dispense' ? 'DISPENSE' : 'ADJUSTMENT');
                                $typeClass = $movementType === 'add' ? 'badge-success' : ($movementType === 'dispense' ? 'badge-warning' : 'badge-info');
                                $qtyChange = (int)($movement['quantity_change'] ?? 0);
                                ?>
                                <tr>
                                    <td><?= esc($movement['medicine_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge <?= $typeClass ?>">
                                            <?= $typeLabel ?>
                                        </span>
                                    </td>
                                    <td class="<?= $qtyChange > 0 ? 'pharmacy-qty-positive' : 'pharmacy-qty-negative' ?>">
                                        <?= $qtyChange > 0 ? '+' : '' ?><?= number_format($qtyChange) ?>
                                    </td>
                                    <td><?= !empty($movement['created_at']) ? date('M j, Y', strtotime($movement['created_at'])) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="admin-empty-message">No stock movements found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Billing & Payments Overview -->
    <section class="panel">
        <header class="panel-header">
            <h2>Billing & Payments Overview</h2>
            <p>Financial metrics and payment transactions</p>
        </header>
        <div class="stack">
            <div class="admin-stat-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Revenue This Month</div>
                    <div class="admin-stat-value" style="color: #10b981;">₱<?= number_format($totalRevenueThisMonth ?? 0, 2) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Outstanding Invoices</div>
                    <div class="admin-stat-value" style="color: #ef4444;"><?= number_format($outstandingInvoices ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($latestPayments)): ?>
                            <?php foreach ($latestPayments as $payment): ?>
                                <tr>
                                    <td><?= esc($payment['patient_name'] ?? 'N/A') ?></td>
                                    <td><strong>₱<?= number_format($payment['amount'] ?? 0, 2) ?></strong></td>
                                    <td><?= !empty($payment['created_at']) ? date('M j, Y', strtotime($payment['created_at'])) : '—' ?></td>
                                    <td><?= esc(ucfirst($payment['payment_method'] ?? 'N/A')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="admin-empty-message">No payments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Recent Activity Feed -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Activity Feed</h2>
        <p>Chronological feed of latest system events</p>
    </header>
    <div class="stack">
        <?php if (!empty($activityFeed)): ?>
            <?php foreach ($activityFeed as $activity): ?>
                <div class="admin-activity-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <p class="admin-activity-description">
                                <?= esc($activity['description']) ?>
                            </p>
                            <div class="admin-activity-meta">
                                <span class="admin-module-badge">
                                    <?= esc($activity['module']) ?>
                                </span>
                                <small class="admin-activity-time">
                                    <?= date('M j, Y g:i A', strtotime($activity['timestamp'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="admin-stat-card">
                <p class="admin-empty-message">No recent activities</p>
            </div>
        <?php endif; ?>
    </div>
</section>
