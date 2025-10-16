<section class="panel">
    <header class="panel-header">
        <h2>Nurse Dashboard</h2>
        <p>Quick overview of today's assigned tasks and patients</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Assigned Patients</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending Medications</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Vital Checks Due</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Discharges Today</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="page-grid">
    <section class="panel">
        <header class="panel-header">
            <h2>Today's Tasks</h2>
            <p>Your assigned tasks for today</p>
        </header>
        <div class="stack">
            <div class="card">
                <p>No tasks assigned yet. Check back later or contact your supervisor.</p>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel-header">
            <h2>Quick Actions</h2>
        </header>
        <div class="actions-grid">
            <a class="action-tile" href="#" onclick="showVitalSignsModal()">
                <span class="icon icon-vitals"></span>
                <span>Update Vitals</span>
            </a>
            <a class="action-tile" href="#" onclick="showTreatmentModal()">
                <span class="icon icon-treatment"></span>
                <span>Update Treatment</span>
            </a>
            <a class="action-tile" href="#" onclick="showScheduleModal()">
                <span class="icon icon-schedule"></span>
                <span>My Schedule</span>
            </a>
            <a class="action-tile" href="#" onclick="showAssignPatientModal()">
                <span class="icon icon-patients"></span>
                <span>Assign Patient</span>
            </a>
        </div>
    </section>
</div>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Patients Under Care</h2>
        <p>Patients currently assigned to you</p>
    </header>
    <div class="stack">
        <div class="card">
            <p>No patients assigned yet. Patients will appear here once assigned by the doctor or administrator.</p>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>My Schedule</h2>
        <p>Your current work schedule</p>
    </header>
    <div class="stack">
        <div class="card">
            <p>No schedule assigned yet. Contact your supervisor to set up your work schedule.</p>
        </div>
    </div>
</section>

<!-- Vital Signs Modal -->
<div id="vitalSignsModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="vitalModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="vitalSignsTitle">
        <header class="panel-header modal-header">
            <h2 id="vitalSignsTitle">Update Vital Signs</h2>
            <button type="button" class="close" onclick="closeVitalSignsModal()">&times;</button>
        </header>
        <form id="vitalSignsForm" class="modal-body" action="<?= base_url('nurse/updateVitals') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Patient ID <span class="req">*</span></label>
                    <input type="text" name="patient_id" required>
                    <div class="error" data-error-for="patient_id"></div>
                </div>
                <div class="form-field">
                    <label>Blood Pressure</label>
                    <input type="text" name="blood_pressure" placeholder="120/80">
                    <div class="error" data-error-for="blood_pressure"></div>
                </div>
                <div class="form-field">
                    <label>Heart Rate (BPM)</label>
                    <input type="number" name="heart_rate" placeholder="72">
                    <div class="error" data-error-for="heart_rate"></div>
                </div>
                <div class="form-field">
                    <label>Temperature (°C)</label>
                    <input type="number" step="0.1" name="temperature" placeholder="36.5">
                    <div class="error" data-error-for="temperature"></div>
                </div>
                <div class="form-field">
                    <label>Respiratory Rate</label>
                    <input type="number" name="respiratory_rate" placeholder="16">
                    <div class="error" data-error-for="respiratory_rate"></div>
                </div>
                <div class="form-field">
                    <label>Oxygen Saturation (%)</label>
                    <input type="number" name="oxygen_saturation" placeholder="98">
                    <div class="error" data-error-for="oxygen_saturation"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeVitalSignsModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Vitals</button>
            </footer>
        </form>
    </div>
</div>

<!-- Treatment Update Modal -->
<div id="treatmentModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="treatmentModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="treatmentTitle">
        <header class="panel-header modal-header">
            <h2 id="treatmentTitle">Update Treatment</h2>
            <button type="button" class="close" onclick="closeTreatmentModal()">&times;</button>
        </header>
        <form id="treatmentForm" class="modal-body" action="<?= base_url('nurse/updateTreatment') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Patient ID <span class="req">*</span></label>
                    <input type="text" name="patient_id" required>
                    <div class="error" data-error-for="patient_id"></div>
                </div>
                <div class="form-field">
                    <label>Medication <span class="req">*</span></label>
                    <input type="text" name="medication" required>
                    <div class="error" data-error-for="medication"></div>
                </div>
                <div class="form-field">
                    <label>Dosage <span class="req">*</span></label>
                    <input type="text" name="dosage" required>
                    <div class="error" data-error-for="dosage"></div>
                </div>
                <div class="form-field">
                    <label>Frequency</label>
                    <input type="text" name="frequency" placeholder="Every 8 hours">
                    <div class="error" data-error-for="frequency"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Notes</label>
                    <textarea name="notes" rows="3"></textarea>
                    <div class="error" data-error-for="notes"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeTreatmentModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Treatment</button>
            </footer>
        </form>
    </div>
