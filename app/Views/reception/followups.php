<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Follow-ups Management</h2>
        <p>Manage follow-up appointments and check-ins</p>
    </header>
    <div class="stack">
        <?php
        $followupsList = isset($followups) && is_array($followups) ? $followups : [];
        $todayFollowups = count($followupsList);
        $pendingCheckIns = 0;
        $today = date('Y-m-d');
        
        foreach ($followupsList as $apt) {
            if ($apt['status'] === 'scheduled') {
                $pendingCheckIns++;
            }
        }
        ?>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Today's Follow-ups</div>
                    <div class="kpi-value"><?= $todayFollowups ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending Check-ins</div>
                    <div class="kpi-value"><?= $pendingCheckIns ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Today's Follow-ups</h2>
        <div class="row between">
            <input type="text" placeholder="Search follow-ups..." class="search-input">
        </div>
    </header>
    
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>Follow-up appointments for <?= date('F j, Y') ?></span>
                <span><?= count($followupsList) ?> follow-ups</span>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($followupsList)): ?>
                        <?php foreach ($followupsList as $followup): ?>
                            <tr>
                                <td><?= date('g:i A', strtotime($followup['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($followup['patient_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($followup['doctor_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $followup['status'] === 'confirmed' ? 'success' : 
                                        ($followup['status'] === 'scheduled' ? 'warning' : 
                                        ($followup['status'] === 'completed' ? 'info' : 'secondary'))
                                    ?>">
                                        <?= strtoupper($followup['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No follow-up appointments scheduled for today</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Upcoming Follow-ups Section -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Upcoming Follow-ups</h2>
        <p>All scheduled follow-up appointments</p>
    </header>
    
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>All upcoming follow-up appointments</span>
                <span><?= count($upcoming_followups ?? []) ?> follow-ups</span>
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
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($upcoming_followups)): ?>
                        <?php foreach ($upcoming_followups as $followup): ?>
                            <tr>
                                <td><?= $followup['id'] ?></td>
                                <td><?= date('M j, Y', strtotime($followup['appointment_date'])) ?></td>
                                <td><?= date('g:i A', strtotime($followup['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($followup['patient_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($followup['doctor_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $followup['status'] === 'confirmed' ? 'success' : 
                                        ($followup['status'] === 'scheduled' ? 'warning' : 
                                        ($followup['status'] === 'completed' ? 'info' : 'secondary'))
                                    ?>">
                                        <?= strtoupper($followup['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No upcoming follow-up appointments</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
function checkIn(appointmentId) {
    if (confirm('Check in this follow-up appointment?')) {
        // Implement check-in functionality
        alert('Check-in functionality - ID: ' + appointmentId);
    }
}

function editFollowup(appointmentId) {
    // Implement edit functionality
    alert('Edit follow-up functionality - ID: ' + appointmentId);
}
</script>

<?= $this->endSection() ?>
