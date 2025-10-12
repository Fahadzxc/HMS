<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Task Management</h2>
        <p>Manage your assigned tasks and responsibilities</p>
    </header>
    <div class="stack">
        <div class="row between">
            <input type="text" placeholder="Search tasks..." class="search-input">
            <a href="#" class="btn-primary" onclick="showNewTaskModal()">+ Add Task</a>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Today's Tasks</h2>
        <p>Your assigned tasks for today</p>
    </header>
    <div class="stack">
        <div class="card">
            <p>No tasks assigned yet. Tasks will appear here once assigned by your supervisor or doctor.</p>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Task Categories</h2>
        <p>Common nursing tasks and responsibilities</p>
    </header>
    <div class="stack">
        <div class="actions-grid">
            <a class="action-tile" href="#" onclick="showVitalSignsModal()">
                <span class="icon icon-vitals"></span>
                <span>Vital Signs</span>
            </a>
            <a class="action-tile" href="#" onclick="showTreatmentModal()">
                <span class="icon icon-treatment"></span>
                <span>Medication</span>
            </a>
            <a class="action-tile" href="#" onclick="showPatientCareModal()">
                <span class="icon icon-patients"></span>
                <span>Patient Care</span>
            </a>
            <a class="action-tile" href="#" onclick="showDocumentationModal()">
                <span class="icon icon-document"></span>
                <span>Documentation</span>
            </a>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Completed Tasks</h2>
        <p>Tasks you have completed today</p>
    </header>
    <div class="stack">
        <div class="card">
            <p>No completed tasks yet.</p>
        </div>
    </div>
</section>

<!-- New Task Modal -->
<div id="newTaskModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="taskModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="newTaskTitle">
        <header class="panel-header modal-header">
            <h2 id="newTaskTitle">Add New Task</h2>
            <button type="button" class="close" onclick="closeNewTaskModal()">&times;</button>
        </header>
        <form id="newTaskForm" class="modal-body" action="<?= base_url('nurse/addTask') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Task Title <span class="req">*</span></label>
                    <input type="text" name="task_title" required>
                    <div class="error" data-error-for="task_title"></div>
                </div>
                <div class="form-field">
                    <label>Priority <span class="req">*</span></label>
                    <select name="priority" required>
                        <option value="">Select Priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <div class="error" data-error-for="priority"></div>
                </div>
                <div class="form-field">
                    <label>Due Time</label>
                    <input type="time" name="due_time">
                    <div class="error" data-error-for="due_time"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Description <span class="req">*</span></label>
                    <textarea name="task_description" rows="3" required></textarea>
                    <div class="error" data-error-for="task_description"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeNewTaskModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Task</button>
            </footer>
        </form>
    </div>
</div>

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

<!-- Patient Care Modal -->
<div id="patientCareModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="patientCareModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="patientCareTitle">
        <header class="panel-header modal-header">
            <h2 id="patientCareTitle">Patient Care Task</h2>
            <button type="button" class="close" onclick="closePatientCareModal()">&times;</button>
        </header>
        <form id="patientCareForm" class="modal-body" action="<?= base_url('nurse/patientCare') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Patient ID <span class="req">*</span></label>
                    <input type="text" name="patient_id" required>
                    <div class="error" data-error-for="patient_id"></div>
                </div>
                <div class="form-field">
                    <label>Care Type <span class="req">*</span></label>
                    <select name="care_type" required>
                        <option value="">Select Care Type</option>
                        <option value="bathing">Bathing</option>
                        <option value="feeding">Feeding</option>
                        <option value="mobility">Mobility Assistance</option>
                        <option value="wound_care">Wound Care</option>
                        <option value="monitoring">Patient Monitoring</option>
                    </select>
                    <div class="error" data-error-for="care_type"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Care Notes <span class="req">*</span></label>
                    <textarea name="care_notes" rows="3" required></textarea>
                    <div class="error" data-error-for="care_notes"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closePatientCareModal()">Cancel</button>
                <button type="submit" class="btn-primary">Record Care</button>
            </footer>
        </form>
    </div>
