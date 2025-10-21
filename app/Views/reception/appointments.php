<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Appointments Management</h2>
        <p>Manage patient appointments and check-ins</p>
    </header>
    <div class="stack">
        <?php
        $appointmentsList = isset($appointments) && is_array($appointments) ? $appointments : [];
        $totalAppointments = count($appointmentsList);
        $todayAppointments = 0;
        $pendingCheckIns = 0;
        $today = date('Y-m-d');
        
        foreach ($appointmentsList as $apt) {
            if (!empty($apt['appointment_date']) && $apt['appointment_date'] === $today) {
                $todayAppointments++;
                if ($apt['status'] === 'scheduled') {
                    $pendingCheckIns++;
                }
            }
        }
        ?>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Today's Appointments</div>
                    <div class="kpi-value"><?= $todayAppointments ?></div>
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
        <h2>Today's Appointments</h2>
        <div class="row between">
            <input type="text" placeholder="Search appointments..." class="search-input">
            <a href="#" id="btnOpenAddAppointment" class="btn-primary">+ New Appointment</a>
        </div>
    </header>
    
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>Patient appointments for <?= date('F j, Y') ?></span>
                <span><?= count($appointmentsList) ?> appointments</span>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointmentsList)): ?>
                        <?php foreach ($appointmentsList as $appointment): ?>
                            <tr>
                                <td><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($appointment['patient_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($appointment['doctor_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-<?= $appointment['appointment_type'] === 'emergency' ? 'danger' : 'primary' ?>">
                                        <?= ucfirst($appointment['appointment_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $appointment['status'] === 'confirmed' ? 'success' : 
                                        ($appointment['status'] === 'scheduled' ? 'warning' : 'secondary') 
                                    ?>">
                                        <?= ucfirst($appointment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] === 'scheduled'): ?>
                                        <button class="btn-sm btn-success" onclick="checkInPatient(<?= $appointment['id'] ?>)">
                                            Check In
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">Checked In</span>
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
        <p>All scheduled appointments</p>
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
                        <th>Doctor</th>
                        <th>Type</th>
                        <th>Status</th>
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
                                <td><?= htmlspecialchars($appointment['doctor_name'] ?? 'N/A') ?></td>
                                <td><?= ucfirst($appointment['appointment_type']) ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $appointment['status'] === 'confirmed' ? 'success' : 
                                        ($appointment['status'] === 'scheduled' ? 'warning' : 'secondary') 
                                    ?>">
                                        <?= strtoupper($appointment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] === 'scheduled' && $appointment['appointment_date'] === date('Y-m-d')): ?>
                                        <button class="btn-xs btn-success" onclick="checkInPatient(<?= $appointment['id'] ?>)">
                                            Check In
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-xs btn-primary" onclick="editAppointment(<?= $appointment['id'] ?>)">
                                            Edit
                                        </button>
                                    <?php endif; ?>
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

<!-- Add Appointment Modal -->
<div id="addAppointmentModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="addAppointmentModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="addAppointmentTitle">
        <header class="panel-header modal-header">
            <h2 id="addAppointmentTitle">Add New Appointment</h2>
            <button type="button" class="close" onclick="closeAddAppointmentModal()">&times;</button>
        </header>
        <form id="addAppointmentForm" class="modal-body" action="<?= base_url('reception/createAppointment') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Select Patient <span class="req">*</span></label>
                    <select name="patient_id" id="patient_select" required onchange="loadPatientDetails()">
                        <option value="">Choose a patient...</option>
                        <?php if (isset($patients) && is_array($patients)): ?>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['id'] ?>" 
                                        data-name="<?= htmlspecialchars($patient['full_name']) ?>" 
                                        data-contact="<?= htmlspecialchars($patient['contact']) ?>">
                                    <?= $patient['id'] ?> - <?= htmlspecialchars($patient['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="error" data-error-for="patient_id"></div>
                </div>
                <div class="form-field">
                    <label>Patient Name</label>
                    <input type="text" name="patient_name" id="patient_name" readonly>
                    <div class="error" data-error-for="patient_name"></div>
                </div>
                <div class="form-field">
                    <label>Contact Number</label>
                    <input type="tel" name="contact" id="patient_contact" readonly>
                    <div class="error" data-error-for="contact"></div>
                </div>
                <div class="form-field">
                    <label>Select Doctor <span class="req">*</span></label>
                    <select name="doctor_id" required>
                        <option value="">Choose a doctor...</option>
                        <?php if (isset($doctors) && is_array($doctors)): ?>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>"><?= htmlspecialchars($doctor['name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="error" data-error-for="doctor_id"></div>
                </div>
                <div class="form-field">
                    <label>Appointment Type <span class="req">*</span></label>
                    <select name="appointment_type" required>
                        <option value="">Select Type</option>
                        <option value="consultation">Consultation</option>
                        <option value="follow-up">Follow-up</option>
                        <option value="emergency">Emergency</option>
                        <option value="routine">Routine Checkup</option>
                        <option value="vaccination">Vaccination</option>
                        <option value="lab_test">Lab Test</option>
                        <option value="xray">X-Ray</option>
                    </select>
                    <div class="error" data-error-for="appointment_type"></div>
                </div>
                <div class="form-field">
                    <label>Appointment Date <span class="req">*</span></label>
                    <input type="date" name="appointment_date" required min="<?= date('Y-m-d') ?>">
                    <div class="error" data-error-for="appointment_date"></div>
                </div>
                <div class="form-field">
                    <label>Appointment Time <span class="req">*</span></label>
                    <input type="time" name="appointment_time" required>
                    <div class="error" data-error-for="appointment_time"></div>
                </div>
                <div class="form-field">
                    <label>Status <span class="req">*</span></label>
                    <select name="status" required>
                        <option value="scheduled">Scheduled</option>
                        <option value="confirmed">Confirmed</option>
                    </select>
                    <div class="error" data-error-for="status"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" placeholder="Additional notes or instructions..."></textarea>
                    <div class="error" data-error-for="notes"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAddAppointmentModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Appointment</button>
            </footer>
        </form>
    </div>
</div>

<script>
// Add Appointment Modal
function showAddAppointmentModal() {
    const modal = document.getElementById('addAppointmentModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeAddAppointmentModal() {
    const modal = document.getElementById('addAppointmentModal');
    const form = document.getElementById('addAppointmentForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Load Patient Details when patient is selected
function loadPatientDetails() {
    const select = document.getElementById('patient_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        document.getElementById('patient_name').value = selectedOption.getAttribute('data-name');
        document.getElementById('patient_contact').value = selectedOption.getAttribute('data-contact');
    } else {
        document.getElementById('patient_name').value = '';
        document.getElementById('patient_contact').value = '';
    }
}

// Event listeners
document.getElementById('btnOpenAddAppointment').addEventListener('click', function(e) {
    e.preventDefault();
    showAddAppointmentModal();
});

// Add appointment form submission
document.getElementById('addAppointmentForm').addEventListener('submit', function(e) {
    // Let the form submit normally for now to debug
    console.log('Form is being submitted...');
    
    // Validate required fields
    const patientId = document.querySelector('select[name="patient_id"]').value;
    const doctorId = document.querySelector('select[name="doctor_id"]').value;
    const appointmentDate = document.querySelector('input[name="appointment_date"]').value;
    const appointmentTime = document.querySelector('input[name="appointment_time"]').value;
    const appointmentType = document.querySelector('select[name="appointment_type"]').value;
    
    if (!patientId || !doctorId || !appointmentDate || !appointmentTime || !appointmentType) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    console.log('All fields filled, submitting form...');
    // Form will submit normally
});

// Check in patient function
function checkInPatient(appointmentId) {
    if (confirm('Check in this patient?')) {
        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        
        fetch('<?= base_url('reception/checkInPatient') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Patient checked in successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to check in patient'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while checking in the patient');
        });
    }
}

// Add backdrop click handlers
document.addEventListener('DOMContentLoaded', function() {
    // Add Appointment Modal
    const addAppointmentBackdrop = document.getElementById('addAppointmentModalBackdrop');
    if (addAppointmentBackdrop) {
        addAppointmentBackdrop.addEventListener('click', closeAddAppointmentModal);
    }
});

// Edit appointment function
function editAppointment(id) {
    alert('Edit appointment functionality - ID: ' + id);
    // Implement edit functionality
}
</script>

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

<?= $this->endSection() ?>
