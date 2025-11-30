<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🏥</span>
                    Inpatients
                </h2>
                <p class="page-subtitle">
                    Your current inpatient assignments
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Inpatient List</h2>
        <p>Patients admitted under your care</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Room</th>
                        <th>Status</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inpatients)): ?>
                        <?php foreach ($inpatients as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= !empty($row['appointment_date']) ? date('M j, Y', strtotime($row['appointment_date'])) : '—' ?></strong><br>
                                    <span style="color: #64748b; font-size: 0.875rem;"><?= !empty($row['appointment_time']) ? date('g:i A', strtotime($row['appointment_time'])) : '—' ?></span>
                                </td>
                                <td>
                                    <strong><?= esc($row['patient_name'] ?? 'N/A') ?></strong><br>
                                    <span style="color: #64748b; font-size: 0.875rem;">
                                        <?php if (!empty($row['age'])): ?>Age: <?= esc($row['age']) ?><?php endif; ?>
                                        <?php if (!empty($row['gender'])): ?> • <?= ucfirst(esc($row['gender'])) ?><?php endif; ?>
                                    </span>
                                </td>
                                <td><?= esc($row['room_number'] ?? '—') ?></td>
                                <td><span class="badge badge-info"><?= strtoupper(esc($row['status'] ?? 'confirmed')) ?></span></td>
                                <td><?= esc($row['appointment_type'] ?? 'emergency') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 2rem; text-align: center; color: #64748b;">
                                No inpatients assigned
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
.badge-info { background: #dbeafe; color: #1d4ed8; padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
</style>

<?= $this->endSection() ?>


