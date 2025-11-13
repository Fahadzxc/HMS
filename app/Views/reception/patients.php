<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Patient Registration</h2>
        <p>Register new patients and view patient records</p>
    </header>
    <div class="stack">
        <?php
        $patientsList = isset($patients) && is_array($patients) ? $patients : [];
        $totalPatients = count($patientsList);
        $today = date('Y-m-d');
        $todayCount = 0;
        foreach ($patientsList as $pp) {
            if (!empty($pp['created_at']) && substr($pp['created_at'], 0, 10) === $today) {
                $todayCount++;
            }
        }
        ?>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Patients</div>
                    <div class="kpi-value"><?= $totalPatients ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">New Patients Today</div>
                    <div class="kpi-value"><?= $todayCount ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Patient Records</h2>
        <div class="row between">
            <input type="text" placeholder="Search patients..." class="search-input">
            <a href="#" id="btnOpenAddPatient" class="btn-primary">+ Add Patient</a>
        </div>
    </header>
    
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <div class="col-id">Patient ID</div>
                <div class="col-name">Name</div>
                <div class="col-age">AGE/GENDER</div>
                <div class="col-contact">CONTACT</div>
                <div class="col-status">Status</div>
                <div class="col-actions">Actions</div>
            </div>
        </div>

        <?php if (!empty($patientsList)): ?>
            <?php foreach ($patientsList as $p): ?>
                <?php
                    $pid = 'P' . str_pad((string) $p['id'], 3, '0', STR_PAD_LEFT);
                    $age = '—';
                    if (!empty($p['date_of_birth']) && $p['date_of_birth'] !== '0000-00-00' && $p['date_of_birth'] !== '') {
                        try {
                            $dateStr = $p['date_of_birth'];
                            if (strpos($dateStr, '/') !== false) {
                                $parts = explode('/', $dateStr);
                                if (count($parts) === 3) {
                                    $dateStr = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                }
                            }
                            $birthDate = new DateTime($dateStr);
                            $today = new DateTime();
                            $ageDiff = $today->diff($birthDate);
                            $age = $ageDiff->y;
                        } catch (Exception $e) {
                            $age = '—';
                        }
                    }
                    $bloodType = !empty($p['blood_type']) ? $p['blood_type'] : 'O+';
                ?>
        <div class="card table-row">
            <div class="row between">
                <div class="col-id patient-id"><?= esc($pid) ?></div>
                <div class="col-name">
                    <div class="patient-info">
                        <div class="patient-avatar">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#3B82F6"/>
                                <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="#3B82F6"/>
                            </svg>
                        </div>
                        <div class="patient-details">
                            <strong><?= esc($p['full_name']) ?></strong>
                            <p class="blood-type">Blood: <?= esc($bloodType) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-age">
                    <div><?= esc($age) ?><?= (is_numeric($age) && $age > 0) ? ' years' : '' ?></div>
                    <div><?= esc($p['gender']) ?></div>
                </div>
                <div class="col-contact">
                    <p class="phone"><?= esc($p['contact']) ?></p>
                    <p class="email"><?= esc($p['email'] ?? 'patient@email.com') ?></p>
                </div>
                <div class="col-status">
                    <?php 
                    $statusClass = 'badge-green';
                    $statusText = 'Active';
                    if (isset($p['status'])) {
                        switch($p['status']) {
                            case 'discharged':
                                $statusClass = 'badge-gray';
                                $statusText = 'Discharged';
                                break;
                            case 'transferred':
                                $statusClass = 'badge-yellow';
                                $statusText = 'Transferred';
                                break;
                        }
                    }
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                    <?php if (isset($p['patient_type'])): ?>
                        <br><small class="text-muted"><?= ucfirst($p['patient_type']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-actions">
                    <a href="#" class="action-link" onclick="viewPatient(<?= $p['id'] ?>); return false;">View</a>
                </div>
            </div>
        </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div class="card table-row">
            <div class="row between">
                <div class="col-name">No patients found.</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Add Patient Modal -->
<div id="addPatientModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="addPatientTitle">
        <header class="panel-header modal-header">
            <h2 id="addPatientTitle">Add New Patient</h2>
            <button id="btnCloseModal" class="icon-button" aria-label="Close">×</button>
        </header>
        <form id="addPatientForm" class="modal-body" method="post" action="<?= site_url('reception/patients/store') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="full_name" required>
                    <div class="error" data-error-for="full_name"></div>
                </div>
                <div class="form-field">
                    <label>Date of Birth <span class="req">*</span></label>
                    <input type="text" name="date_of_birth" id="date_of_birth" placeholder="mm/dd/yyyy" maxlength="10" required>
                    <div class="error" data-error-for="date_of_birth"></div>
                </div>
                <div class="form-field">
                    <label>Gender <span class="req">*</span></label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                    <div class="error" data-error-for="gender"></div>
                </div>
                <div class="form-field">
                    <label>Blood Type</label>
                    <select name="blood_type">
                        <option value="">Select Blood Type</option>
                        <option>A+</option>
                        <option>A-</option>
                        <option>B+</option>
                        <option>B-</option>
                        <option>AB+</option>
                        <option>AB-</option>
                        <option>O+</option>
                        <option>O-</option>
                    </select>
                    <div class="error" data-error-for="blood_type"></div>
                </div>
                <div class="form-field">
                    <label>Contact Number <span class="req">*</span></label>
                    <input type="text" name="contact" placeholder="09XX XXX XXXX" required>
                    <small class="form-help">Philippine mobile number (09XX XXX XXXX)</small>
                    <div class="error" data-error-for="contact"></div>
                </div>
                <div class="form-field">
                    <label>Email Address</label>
                    <input type="email" name="email">
                    <div class="error" data-error-for="email"></div>
                </div>
                <div class="form-field">
                    <label>Address <span class="req">*</span></label>
                    <select name="address" required>
                        <option value="">Select Address</option>
                        <option>Lagao</option>
                        <option>Bula</option>
                        <option>San Isidro</option>
                        <option>Calumpang</option>
                        <option>Tambler</option>
                        <option>City Heights</option>
                    </select>
                    <div class="error" data-error-for="address"></div>
                </div>
                <div class="form-field">
                    <label>Patient Type <span class="req">*</span></label>
                    <select name="patient_type" required>
                        <option value="">Select Patient Type</option>
                        <option value="outpatient">Outpatient</option>
                        <option value="inpatient">Inpatient</option>
                    </select>
                    <div class="error" data-error-for="patient_type"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Medical Concern <span class="req">*</span></label>
                    <textarea name="concern" rows="4" required></textarea>
                    <div class="error" data-error-for="concern"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" id="btnCancel" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Patient</button>
            </footer>
        </form>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    (function(){
        const openBtn = document.getElementById('btnOpenAddPatient');
        const modal = document.getElementById('addPatientModal');
        const closeBtn = document.getElementById('btnCloseModal');
        const cancelBtn = document.getElementById('btnCancel');
        const backdrop = document.getElementById('modalBackdrop');
        const form = document.getElementById('addPatientForm');

        function open(){ modal.style.display='block'; modal.setAttribute('aria-hidden','false'); }
        function close(){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); clearErrors(); form.reset(); }
        function setError(name, msg){ const el = modal.querySelector('[data-error-for="'+name+'"]'); if(el){ el.textContent = msg || ''; } }
        function clearErrors(){ modal.querySelectorAll('.error').forEach(e=>e.textContent=''); }

        openBtn.addEventListener('click', function(e){ e.preventDefault(); open(); });
        closeBtn.addEventListener('click', function(){ close(); });
        cancelBtn.addEventListener('click', function(){ close(); });
        backdrop.addEventListener('click', function(){ close(); });
        document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ close(); }});

        form.addEventListener('submit', async function(e){
            e.preventDefault();
            clearErrors();
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            const formData = new FormData(form);

            try {
                const resp = await fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With':'XMLHttpRequest' } });
                if(resp.status === 422){
                    const data = await resp.json();
                    const errs = data.errors || {};
                    Object.keys(errs).forEach(k=> setError(k, errs[k]));
                    submitBtn.disabled = false;
                    return;
                }
                const data = await resp.json();
                if(data.status === 'success'){
                    close();
                    alert('Patient registered successfully');
                    location.reload();
                } else {
                    console.error('Server response:', data);
                    alert('Failed to register patient: ' + (data.message || 'Unknown error'));
                    submitBtn.disabled = false;
                }
            } catch (err){
                console.error(err);
                alert('Network error');
                submitBtn.disabled = false;
            }
        });

        // Date formatting
        const dateInput = document.getElementById('date_of_birth');
        if (dateInput) {
            dateInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 8) value = value.substring(0, 8);
                if (value.length >= 2) value = value.substring(0, 2) + '/' + value.substring(2);
                if (value.length >= 5) value = value.substring(0, 5) + '/' + value.substring(5);
                e.target.value = value;
            });
        }

        // Phone formatting
        const contactInput = document.querySelector('input[name="contact"]');
        if (contactInput) {
            contactInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.substring(0, 11);
                if (value.length >= 4) value = value.substring(0, 4) + ' ' + value.substring(4);
                if (value.length >= 8) value = value.substring(0, 8) + ' ' + value.substring(8);
                e.target.value = value;
            });
        }

        // Patient type handling - removed room selection logic
    })();
