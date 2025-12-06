<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🩺</span>
                    Consultations
                </h2>
                <p class="page-subtitle">
                    View your completed patient consultations
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
                <div class="kpi-label">Total Consultations</div>
                <div class="kpi-value"><?= number_format($totalConsultations ?? 0) ?></div>
                <div class="kpi-change">All time</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">This Month</div>
                <div class="kpi-value"><?= number_format($thisMonthConsultations ?? 0) ?></div>
                <div class="kpi-change kpi-positive">Current month</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">This Week</div>
                <div class="kpi-value"><?= number_format($thisWeekConsultations ?? 0) ?></div>
                <div class="kpi-change kpi-positive">Current week</div>
            </div>
        </div>
    </div>
</section>

<!-- Consultations List -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Consultation History</h2>
        <p>Your completed patient consultations</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($consultations)): ?>
                        <?php foreach ($consultations as $consultation): ?>
                            <tr>
                                <td>
                                    <strong><?= !empty($consultation['appointment_date']) ? date('M j, Y', strtotime($consultation['appointment_date'])) : '—' ?></strong><br>
                                    <span style="color: #64748b; font-size: 0.875rem;"><?= !empty($consultation['appointment_time']) ? date('g:i A', strtotime($consultation['appointment_time'])) : '—' ?></span>
                                </td>
                                <td>
                                    <strong><?= esc($consultation['patient_name'] ?? 'N/A') ?></strong><br>
                                    <span style="color: #64748b; font-size: 0.875rem;">
                                        <?php if (!empty($consultation['patient_age'])): ?>
                                            Age: <?= esc($consultation['patient_age']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($consultation['patient_gender'])): ?>
                                            • <?= ucfirst(esc($consultation['patient_gender'])) ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="text-transform: capitalize;"><?= esc($consultation['appointment_type'] ?? 'consultation') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status = strtolower($consultation['status'] ?? 'scheduled');
                                    $statusBadgeClass = '';
                                    if ($status === 'completed') {
                                        $statusBadgeClass = 'badge-success';
                                    } elseif ($status === 'confirmed') {
                                        $statusBadgeClass = 'badge-warning';
                                    } else {
                                        $statusBadgeClass = 'badge-info';
                                    }
                                    ?>
                                    <span class="badge <?= $statusBadgeClass ?>" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                        <?= strtoupper(str_replace('_', ' ', $status)) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($consultation['notes'])): ?>
                                        <span style="color: #475569;"><?= esc(substr($consultation['notes'], 0, 50)) ?><?= strlen($consultation['notes']) > 50 ? '...' : '' ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('doctor/consultations/view/' . $consultation['id']) ?>" style="color: #3b82f6; text-decoration: none; font-size: 0.9rem; font-weight: 500; margin-right: 1rem;">View</a>
                                    <?php if (!empty($consultation['prescription_id'])): ?>
                                        <a href="<?= base_url('doctor/prescriptions/view/' . $consultation['prescription_id']) ?>" style="color: #10b981; text-decoration: none; font-size: 0.9rem; font-weight: 500;">Prescription</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 2rem; text-align: center; color: #64748b;">
                                No consultations found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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

.badge-info {
    background: #dbeafe;
    color: #1d4ed8;
}
</style>


<?= $this->endSection() ?>

