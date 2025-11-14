<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Today's Appointments</h2>
        <p>Patient appointments for <?= date('F j, Y') ?> • <?= count($appointments ?? []) ?> appointments</p>
    </header>
    
    <div class="stack">

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
        <p>All upcoming appointments • <?= count($upcoming_appointments ?? []) ?> appointments</p>
    </header>
    
    <div class="stack">

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

<!-- CSS moved to template.php -->

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