</script>

<!-- Patient View Modal -->
<div id="patientViewModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="patientViewModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="patientViewTitle" style="max-width: 800px; max-height: 90vh; display: flex; flex-direction: column;">
        <header class="panel-header modal-header">
            <h2 id="patientViewTitle">Patient Information</h2>
            <button type="button" class="close" onclick="closePatientViewModal()">&times;</button>
        </header>
        <div class="modal-body" id="patientViewContent" style="max-height: 70vh; overflow-y: auto;">
            <div style="text-align: center; padding: 2rem;">
                <p>Loading patient information...</p>
            </div>
        </div>
        <footer class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePatientViewModal()">Close</button>
        </footer>
    </div>
</div>

<script>
function viewPatient(patientId) {
    const modal = document.getElementById('patientViewModal');
    const content = document.getElementById('patientViewContent');
    const title = document.getElementById('patientViewTitle');
    
    // Show modal and loading state
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    content.innerHTML = '<div style="text-align: center; padding: 2rem;"><p>Loading patient information...</p></div>';
    
    // Fetch patient details
    fetch(`<?= site_url('reception/patients/show') ?>/${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.patient) {
                displayPatientInfo(data.patient, data.assigned_nurse, data.latest_vitals, data.vital_signs_history);
            } else {
                content.innerHTML = '<div style="text-align: center; padding: 2rem; color: #ef4444;"><p>Error: ' + (data.message || 'Failed to load patient information') + '</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div style="text-align: center; padding: 2rem; color: #ef4444;"><p>Error loading patient information</p></div>';
        });
}

function displayPatientInfo(patient, assignedNurse, latestVitals, vitalSignsHistory) {
    const content = document.getElementById('patientViewContent');
    const pid = 'P' + String(patient.id).padStart(3, '0');
    
    // Calculate age
    let age = '—';
    let dateOfBirth = '—';
    if (patient.date_of_birth && patient.date_of_birth !== '0000-00-00' && patient.date_of_birth !== '') {
        try {
            let dateStr = patient.date_of_birth;
            if (dateStr.includes('/')) {
                const parts = dateStr.split('/');
                if (parts.length === 3) {
                    dateStr = parts[2] + '-' + parts[0] + '-' + parts[1];
                }
            }
            const birthDate = new Date(dateStr);
            const today = new Date();
            const ageDiff = today - birthDate;
            const years = Math.floor(ageDiff / (365.25 * 24 * 60 * 60 * 1000));
            age = years > 0 ? years + ' years' : 'Less than 1 year';
            
            // Format date for display
            dateOfBirth = birthDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        } catch (e) {
            dateOfBirth = patient.date_of_birth;
        }
    }
    
    // Format registration date
    let registrationDate = '—';
    if (patient.created_at) {
        try {
            const regDate = new Date(patient.created_at);
            registrationDate = regDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            registrationDate = patient.created_at;
        }
    }
    
    // Format contact number
    let contactFormatted = patient.contact || '—';
    if (contactFormatted.length === 11 && contactFormatted.startsWith('09')) {
        contactFormatted = contactFormatted.substring(0, 4) + ' ' + contactFormatted.substring(4, 7) + ' ' + contactFormatted.substring(7);
    }
    
    const html = `
        <div class="patient-view-container">
            <div class="patient-view-header">
                <div class="patient-avatar-large">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#3B82F6"/>
                        <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="#3B82F6"/>
                    </svg>
                </div>
                <div class="patient-view-title">
                    <h3>${patient.full_name || '—'}</h3>
                    <p class="patient-id-display">Patient ID: ${pid}</p>
                </div>
            </div>
            
            <div class="patient-view-grid">
                <div class="patient-view-section">
                    <h4>Personal Information</h4>
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value">${patient.full_name || '—'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date of Birth:</span>
                        <span class="info-value">${dateOfBirth}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Age:</span>
                        <span class="info-value">${age}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gender:</span>
                        <span class="info-value">${patient.gender || '—'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Blood Type:</span>
                        <span class="info-value">${patient.blood_type || 'Not specified'}</span>
                    </div>
                </div>
                
                <div class="patient-view-section">
                    <h4>Contact Information</h4>
                    <div class="info-row">
                        <span class="info-label">Contact Number:</span>
                        <span class="info-value">${contactFormatted}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email Address:</span>
                        <span class="info-value">${patient.email || 'Not provided'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span class="info-value">${patient.address || '—'}</span>
                    </div>
                </div>
                
                <div class="patient-view-section">
                    <h4>Medical Information</h4>
                    <div class="info-row">
                        <span class="info-label">Patient Type:</span>
                        <span class="info-value"><span class="badge badge-${patient.patient_type === 'inpatient' ? 'blue' : 'green'}">${patient.patient_type ? patient.patient_type.charAt(0).toUpperCase() + patient.patient_type.slice(1) : '—'}</span></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value"><span class="badge badge-green">${patient.status ? patient.status.charAt(0).toUpperCase() + patient.status.slice(1) : 'Active'}</span></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Medical Concern:</span>
                        <span class="info-value" style="display: block; margin-top: 0.5rem;">${patient.concern || 'Not specified'}</span>
                    </div>
                </div>
                
                <div class="patient-view-section">
                    <h4>Registration Information</h4>
                    <div class="info-row">
                        <span class="info-label">Registration Date:</span>
                        <span class="info-value">${registrationDate}</span>
                    </div>
                    ${patient.room_number ? `
                    <div class="info-row">
                        <span class="info-label">Room Number:</span>
                        <span class="info-value">${patient.room_number}</span>
                    </div>
                    ` : ''}
                    ${patient.admission_date ? `
                    <div class="info-row">
                        <span class="info-label">Admission Date:</span>
                        <span class="info-value">${patient.admission_date}</span>
                    </div>
                    ` : ''}
                    <div class="info-row">
                        <span class="info-label">Assigned Nurse:</span>
                        <span class="info-value"><strong>${assignedNurse || 'Not assigned'}</strong></span>
                    </div>
                </div>
            </div>
            
            ${latestVitals || (vitalSignsHistory && vitalSignsHistory.length > 0) ? `
            <div class="patient-view-section patient-view-section-full">
                <h4>Latest Vital Signs</h4>
                ${latestVitals ? `
                <div class="vitals-display">
                    <div class="vital-item-display">
                        <span class="vital-label">Time:</span>
                        <span class="vital-value">${latestVitals.time || '—'}</span>
                    </div>
                    <div class="vital-item-display">
                        <span class="vital-label">Blood Pressure:</span>
                        <span class="vital-value">${latestVitals.blood_pressure || '—'}</span>
                    </div>
                    <div class="vital-item-display">
                        <span class="vital-label">Heart Rate:</span>
                        <span class="vital-value">${latestVitals.heart_rate || '—'}</span>
                    </div>
                    <div class="vital-item-display">
                        <span class="vital-label">Temperature:</span>
                        <span class="vital-value">${latestVitals.temperature || '—'}</span>
                    </div>
                    <div class="vital-item-display">
                        <span class="vital-label">Oxygen Saturation:</span>
                        <span class="vital-value">${latestVitals.oxygen_saturation || '—'}</span>
                    </div>
                    ${latestVitals.recorded_at ? `
                    <div class="vital-item-display">
                        <span class="vital-label">Recorded:</span>
                        <span class="vital-value" style="font-size: 0.8rem; color: #64748b;">${new Date(latestVitals.recorded_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                    </div>
                    ` : ''}
                </div>
                ` : '<p style="color: #64748b; text-align: center;">No vital signs recorded yet</p>'}
                
                ${vitalSignsHistory && vitalSignsHistory.length > 0 ? `
                <div style="margin-top: 1.5rem;">
                    <h5 style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #475569; font-weight: 600;">Recent Vital Signs History</h5>
                    <div class="vitals-history-table">
                        <div class="vitals-history-header">
                            <div>Time</div>
                            <div>BP</div>
                            <div>HR</div>
                            <div>Temp</div>
                            <div>O2 Sat</div>
                            <div>Nurse</div>
                        </div>
                        ${vitalSignsHistory.map(vital => `
                            <div class="vitals-history-row">
                                <div>${vital.time || '—'}</div>
                                <div>${vital.blood_pressure || '—'}</div>
                                <div>${vital.heart_rate || '—'}</div>
                                <div>${vital.temperature || '—'}</div>
                                <div>${vital.oxygen_saturation || '—'}</div>
                                <div><strong>${vital.nurse_name || '—'}</strong></div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            </div>
            ` : ''}
        </div>
    `;
    
    content.innerHTML = html;
}

function closePatientViewModal() {
    const modal = document.getElementById('patientViewModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

// Close modal on backdrop click
document.addEventListener('DOMContentLoaded', function() {
    const backdrop = document.getElementById('patientViewModalBackdrop');
    if (backdrop) {
        backdrop.addEventListener('click', closePatientViewModal);
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('patientViewModal');
            if (modal && modal.style.display === 'block') {
                closePatientViewModal();
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
<?= $this->endSection() ?>

