<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>My Appointments</h2>
        <p>View and manage your patient appointments</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Today's Appointments</div>
                    <div class="kpi-value"><?= count($appointments ?? []) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Confirmed</div>
                    <div class="kpi-value"><?= count(array_filter($upcoming_appointments ?? [], function($apt) { return $apt['status'] === 'confirmed'; })) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending</div>
                    <div class="kpi-value"><?= count(array_filter($upcoming_appointments ?? [], function($apt) { return $apt['status'] === 'scheduled'; })) ?></div>
                    <div class="kpi-change kpi-negative">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">This Week</div>
                    <div class="kpi-value"><?= count($upcoming_appointments ?? []) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Today's Appointments</h2>
        <p>Patient appointments scheduled for today</p>
    </header>
    
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>Patient appointments for <?= date('F j, Y') ?></span>
                <span><?= count($appointments ?? []) ?> appointments</span>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($appointment['patient_name'] ?? 'N/A') ?></td>
                                <td><?= ucfirst($appointment['appointment_type']) ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $appointment['status'] === 'confirmed' ? 'success' : 
                                        ($appointment['status'] === 'scheduled' ? 'warning' : 
                                        ($appointment['status'] === 'completed' ? 'info' : 'secondary'))
                                    ?>">
                                        <?= strtoupper($appointment['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($appointment['notes'] ?? '-') ?></td>
                                <td>
                                    <?php if ($appointment['status'] === 'confirmed'): ?>
                                        <button class="btn-xs btn-success" onclick="completeAppointment(<?= $appointment['id'] ?>)">
                                            Complete
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-xs btn-primary" onclick="viewPatient(<?= $appointment['patient_id'] ?>)">
                                            View Patient
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No appointments scheduled for today</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Upcoming Appointments Section -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Upcoming Appointments</h2>
        <p>All your scheduled appointments</p>
    </header>
    
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>All upcoming appointments</span>
                <span><?= count($upcoming_appointments ?? []) ?> appointments</span>
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
                        <th>Type</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($upcoming_appointments)): ?>
                        <?php foreach ($upcoming_appointments as $appointment): ?>
                            <tr>
                                <td><?= $appointment['id'] ?></td>
                                <td><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></td>
                                <td><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($appointment['patient_name'] ?? 'N/A') ?></td>
                                <td><?= ucfirst($appointment['appointment_type']) ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $appointment['status'] === 'confirmed' ? 'success' : 
                                        ($appointment['status'] === 'scheduled' ? 'warning' : 
                                        ($appointment['status'] === 'completed' ? 'info' : 'secondary'))
                                    ?>">
                                        <?= strtoupper($appointment['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($appointment['notes'] ?? '-') ?></td>
                                <td>
                                    <button class="btn-xs btn-primary" onclick="viewPatient(<?= $appointment['patient_id'] ?>)">
                                        View Patient
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No upcoming appointments</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    margin-right: 0.25rem;
}

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

<script>
function completeAppointment(id) {
    if (confirm('Mark this appointment as completed?')) {
        alert('Complete appointment functionality - ID: ' + id);
        // Implement complete functionality
    }
}

function viewPatient(patientId) {
    alert('View patient details - Patient ID: ' + patientId);
    // Implement view patient functionality
}
</script>

<?= $this->endSection() ?>
