<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<?php
$metrics = $metrics ?? ['newPatientsToday' => 0, 'newPatientsChange' => 0, 'appointmentsToday' => 0, 'walkInsToday' => 0, 'dischargedToday' => 0];
$tasks = $tasks ?? [];
$quickActions = $quickActions ?? [];
$appointmentsByStatus = $appointmentsByStatus ?? [];
$upcomingAppointments = $upcomingAppointments ?? [];
?>

<section class="panel">
    <header class="panel-header">
        <h2>Reception Dashboard</h2>
        <p>Quick overview of today's patient flow and appointments</p>
    </header>
    <div class="stack">
        <div class="card receptionist-card">
            <div class="row between">
                <div>
                    <h3><?= esc($user_name ?? 'Logged-in User') ?></h3>
                    <p><?= esc($user_email ?? '') ?></p>
                </div>
                <?php if (!empty($receptionistProfile)): ?>
                <div class="receptionist-meta">
                    <?php if (!empty($receptionistProfile['employee_id'])): ?>
                        <div><strong>Employee ID:</strong> <?= esc($receptionistProfile['employee_id']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($receptionistProfile['department'])): ?>
                        <div><strong>Department:</strong> <?= esc($receptionistProfile['department']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($receptionistProfile['shift'])): ?>
                        <div><strong>Shift:</strong> <?= esc(ucfirst($receptionistProfile['shift'])) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">New Patients Today</div>
                    <div class="kpi-value"><?= number_format($metrics['newPatientsToday']) ?></div>
                    <div class="kpi-change <?= ($metrics['newPatientsChange'] ?? 0) >= 0 ? 'kpi-positive' : 'kpi-negative' ?>">
                        <?= ($metrics['newPatientsChange'] >= 0 ? '+' : '') . number_format($metrics['newPatientsChange']) ?> from yesterday
                    </div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Appointments</div>
                    <div class="kpi-value"><?= number_format($metrics['appointmentsToday']) ?></div>
                    <div class="kpi-change">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Walk-ins</div>
                    <div class="kpi-value"><?= number_format($metrics['walkInsToday']) ?></div>
                    <div class="kpi-change"><?= ($metrics['walkInsToday'] ?? 0) > 0 ? '<span class="kpi-positive">Active</span>' : '&nbsp;' ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Discharged</div>
                    <div class="kpi-value"><?= number_format($metrics['dischargedToday']) ?></div>
                    <div class="kpi-change">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Today's Tasks</h2>
        <p>Your assigned tasks for today</p>
    </header>
    <div class="stack">
        <?php foreach ($tasks as $task): ?>
            <div class="card">
                <div class="row between">
                    <h3><?= esc($task['title']) ?></h3>
                    <?php if (!empty($task['status'])): ?>
                        <span class="badge <?= $task['status'] === 'urgent' ? 'high' : ($task['status'] === 'pending' ? 'medium' : 'success') ?>">
                            <?= strtoupper($task['status']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <p><?= esc($task['description']) ?></p>
                <?php if (!empty($task['link'])): ?>
                    <a href="<?= esc($task['link']) ?>" class="link">View</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($tasks)): ?>
            <div class="card"><p>No tasks at the moment.</p></div>
        <?php endif; ?>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Patient Flow Overview</h2>
        <p>Current patient flow and appointment status</p>
    </header>
    <div class="stack">
        <div class="card">
            <div class="row between">
                <div>
                    <h3>Today's Appointments</h3>
                    <p><?= number_format($metrics['appointmentsToday']) ?> appointments scheduled</p>
                </div>
                <div class="row">
                    <span class="badge success">Active</span>
                </div>
            </div>
            <div class="status-list">
                <?php foreach (['confirmed' => 'ok', 'pending' => 'warn', 'cancelled' => 'error'] as $status => $class): ?>
                    <div class="status-row">
                        <span><span class="dot <?= $class ?>"></span><?= ucfirst($status) ?></span>
                        <span><?= number_format($appointmentsByStatus[$status] ?? 0) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Upcoming Appointments</h2>
        <p>Next patients arriving</p>
    </header>
    <div class="stack">
        <?php if (!empty($upcomingAppointments)): ?>
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
                        <?php foreach ($upcomingAppointments as $apt): ?>
                            <tr>
                                <td><?= esc($apt['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($apt['doctor_name'] ?? 'N/A') ?></td>
                                <td><?= !empty($apt['appointment_date']) ? date('M d, Y', strtotime($apt['appointment_date'])) : '—' ?></td>
                                <td><?= !empty($apt['appointment_time']) ? date('g:i A', strtotime($apt['appointment_time'])) : '—' ?></td>
                                <td><span class="badge <?= ($apt['status'] ?? '') === 'pending' ? 'badge-warning' : 'badge-info' ?>"><?= strtoupper($apt['status'] ?? 'scheduled') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card"><p>No upcoming appointments.</p></div>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>
