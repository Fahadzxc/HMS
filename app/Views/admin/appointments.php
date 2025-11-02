<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Appointments Monitor</h2>
        <p>View current appointments across the facility</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Today's Appointments</div>
                    <div class="kpi-value"><?= count(array_filter($appointments ?? [], function($apt) { return $apt['appointment_date'] === date('Y-m-d'); })) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Confirmed</div>
                    <div class="kpi-value"><?= count(array_filter($appointments ?? [], function($apt) { return $apt['status'] === 'confirmed'; })) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending</div>
                    <div class="kpi-value"><?= count(array_filter($appointments ?? [], function($apt) { return $apt['status'] === 'scheduled'; })) ?></div>
                    <div class="kpi-change kpi-negative">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">This Week</div>
                    <div class="kpi-value"><?= count($appointments ?? []) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Appointment Schedule</h2>
        <div class="row between">
            <input type="text" placeholder="Search appointments..." class="search-input">
        </div>
    </header>
    
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>All appointments</span>
                <span><?= count($appointments ?? []) ?> total</span>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?= $appointment['id'] ?></td>
                                <td><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></td>
                                <td><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($appointment['patient_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($appointment['doctor_name'] ?? 'N/A') ?></td>
                                <td><?= ucfirst($appointment['appointment_type']) ?></td>
                                <td>
                                    <span class="badge badge<?= 
                                        $appointment['status'] === 'confirmed' ? '-success' : 
                                        ($appointment['status'] === 'scheduled' ? '-warning' : 
                                        ($appointment['status'] === 'completed' ? '-info' : '-secondary'))
                                    ?>">
                                        <?= ucfirst($appointment['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($appointment['notes'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No appointments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.data-table th,
.data-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.data-table th {
    background-color: #f8fafc;
    font-weight: 600;
    color: #2d3748;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success { background-color: #c6f6d5; color: #22543d; }
.badge-warning { background-color: #fef5e7; color: #744210; }
.badge-info { background-color: #bee3f8; color: #2a4365; }
.badge-secondary { background-color: #e2e8f0; color: #4a5568; }
</style>

<?= $this->endSection() ?>