</div>

<!-- Documentation Modal -->
<div id="documentationModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="documentationModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="documentationTitle">
        <header class="panel-header modal-header">
            <h2 id="documentationTitle">Documentation</h2>
            <button type="button" class="close" onclick="closeDocumentationModal()">&times;</button>
        </header>
        <form id="documentationForm" class="modal-body" action="<?= base_url('nurse/documentation') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Patient ID <span class="req">*</span></label>
                    <input type="text" name="patient_id" required>
                    <div class="error" data-error-for="patient_id"></div>
                </div>
                <div class="form-field">
                    <label>Document Type <span class="req">*</span></label>
                    <select name="document_type" required>
                        <option value="">Select Document Type</option>
                        <option value="nursing_notes">Nursing Notes</option>
                        <option value="medication_record">Medication Record</option>
                        <option value="vital_signs">Vital Signs Record</option>
                        <option value="incident_report">Incident Report</option>
                    </select>
                    <div class="error" data-error-for="document_type"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Content <span class="req">*</span></label>
                    <textarea name="document_content" rows="4" required></textarea>
                    <div class="error" data-error-for="document_content"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeDocumentationModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Document</button>
            </footer>
        </form>
    </div>
</div>

<script>
(function(){
    // New Task Modal
    const openTaskBtn = document.querySelector('[onclick="showNewTaskModal()"]');
    const taskModal = document.getElementById('newTaskModal');
    const taskCancelBtn = document.querySelector('[onclick="closeNewTaskModal()"]');
    const taskBackdrop = document.getElementById('taskModalBackdrop');
    const taskForm = document.getElementById('newTaskForm');

    function openTaskModal(){ 
        taskModal.style.display='block'; 
        taskModal.setAttribute('aria-hidden','false'); 
    }
    function closeTaskModal(){ 
        taskModal.style.display='none'; 
        taskModal.setAttribute('aria-hidden','true'); 
        clearTaskErrors(); 
        taskForm.reset(); 
    }
    function setTaskError(name, msg){ 
        const el = taskModal.querySelector('[data-error-for="'+name+'"]'); 
        if(el){ el.textContent = msg || ''; } 
    }
    function clearTaskErrors(){ 
        taskModal.querySelectorAll('.error').forEach(e=>e.textContent=''); 
    }

    if(openTaskBtn) openTaskBtn.addEventListener('click', function(e){ e.preventDefault(); openTaskModal(); });
    if(taskCancelBtn) taskCancelBtn.addEventListener('click', function(){ closeTaskModal(); });
    if(taskBackdrop) taskBackdrop.addEventListener('click', function(){ closeTaskModal(); });

    // Global functions for onclick handlers
    window.showNewTaskModal = openTaskModal;
    window.closeNewTaskModal = closeTaskModal;
})();

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

// Patient Care Modal
function showPatientCareModal() {
    const modal = document.getElementById('patientCareModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closePatientCareModal() {
    const modal = document.getElementById('patientCareModal');
    const form = document.getElementById('patientCareForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Documentation Modal
function showDocumentationModal() {
    const modal = document.getElementById('documentationModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeDocumentationModal() {
    const modal = document.getElementById('documentationModal');
    const form = document.getElementById('documentationForm');
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
    
    // Patient Care Modal
    const patientCareBackdrop = document.getElementById('patientCareModalBackdrop');
    if (patientCareBackdrop) {
        patientCareBackdrop.addEventListener('click', closePatientCareModal);
    }
    
    // Documentation Modal
    const documentationBackdrop = document.getElementById('documentationModalBackdrop');
    if (documentationBackdrop) {
        documentationBackdrop.addEventListener('click', closeDocumentationModal);
    }
});
</script>

<?= $this->endSection() ?>