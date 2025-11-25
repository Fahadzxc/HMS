<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Reception Reports
                </h2>
                <p class="page-subtitle">
                    View patient registrations, appointments, and check-ins
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Report Filters -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Report Filters</h2>
    </header>
    <form method="GET" action="<?= base_url('reception/reports') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Report Type</label>
            <select name="type" id="reportType" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="patients" <?= ($report_type ?? 'patients') === 'patients' ? 'selected' : '' ?>>New Patients</option>
                <option value="appointments" <?= ($report_type ?? '') === 'appointments' ? 'selected' : '' ?>>Appointments</option>
                <option value="all" <?= ($report_type ?? '') === 'all' ? 'selected' : '' ?>>All Reports</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date From</label>
            <input type="date" name="date_from" value="<?= esc($date_from ?? date('Y-m-01')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date To</label>
            <input type="date" name="date_to" value="<?= esc($date_to ?? date('Y-m-d')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 0 0 auto;">
            <button type="submit" style="padding: 0.5rem 1.5rem; background: #4299e1; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500;">Apply Filters</button>
        </div>
    </form>
</section>

<!-- Summary Cards -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Summary</h2>
    </header>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">New Patients</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_new_patients'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Appointments</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_appointments'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Check-ins</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_checkins'] ?? 0) ?></div>
        </div>
        <?php if (!empty($summary['appointments_by_status'])): ?>
            <?php foreach (array_slice($summary['appointments_by_status'], 0, 1) as $status => $count): ?>
                <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;"><?= ucfirst($status) ?> Appointments</div>
                    <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($count) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php if (($report_type ?? 'patients') === 'patients' || ($report_type ?? '') === 'all'): ?>
    <!-- New Patients Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>👥 New Patients Report</h2>
            <p>Patient registrations from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date Registered</th>
                        <th>Patient ID</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Contact</th>
                        <th>Patient Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($new_patients)): ?>
                        <?php foreach ($new_patients as $patient): ?>
                            <tr>
                                <td><?= !empty($patient['created_at']) ? date('M j, Y g:i A', strtotime($patient['created_at'])) : '—' ?></td>
                                <td><strong><?= esc($patient['patient_id'] ?? 'N/A') ?></strong></td>
                                <td><strong><?= esc($patient['full_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($patient['gender'] ?? '—') ?></td>
                                <td><?= esc($patient['age'] ?? '—') ?></td>
                                <td><?= esc($patient['contact'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= ucfirst(esc($patient['patient_type'] ?? 'outpatient')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= esc($patient['status'] ?? 'active') ?>">
                                        <?= ucfirst(esc($patient['status'] ?? 'active')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No new patients found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'patients') === 'appointments' || ($report_type ?? '') === 'all'): ?>
    <!-- Appointments Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>📅 Appointments Report</h2>
            <p>Appointments from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
        </header>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Doctor</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td><?= !empty($apt['appointment_date']) ? date('M j, Y', strtotime($apt['appointment_date'])) : '—' ?></td>
                                <td><?= !empty($apt['appointment_time']) ? date('g:i A', strtotime($apt['appointment_time'])) : '—' ?></td>
                                <td><strong><?= esc($apt['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($apt['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($apt['doctor_name'] ?? 'N/A') ?></td>
                                <td><?= esc($apt['appointment_type'] ?? 'Consultation') ?></td>
                                <td>
                                    <span class="badge badge-<?= esc($apt['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($apt['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= esc($apt['room_number'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No appointments found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?= $this->endSection() ?>

