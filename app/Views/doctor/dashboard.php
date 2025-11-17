<!-- Doctor dashboard partial (inner content only) -->

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>👨‍⚕️</span>
                    Doctor Dashboard
                </h2>
                <p class="page-subtitle">
                    Quick overview of today's appointments and patient care
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
                <div class="kpi-label">Today's Appointments</div>
                <div class="kpi-value"><?= number_format($todayAppointments ?? 0) ?></div>
                <div class="kpi-change kpi-positive">+2 from yesterday</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Patients</div>
                <div class="kpi-value"><?= number_format($totalPatients ?? 0) ?></div>
                <div class="kpi-change">&nbsp;</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Reports</div>
                <div class="kpi-value"><?= number_format($pendingReports ?? 0) ?></div>
                <div class="kpi-change kpi-negative">-1 from yesterday</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Revenue This Month</div>
                <div class="kpi-value">₱<?= number_format($monthRevenue ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">+8% from last month</div>
            </div>
        </div>
    </div>
</section>

<!-- Today's Appointments -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Today's Appointments</h2>
        <p>Your scheduled appointments for today</p>
    </header>
    <div class="stack">
        <?php if (!empty($appointments)): ?>
            <?php foreach ($appointments as $appointment): ?>
                <div class="card" style="padding: 1.25rem; border: 1px solid #e2e8f0; border-radius: 0.75rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                        <div>
                            <h3 style="margin: 0 0 0.25rem; font-size: 1.1rem; font-weight: 600; color: #1e293b;"><?= esc($appointment['patient_name'] ?? 'N/A') ?></h3>
                            <p style="margin: 0; color: #64748b; font-size: 0.9rem;"><?= esc($appointment['type'] ?? 'Consultation') ?></p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="color: #475569; font-size: 0.9rem; font-weight: 500;"><?= !empty($appointment['appointment_time']) ? date('g:i A', strtotime($appointment['appointment_time'])) : '—' ?></span>
                            <?php
                            $status = strtolower($appointment['status'] ?? 'upcoming');
                            $badgeClass = '';
                            if ($status === 'completed') {
                                $badgeClass = 'badge-success';
                            } elseif ($status === 'in progress' || $status === 'in_progress') {
                                $badgeClass = 'badge-warning';
                            } else {
                                $badgeClass = 'badge-danger';
                            }
                            ?>
                            <span class="badge <?= $badgeClass ?>" style="text-transform: uppercase; font-size: 0.75rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 999px;">
                                <?= strtoupper(str_replace('_', ' ', $status)) ?>
                            </span>
                        </div>
                    </div>
                    <a href="<?= base_url('doctor/appointments/view/' . ($appointment['id'] ?? '')) ?>" style="color: #3b82f6; text-decoration: none; font-size: 0.9rem; font-weight: 500;">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 2rem; text-align: center; color: #64748b;">
                <p style="margin: 0; font-size: 1rem;">No appointments scheduled for today</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Recent Appointments -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Appointments</h2>
        <p>Your recent appointments from the past week</p>
    </header>
    <div class="stack">
        <?php if (!empty($recentAppointments)): ?>
            <?php foreach ($recentAppointments as $appointment): ?>
                <div class="card" style="padding: 1.25rem; border: 1px solid #e2e8f0; border-radius: 0.75rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                        <div>
                            <h3 style="margin: 0 0 0.25rem; font-size: 1.1rem; font-weight: 600; color: #1e293b;"><?= esc($appointment['patient_name'] ?? 'N/A') ?></h3>
                            <p style="margin: 0; color: #64748b; font-size: 0.9rem;"><?= esc($appointment['type'] ?? 'Consultation') ?></p>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                            <span style="color: #475569; font-size: 0.9rem; font-weight: 500;">
                                <?= !empty($appointment['appointment_date']) ? date('M j, Y', strtotime($appointment['appointment_date'])) : '—' ?>
                                <?= !empty($appointment['appointment_time']) ? ' • ' . date('g:i A', strtotime($appointment['appointment_time'])) : '' ?>
                            </span>
                            <?php
                            $status = strtolower($appointment['status'] ?? 'completed');
                            $badgeClass = '';
                            if ($status === 'completed') {
                                $badgeClass = 'badge-success';
                            } elseif ($status === 'in progress' || $status === 'in_progress') {
                                $badgeClass = 'badge-warning';
                            } else {
                                $badgeClass = 'badge-danger';
                            }
                            ?>
                            <span class="badge <?= $badgeClass ?>" style="text-transform: uppercase; font-size: 0.75rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 999px;">
                                <?= strtoupper(str_replace('_', ' ', $status)) ?>
                            </span>
                        </div>
                    </div>
                    <a href="<?= base_url('doctor/appointments/view/' . ($appointment['id'] ?? '')) ?>" style="color: #3b82f6; text-decoration: none; font-size: 0.9rem; font-weight: 500;">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 2rem; text-align: center; color: #64748b;">
                <p style="margin: 0; font-size: 1rem;">No recent appointments</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.badge-success {
    background: #dcfce7;
    color: #15803d;
}

.badge-warning {
    background: #fef3c7;
    color: #b45309;
}

.badge-danger {
    background: #fee2e2;
    color: #b91c1c;
}
</style>