</div>

<!-- Schedule Modal -->
<div id="scheduleModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="scheduleModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="scheduleTitle">
        <header class="panel-header modal-header">
            <h2 id="scheduleTitle">My Schedule</h2>
            <button type="button" class="close" onclick="closeScheduleModal()">&times;</button>
        </header>
        <form id="scheduleForm" class="modal-body" action="<?= base_url('nurse/updateSchedule') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Date <span class="req">*</span></label>
                    <input type="date" name="date" required>
                    <div class="error" data-error-for="date"></div>
                </div>
                <div class="form-field">
                    <label>Shift Start <span class="req">*</span></label>
                    <input type="time" name="shift_start" required>
                    <div class="error" data-error-for="shift_start"></div>
                </div>
                <div class="form-field">
                    <label>Shift End <span class="req">*</span></label>
                    <input type="time" name="shift_end" required>
                    <div class="error" data-error-for="shift_end"></div>
                </div>
                <div class="form-field">
                    <label>Department <span class="req">*</span></label>
                    <select name="department" required>
                        <option value="">Select Department</option>
                        <option value="emergency">Emergency</option>
                        <option value="icu">ICU</option>
                        <option value="surgery">Surgery</option>
                        <option value="pediatrics">Pediatrics</option>
                        <option value="cardiology">Cardiology</option>
                        <option value="orthopedics">Orthopedics</option>
                    </select>
                    <div class="error" data-error-for="department"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Notes</label>
                    <textarea name="notes" rows="3"></textarea>
                    <div class="error" data-error-for="notes"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeScheduleModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Schedule</button>
            </footer>
        </form>
    </div>
</div>

<!-- Assign Patient Modal -->
<div id="assignPatientModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="assignPatientModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="assignPatientTitle">
        <header class="panel-header modal-header">
            <h2 id="assignPatientTitle">Assign Patient</h2>
            <button type="button" class="close" onclick="closeAssignPatientModal()">&times;</button>
        </header>
        <form id="assignPatientForm" class="modal-body" action="<?= base_url('nurse/assignPatient') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Patient ID <span class="req">*</span></label>
                    <input type="text" name="patient_id" required>
                    <div class="error" data-error-for="patient_id"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Assignment Notes</label>
                    <textarea name="assignment_notes" rows="3" placeholder="Reason for assignment, special instructions, etc."></textarea>
                    <div class="error" data-error-for="assignment_notes"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAssignPatientModal()">Cancel</button>
                <button type="submit" class="btn-primary">Assign Patient</button>
            </footer>
        </form>
    </div>
</div>

<script>
// Vital Signs Modal
function showVitalSignsModal() {
    const modal = document.getElementById('vitalSignsModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeVitalSignsModal() {
    const modal = document.getElementById('vitalSignsModal');
    const form = document.getElementById('vitalSignsForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Treatment Modal
function showTreatmentModal() {
    const modal = document.getElementById('treatmentModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeTreatmentModal() {
    const modal = document.getElementById('treatmentModal');
    const form = document.getElementById('treatmentForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Schedule Modal
function showScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Assign Patient Modal
function showAssignPatientModal() {
    const modal = document.getElementById('assignPatientModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeAssignPatientModal() {
    const modal = document.getElementById('assignPatientModal');
    const form = document.getElementById('assignPatientForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Add backdrop click handlers
document.addEventListener('DOMContentLoaded', function() {
    // Vital Signs Modal
    const vitalBackdrop = document.getElementById('vitalModalBackdrop');
    if (vitalBackdrop) {
        vitalBackdrop.addEventListener('click', closeVitalSignsModal);
    }
    
    // Treatment Modal
    const treatmentBackdrop = document.getElementById('treatmentModalBackdrop');
    if (treatmentBackdrop) {
        treatmentBackdrop.addEventListener('click', closeTreatmentModal);
    }
    
    // Schedule Modal
    const scheduleBackdrop = document.getElementById('scheduleModalBackdrop');
    if (scheduleBackdrop) {
        scheduleBackdrop.addEventListener('click', closeScheduleModal);
    }
    
    // Assign Patient Modal
    const assignBackdrop = document.getElementById('assignPatientModalBackdrop');
    if (assignBackdrop) {
        assignBackdrop.addEventListener('click', closeAssignPatientModal);
    }
});
</script>
