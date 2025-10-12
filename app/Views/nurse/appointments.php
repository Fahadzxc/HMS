<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Patient Appointments</h2>
        <p>View and manage patient appointments assigned to you</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">My Appointments Today</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Confirmed</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-negative">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">This Week</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Patient List & Appointments</h2>
        <div class="row between">
            <input type="text" placeholder="Search patients..." class="search-input">
            <a href="#" class="btn-primary" onclick="showAddAppointmentModal()">+ Add Appointment</a>
        </div>
    </header>
    
    <div class="stack">
        <!-- Empty state until appointments are created -->
        <div class="card">
            <p>No appointments yet. Click "+ Add Appointment" to schedule a patient appointment.</p>
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
        <form id="addAppointmentForm" class="modal-body" action="<?= base_url('nurse/addAppointment') ?>" method="post">
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
                        <option value="D001">Dr. Maria Santos - General Medicine</option>
                        <option value="D002">Dr. John Cruz - Cardiology</option>
                        <option value="D003">Dr. Ana Reyes - Pediatrics</option>
                        <option value="D004">Dr. Carlos Lopez - Orthopedics</option>
                        <option value="D005">Dr. Lisa Garcia - Emergency Medicine</option>
                        <option value="D006">Dr. Michael Torres - Surgery</option>
                    </select>
                    <div class="error" data-error-for="doctor_id"></div>
                </div>
                <div class="form-field">
                    <label>Appointment Type <span class="req">*</span></label>
                    <select name="appointment_type" required>
                        <option value="">Select Type</option>
                        <option value="consultation">Consultation</option>
                        <option value="follow_up">Follow-up</option>
                        <option value="emergency">Emergency</option>
                        <option value="routine_checkup">Routine Checkup</option>
                        <option value="vaccination">Vaccination</option>
                        <option value="lab_test">Lab Test</option>
                        <option value="xray">X-Ray</option>
                        <option value="surgery">Surgery</option>
                    </select>
                    <div class="error" data-error-for="appointment_type"></div>
                </div>
                <div class="form-field">
                    <label>Appointment Date <span class="req">*</span></label>
                    <input type="date" name="appointment_date" required>
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
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
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

<!-- Update Appointment Modal -->
<div id="updateAppointmentModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="updateAppointmentModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="updateAppointmentTitle">
        <header class="panel-header modal-header">
            <h2 id="updateAppointmentTitle">Update Appointment</h2>
            <button type="button" class="close" onclick="closeUpdateAppointmentModal()">&times;</button>
        </header>
        <form id="updateAppointmentForm" class="modal-body" action="<?= base_url('nurse/updateAppointment') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="appointment_id" id="update_appointment_id">
            <div class="form-grid">
                <div class="form-field">
                    <label>Patient ID <span class="req">*</span></label>
                    <input type="text" name="patient_id" id="update_patient_id" required>
                    <div class="error" data-error-for="patient_id"></div>
                </div>
                <div class="form-field">
                    <label>Patient Name <span class="req">*</span></label>
                    <input type="text" name="patient_name" id="update_patient_name" required>
                    <div class="error" data-error-for="patient_name"></div>
                </div>
                <div class="form-field">
                    <label>Contact Number <span class="req">*</span></label>
                    <input type="tel" name="contact" id="update_contact" required>
                    <div class="error" data-error-for="contact"></div>
                </div>
                <div class="form-field">
                    <label>Select Doctor <span class="req">*</span></label>
                    <select name="doctor_id" id="update_doctor_id" required>
                        <option value="">Choose a doctor...</option>
                        <option value="D001">Dr. Maria Santos - General Medicine</option>
                        <option value="D002">Dr. John Cruz - Cardiology</option>
                        <option value="D003">Dr. Ana Reyes - Pediatrics</option>
                        <option value="D004">Dr. Carlos Lopez - Orthopedics</option>
                        <option value="D005">Dr. Lisa Garcia - Emergency Medicine</option>
                        <option value="D006">Dr. Michael Torres - Surgery</option>
                    </select>
                    <div class="error" data-error-for="doctor_id"></div>
                </div>
                <div class="form-field">
                    <label>Appointment Type <span class="req">*</span></label>
                    <select name="appointment_type" id="update_appointment_type" required>
                        <option value="">Select Type</option>
                        <option value="consultation">Consultation</option>
                        <option value="follow_up">Follow-up</option>
                        <option value="emergency">Emergency</option>
                        <option value="routine_checkup">Routine Checkup</option>
                        <option value="vaccination">Vaccination</option>
                        <option value="lab_test">Lab Test</option>
                        <option value="xray">X-Ray</option>
                        <option value="surgery">Surgery</option>
                    </select>
                    <div class="error" data-error-for="appointment_type"></div>
                </div>
                <div class="form-field">
                    <label>Appointment Date <span class="req">*</span></label>
                    <input type="date" name="appointment_date" id="update_appointment_date" required>
                    <div class="error" data-error-for="appointment_date"></div>
                </div>
                <div class="form-field">
                    <label>Appointment Time <span class="req">*</span></label>
                    <input type="time" name="appointment_time" id="update_appointment_time" required>
                    <div class="error" data-error-for="appointment_time"></div>
                </div>
                <div class="form-field">
                    <label>Status <span class="req">*</span></label>
                    <select name="status" id="update_status" required>
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <div class="error" data-error-for="status"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Notes</label>
                    <textarea name="notes" id="update_notes" rows="3" placeholder="Additional notes or instructions..."></textarea>
                    <div class="error" data-error-for="notes"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeUpdateAppointmentModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Appointment</button>
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

// Update Appointment Modal
function showUpdateAppointmentModal(patientId) {
    const modal = document.getElementById('updateAppointmentModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    
    // Pre-fill form with patient data (you can fetch this from database)
    document.getElementById('update_appointment_id').value = patientId;
    document.getElementById('update_patient_id').value = patientId;
    // Add more pre-filling logic here
}

function closeUpdateAppointmentModal() {
    const modal = document.getElementById('updateAppointmentModal');
    const form = document.getElementById('updateAppointmentForm');
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

// View Patient Details
function viewPatientDetails(patientId) {
    alert('Viewing patient details for: ' + patientId);
    // Implement patient details view
}

// Update Appointment
function updateAppointment(patientId) {
    showUpdateAppointmentModal(patientId);
}

// Add backdrop click handlers
document.addEventListener('DOMContentLoaded', function() {
    // Add Appointment Modal
    const addAppointmentBackdrop = document.getElementById('addAppointmentModalBackdrop');
    if (addAppointmentBackdrop) {
        addAppointmentBackdrop.addEventListener('click', closeAddAppointmentModal);
    }
    
    // Update Appointment Modal
    const updateAppointmentBackdrop = document.getElementById('updateAppointmentModalBackdrop');
    if (updateAppointmentBackdrop) {
        updateAppointmentBackdrop.addEventListener('click', closeUpdateAppointmentModal);
    }
});
</script>

<?= $this->endSection() ?>
