<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Patients</h2>
        <p>Manage patient records and information</p>
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
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">New Patients Today</div>
                    <div class="kpi-value"><?= $todayCount ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Admitted Patients</div>
                    <div class="kpi-value">—</div>
                    <div class="kpi-change kpi-negative">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Critical Patients</div>
                    <div class="kpi-value">—</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
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
        </div>
    </header>
    
    <div class="stack">
        <!-- Table Header (matches patients table schema) -->
        <div class="card table-header">
            <div class="row between">
                <div class="col-id">Patient ID</div>
                <div class="col-name">Name</div>
                <div class="col-age">AGE/GENDER</div>
                <div class="col-contact">CONTACT</div>
                <div class="col-status">Status</div>
                <div class="col-doctor">DOCTOR</div>
                <div class="col-actions">Actions</div>
            </div>
        </div>

        <!-- Patient Rows (from database) -->
        <?php if (!empty($patientsList)): ?>
            <?php foreach ($patientsList as $p): ?>
                <?php
                    $pid = 'P' . str_pad((string) $p['id'], 3, '0', STR_PAD_LEFT);
                    $last = !empty($p['created_at']) ? date('n/j/Y', strtotime($p['created_at'])) : '—';
                    
                    // Calculate age from date of birth
                    $age = '—';
                    if (!empty($p['date_of_birth']) && $p['date_of_birth'] !== '0000-00-00' && $p['date_of_birth'] !== '') {
                        try {
                            // Handle different date formats
                            $dateStr = $p['date_of_birth'];
                            if (strpos($dateStr, '/') !== false) {
                                // Format: MM/DD/YYYY
                                $parts = explode('/', $dateStr);
                                if (count($parts) === 3) {
                                    $dateStr = $parts[2] . '-' . $parts[0] . '-' . $parts[1]; // Convert to YYYY-MM-DD
                                }
                            }
                            
                            $birthDate = new DateTime($dateStr);
                            $today = new DateTime();
                            $ageDiff = $today->diff($birthDate);
                            $age = $ageDiff->y;
                            
                            // If less than 1 year old, show months
                            if ($age == 0 && $ageDiff->m > 0) {
                                $age = $ageDiff->m . ' months';
                            } else if ($age == 0 && $ageDiff->m == 0 && $ageDiff->d > 0) {
                                $age = $ageDiff->d . ' days';
                            }
                        } catch (Exception $e) {
                            $age = '—';
                        }
                    }
                    
                    // Get blood type or default to O+
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
                                switch(strtolower($p['status'])) {
                                    case 'discharged':
                                        $statusClass = 'badge-gray';
                                        $statusText = 'Discharged';
                                        break;
                                    case 'transferred':
                                        $statusClass = 'badge-yellow';
                                        $statusText = 'Transferred';
                                        break;
                                    case 'inactive':
                                        $statusClass = 'badge-red';
                                        $statusText = 'Inactive';
                                        break;
                                    case 'active':
                                        $statusClass = 'badge-green';
                                        $statusText = 'Active';
                                        break;
                                }
                            }
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            <?php if (isset($p['patient_type'])): ?>
                                <br><small class="text-muted"><?= ucfirst($p['patient_type']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-doctor">
                            <?php if (!empty($p['assigned_doctor_name'])): ?>
                                <strong><?= esc($p['assigned_doctor_name']) ?></strong>
                                <?php if (!empty($p['last_appointment_date'])): ?>
                                    <br><small class="text-muted">Last: <?= date('M j, Y', strtotime($p['last_appointment_date'])) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">No appointments</span>
                            <?php endif; ?>
                        </div>
                <div class="col-actions">
                    <a href="#" class="action-link" onclick="viewPatient(<?= $p['id'] ?>); return false;">View</a>
                    <a href="#" class="action-link" onclick="editPatient(<?= $p['id'] ?>); return false;">Edit</a>
                    <a href="#" class="action-link action-delete" onclick="deletePatient(<?= $p['id'] ?>, <?= json_encode($p['full_name']) ?>); return false;">Delete</a>
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

<!-- View Patient Modal -->
<div id="viewPatientModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeViewModal()"></div>
    <div class="modal-dialog" style="max-width: 1000px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>Patient Details</h3>
            <button class="modal-close" onclick="closeViewModal()">&times;</button>
        </div>
        <div class="modal-body" id="viewPatientContent" style="padding: 1.5rem;">
            <div style="text-align: center; padding: 2rem;">
                <p>Loading patient information...</p>
            </div>
        </div>
    </div>
</div>

<!-- Edit Patient Modal -->
<div id="editPatientModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeEditModal()"></div>
    <div class="modal-dialog" style="max-width: 700px;">
        <div class="modal-header">
            <h3>Edit Patient</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editPatientForm" onsubmit="updatePatient(event); return false;">
                <input type="hidden" id="edit_patient_id" name="patient_id">
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Full Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="edit_full_name" name="full_name" required style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Date of Birth <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="edit_date_of_birth" name="date_of_birth" placeholder="MM/DD/YYYY" required style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Gender <span style="color: #ef4444;">*</span></label>
                        <select id="edit_gender" name="gender" required style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Blood Type</label>
                        <select id="edit_blood_type" name="blood_type" style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <option value="">Select Blood Type</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Contact <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="edit_contact" name="contact" required style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Email</label>
                        <input type="email" id="edit_email" name="email" style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Address</label>
                    <input type="text" id="edit_address" name="address" style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Status</label>
                        <select id="edit_status" name="status" style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <option value="active">Active</option>
                            <option value="discharged">Discharged</option>
                            <option value="transferred">Transferred</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Patient Type</label>
                        <select id="edit_patient_type" name="patient_type" style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <option value="outpatient">Outpatient</option>
                            <option value="inpatient">Inpatient</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Concern</label>
                    <textarea id="edit_concern" name="concern" rows="3" style="width: 100%; padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 6px; resize: vertical;"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeEditModal()" style="padding: 0.625rem 1.25rem; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancel</button>
                    <button type="submit" style="padding: 0.625rem 1.25rem; background: #1C3F70; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Update Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewPatient(id) {
    fetch('<?= base_url('admin/patients/view/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const patient = data.patient;
                const pid = 'P' + String(patient.id).padStart(3, '0');
                
                // Build assigned staff section
                let assignedStaffHtml = '';
                if (patient.assigned_doctor || patient.assigned_nurse) {
                    assignedStaffHtml = `
                        <div style="margin-top: 1.5rem;">
                            <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Assigned Staff</h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Assigned Doctor</span>
                                    <strong style="color: #1e293b;">${patient.assigned_doctor?.doctor_name || '—'}</strong>
                                    ${patient.assigned_doctor?.appointment_date ? `<div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">Last Appointment: ${new Date(patient.assigned_doctor.appointment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>` : ''}
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Assigned Nurse</span>
                                    <strong style="color: #1e293b;">${patient.assigned_nurse || '—'}</strong>
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Build prescriptions section
                let prescriptionsHtml = '';
                if (patient.prescriptions && patient.prescriptions.length > 0) {
                    let prescriptionsList = '';
                    patient.prescriptions.forEach(rx => {
                        let itemsList = '';
                        if (rx.items && rx.items.length > 0) {
                            itemsList = rx.items.map(item => 
                                `${item.name || 'N/A'} - ${item.dosage || 'N/A'} (${item.frequency || 'N/A'})`
                            ).join('<br>');
                        } else {
                            itemsList = 'No medications';
                        }
                        
                        const statusClass = rx.status === 'completed' ? 'badge-green' : rx.status === 'dispensed' ? 'badge-blue' : 'badge-yellow';
                        const statusText = (rx.status || 'pending').charAt(0).toUpperCase() + (rx.status || 'pending').slice(1);
                        
                        prescriptionsList += `
                            <div style="padding: 1rem; background: #f8fafc; border-radius: 8px; margin-bottom: 0.75rem; border-left: 3px solid #1C3F70;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <div>
                                        <strong style="color: #1e293b;">${rx.rx_number}</strong>
                                        <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">By: ${rx.doctor_name}</div>
                                    </div>
                                    <span class="badge ${statusClass}">${statusText}</span>
                                </div>
                                <div style="font-size: 0.875rem; color: #475569; margin-bottom: 0.5rem;">
                                    <strong>Medications:</strong><br>
                                    <div style="margin-top: 0.25rem; padding-left: 1rem;">${itemsList}</div>
                                </div>
                                ${rx.notes ? `<div style="font-size: 0.875rem; color: #475569; margin-top: 0.5rem;"><strong>Notes:</strong> ${rx.notes}</div>` : ''}
                                <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.5rem;">${rx.created_at_formatted}</div>
                            </div>
                        `;
                    });
                    
                    prescriptionsHtml = `
                        <div style="margin-top: 1.5rem;">
                            <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Prescriptions (${patient.prescriptions.length})</h4>
                            <div style="max-height: 300px; overflow-y: auto;">
                                ${prescriptionsList}
                            </div>
                        </div>
                    `;
                } else {
                    prescriptionsHtml = `
                        <div style="margin-top: 1.5rem;">
                            <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Prescriptions</h4>
                            <p style="color: #6B7280; font-style: italic;">No prescriptions found</p>
                        </div>
                    `;
                }

                // Build lab tests section
                let labTestsHtml = '';
                if (patient.lab_tests && patient.lab_tests.length > 0) {
                    let labTestsList = '';
                    patient.lab_tests.forEach(lab => {
                        const statusClass = lab.has_result ? (lab.is_critical ? 'badge-danger' : 'badge-green') : 
                                          lab.status === 'in_progress' ? 'badge-blue' : 'badge-yellow';
                        const statusText = lab.has_result ? (lab.is_critical ? 'Critical Result' : 'Completed') : 
                                          (lab.status || 'pending').charAt(0).toUpperCase() + (lab.status || 'pending').slice(1);
                        const priorityClass = lab.priority === 'urgent' ? 'badge-danger' : lab.priority === 'high' ? 'badge-warning' : 'badge-info';
                        
                        labTestsList += `
                            <div style="padding: 1rem; background: #f8fafc; border-radius: 8px; margin-bottom: 0.75rem; border-left: 3px solid ${lab.is_critical ? '#ef4444' : '#1C3F70'};">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <div>
                                        <strong style="color: #1e293b;">${lab.test_type}</strong>
                                        <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">By: ${lab.doctor_name}</div>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <span class="badge ${priorityClass}">${(lab.priority || 'normal').toUpperCase()}</span>
                                        <span class="badge ${statusClass}">${statusText}</span>
                                    </div>
                                </div>
                                ${lab.has_result && lab.result_summary ? `
                                    <div style="font-size: 0.875rem; color: #475569; margin-top: 0.5rem; padding: 0.75rem; background: white; border-radius: 6px;">
                                        <strong>Result Summary:</strong><br>
                                        ${lab.result_summary}
                                    </div>
                                ` : ''}
                                <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.5rem;">${lab.created_at_formatted}</div>
                            </div>
                        `;
                    });
                    
                    labTestsHtml = `
                        <div style="margin-top: 1.5rem;">
                            <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Lab Tests (${patient.lab_tests.length})</h4>
                            <div style="max-height: 300px; overflow-y: auto;">
                                ${labTestsList}
                            </div>
                        </div>
                    `;
                } else {
                    labTestsHtml = `
                        <div style="margin-top: 1.5rem;">
                            <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Lab Tests</h4>
                            <p style="color: #6B7280; font-style: italic;">No lab tests found</p>
                        </div>
                    `;
                }

                let html = `
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                        <div>
                            <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Personal Information</h4>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Patient ID</span>
                                    <strong style="color: #1e293b;">${pid}</strong>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Full Name</span>
                                    <strong style="color: #1e293b;">${patient.full_name || '—'}</strong>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Date of Birth</span>
                                    <span style="color: #1e293b;">${patient.date_of_birth_formatted || '—'}</span>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Age</span>
                                    <span style="color: #1e293b;">${patient.age || '—'} ${patient.age && !isNaN(patient.age) ? 'years' : ''}</span>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Gender</span>
                                    <span style="color: #1e293b;">${patient.gender || '—'}</span>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Blood Type</span>
                                    <span style="color: #1e293b;">${patient.blood_type || '—'}</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Contact Information</h4>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Contact Number</span>
                                    <span style="color: #1e293b;">${patient.contact || '—'}</span>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Email</span>
                                    <span style="color: #1e293b;">${patient.email || '—'}</span>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Address</span>
                                    <span style="color: #1e293b;">${patient.address || '—'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Medical Information</h4>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                            <div>
                                <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Status</span>
                                <span class="badge ${patient.status === 'active' ? 'badge-green' : patient.status === 'discharged' ? 'badge-gray' : 'badge-yellow'}">${(patient.status || 'active').charAt(0).toUpperCase() + (patient.status || 'active').slice(1)}</span>
                            </div>
                            <div>
                                <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Patient Type</span>
                                <span style="color: #1e293b;">${(patient.patient_type || 'outpatient').charAt(0).toUpperCase() + (patient.patient_type || 'outpatient').slice(1)}</span>
                            </div>
                        </div>
                        <div style="margin-top: 1rem;">
                            <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Concern</span>
                            <p style="color: #1e293b; margin: 0; padding: 0.75rem; background: #f8fafc; border-radius: 6px; white-space: pre-wrap;">${patient.concern || '—'}</p>
                        </div>
                    </div>
                    
                    ${patient.patient_type === 'inpatient' ? `
                    <div style="margin-top: 1.5rem;">
                        <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Room & Admission Information</h4>
                        
                        <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f0f9ff; border-radius: 8px; border-left: 3px solid #3b82f6;">
                            <h5 style="margin: 0 0 0.75rem 0; color: #1C3F70; font-size: 0.9375rem;">Guardian / Emergency Contact</h5>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Guardian Name</span>
                                    <strong style="color: #1e293b;">${patient.emergency_name || '—'}</strong>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Relationship</span>
                                    <strong style="color: #1e293b;">${patient.relationship || '—'}</strong>
                                </div>
                                <div style="grid-column: 1 / -1;">
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Emergency Contact</span>
                                    <strong style="color: #1e293b;">${patient.emergency_contact || '—'}</strong>
                                </div>
                            </div>
                        </div>
                        
                        ${patient.room_number || patient.room_info ? `
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1rem;">
                            <div>
                                <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Room Number</span>
                                <strong style="color: #1e293b; font-size: 1.125rem;">${patient.room_number || patient.room_info?.room_number || '—'}</strong>
                                ${patient.room_info ? `
                                    <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">
                                        ${patient.room_info.room_type ? `Type: ${patient.room_info.room_type.charAt(0).toUpperCase() + patient.room_info.room_type.slice(1)}` : ''}
                                        ${patient.room_info.floor ? ` • Floor: ${patient.room_info.floor}` : ''}
                                    </div>
                                ` : ''}
                            </div>
                            <div>
                                <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Room Status</span>
                                ${patient.room_info ? `
                                    <span class="badge ${patient.room_info.is_available ? 'badge-green' : 'badge-warning'}">
                                        ${patient.room_info.is_available ? 'Available' : 'Occupied'}
                                    </span>
                                    ${patient.room_info.capacity ? `
                                        <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">
                                            Capacity: ${patient.room_info.current_occupancy || 0}/${patient.room_info.capacity}
                                        </div>
                                    ` : ''}
                                ` : '<span style="color: #6B7280;">No room details</span>'}
                            </div>
                        </div>
                        ` : '<p style="color: #6B7280; font-style: italic; margin-bottom: 1rem;">No room assigned</p>'}
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1rem;">
                            <div>
                                <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Admission Date</span>
                                <strong style="color: #1e293b;">${patient.admission_date_formatted || (patient.admission_appointment?.appointment_date ? new Date(patient.admission_appointment.appointment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—')}</strong>
                                ${patient.admission_appointment?.appointment_time ? `
                                    <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">
                                        Time: ${patient.admission_appointment.appointment_time}
                                    </div>
                                ` : ''}
                            </div>
                            <div>
                                <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Discharge Date</span>
                                <strong style="color: ${patient.discharge_date_formatted && patient.discharge_date_formatted !== '—' ? '#1e293b' : '#6B7280'};">${patient.discharge_date_formatted || 'Not discharged'}</strong>
                            </div>
                        </div>
                        ${patient.admission_appointment ? `
                        <div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border-left: 3px solid #1C3F70;">
                            <h5 style="margin: 0 0 0.75rem 0; color: #1C3F70; font-size: 0.9375rem;">Admission Form Details</h5>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Admission Type</span>
                                    <span class="badge ${patient.admission_appointment.appointment_type === 'emergency' ? 'badge-danger' : 'badge-info'}">
                                        ${(patient.admission_appointment.appointment_type || 'N/A').charAt(0).toUpperCase() + (patient.admission_appointment.appointment_type || 'N/A').slice(1)}
                                    </span>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Attending Doctor</span>
                                    <strong style="color: #1e293b;">${patient.admission_appointment.doctor_name || '—'}</strong>
                                </div>
                            </div>
                            ${patient.admission_appointment.appointment_notes ? `
                            <div style="margin-top: 0.75rem;">
                                <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Admission Notes</span>
                                <p style="color: #1e293b; margin: 0; padding: 0.5rem; background: white; border-radius: 6px; white-space: pre-wrap; font-size: 0.875rem;">${patient.admission_appointment.appointment_notes}</p>
                            </div>
                            ` : ''}
                        </div>
                        ` : ''}
                    </div>
                    ` : ''}
                    
                    ${assignedStaffHtml}
                    
                    ${patient.insurance_info || patient.insurance_claims?.length > 0 ? `
                    <div style="margin-top: 1.5rem;">
                        <h4 style="margin: 0 0 1rem 0; color: #1C3F70; border-bottom: 2px solid #1C3F70; padding-bottom: 0.5rem;">Insurance Information</h4>
                        ${patient.insurance_info ? `
                        <div style="margin-bottom: 1rem; padding: 1rem; background: #f0fdf4; border-radius: 8px; border-left: 3px solid #10b981;">
                            <h5 style="margin: 0 0 0.75rem 0; color: #1C3F70; font-size: 0.9375rem;">Primary Insurance Policy</h5>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Insurance Provider</span>
                                    <strong style="color: #1e293b;">${patient.insurance_info.insurance_provider || '—'}</strong>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Policy Number</span>
                                    <strong style="color: #1e293b;">${patient.insurance_info.policy_number || '—'}</strong>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Member ID</span>
                                    <strong style="color: #1e293b;">${patient.insurance_info.member_id || '—'}</strong>
                                </div>
                                <div>
                                    <span style="font-size: 0.875rem; color: #6B7280; display: block; margin-bottom: 0.25rem;">Policy Status</span>
                                    <span class="badge ${patient.insurance_info.policy_status === 'active' ? 'badge-success' : patient.insurance_info.policy_status === 'inactive' ? 'badge-danger' : 'badge-warning'}" title="Overall policy status">
                                        ${(patient.insurance_info.policy_status || 'active').charAt(0).toUpperCase() + (patient.insurance_info.policy_status || 'active').slice(1)}
                                    </span>
                                    <small style="display: block; font-size: 0.7rem; color: #6B7280; margin-top: 0.25rem;">Policy is ${(patient.insurance_info.policy_status || 'active') === 'active' ? 'active and valid' : 'inactive'}</small>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                        ${patient.insurance_claims && patient.insurance_claims.length > 0 ? `
                        <div style="margin-top: 1rem;">
                            <h5 style="margin: 0 0 0.75rem 0; color: #1C3F70; font-size: 0.9375rem;">Insurance Claims History</h5>
                            <p style="font-size: 0.8rem; color: #6B7280; margin-bottom: 0.75rem;">Past and current insurance claims for this patient</p>
                            <div style="max-height: 300px; overflow-y: auto;">
                                ${patient.insurance_claims
                                    .filter(claim => parseFloat(claim.claim_amount || 0) > 0) // Filter out zero-amount claims
                                    .map(claim => {
                                        // Check if policy/member ID matches primary insurance (to avoid redundancy)
                                        const isPrimaryPolicy = patient.insurance_info && 
                                            claim.policy_number === patient.insurance_info.policy_number &&
                                            claim.member_id === patient.insurance_info.member_id;
                                        
                                        return `
                                    <div style="padding: 1rem; background: #f8fafc; border-radius: 8px; margin-bottom: 0.75rem; border-left: 3px solid ${claim.status === 'paid' ? '#10b981' : claim.status === 'approved' ? '#3b82f6' : claim.status === 'rejected' ? '#ef4444' : '#f59e0b'};">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                            <div>
                                                <strong style="color: #1e293b; font-size: 0.9375rem;">${claim.claim_number || 'N/A'}</strong>
                                                ${!isPrimaryPolicy ? `
                                                <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">
                                                    Provider: ${claim.insurance_provider || '—'}
                                                </div>
                                                ` : ''}
                                            </div>
                                            <span class="badge ${claim.status === 'approved' ? 'badge-success' : claim.status === 'rejected' ? 'badge-danger' : claim.status === 'paid' ? 'badge-info' : 'badge-warning'}" title="Claim status">
                                                ${(claim.status || 'pending').charAt(0).toUpperCase() + (claim.status || 'pending').slice(1)}
                                            </span>
                                        </div>
                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; font-size: 0.875rem;">
                                            ${!isPrimaryPolicy ? `
                                            <div>
                                                <span style="color: #6B7280;">Policy:</span>
                                                <strong style="color: #1e293b; margin-left: 0.5rem;">${claim.policy_number || '—'}</strong>
                                            </div>
                                            <div>
                                                <span style="color: #6B7280;">Member ID:</span>
                                                <strong style="color: #1e293b; margin-left: 0.5rem;">${claim.member_id || '—'}</strong>
                                            </div>
                                            ` : `
                                            <div>
                                                <span style="color: #6B7280;">Policy:</span>
                                                <strong style="color: #1e293b; margin-left: 0.5rem;" title="Same as Primary Insurance">${claim.policy_number || '—'}</strong>
                                                <small style="color: #10b981; margin-left: 0.25rem; font-size: 0.7rem;">(Primary)</small>
                                            </div>
                                            <div>
                                                <span style="color: #6B7280;">Member ID:</span>
                                                <strong style="color: #1e293b; margin-left: 0.5rem;" title="Same as Primary Insurance">${claim.member_id || '—'}</strong>
                                            </div>
                                            `}
                                            <div>
                                                <span style="color: #6B7280;">Claim Amount:</span>
                                                <strong style="color: #1e293b; margin-left: 0.5rem;">₱${parseFloat(claim.claim_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                                            </div>
                                            <div>
                                                <span style="color: #6B7280;">Approved Amount:</span>
                                                <strong style="color: #10b981; margin-left: 0.5rem;">₱${parseFloat(claim.approved_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                                            </div>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid #e5e7eb;">
                                            ${claim.submitted_date && claim.submitted_date !== '—' ? `<span>📅 Submitted: ${claim.submitted_date}</span>` : ''}
                                            ${claim.approved_date && claim.approved_date !== '—' ? `<span style="margin-left: 1rem;">✅ Approved: ${claim.approved_date}</span>` : ''}
                                            ${!claim.submitted_date || claim.submitted_date === '—' ? '<span style="color: #f59e0b;">⏳ Not yet submitted</span>' : ''}
                                        </div>
                                    </div>
                                `;
                                    }).join('')}
                                ${patient.insurance_claims.filter(claim => parseFloat(claim.claim_amount || 0) > 0).length === 0 ? `
                                    <div style="padding: 1.5rem; text-align: center; color: #6B7280; font-size: 0.875rem;">
                                        No valid claims found (all claims have zero amounts)
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    ` : ''}
                    
                    ${prescriptionsHtml}
                    ${labTestsHtml}
                `;
                
                document.getElementById('viewPatientContent').innerHTML = html;
                document.getElementById('viewPatientModal').style.display = 'flex';
            } else {
                alert('Error loading patient: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading patient information');
        });
}

function closeViewModal() {
    document.getElementById('viewPatientModal').style.display = 'none';
}

function editPatient(id) {
    fetch('<?= base_url('admin/patients/edit/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const patient = data.patient;
                document.getElementById('edit_patient_id').value = patient.id;
                document.getElementById('edit_full_name').value = patient.full_name || '';
                document.getElementById('edit_date_of_birth').value = patient.date_of_birth || '';
                document.getElementById('edit_gender').value = patient.gender || '';
                document.getElementById('edit_blood_type').value = patient.blood_type || '';
                document.getElementById('edit_contact').value = patient.contact || '';
                document.getElementById('edit_email').value = patient.email || '';
                document.getElementById('edit_address').value = patient.address || '';
                document.getElementById('edit_status').value = patient.status || 'active';
                document.getElementById('edit_patient_type').value = patient.patient_type || 'outpatient';
                document.getElementById('edit_concern').value = patient.concern || '';
                
                document.getElementById('editPatientModal').style.display = 'flex';
            } else {
                alert('Error loading patient: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading patient information');
        });
}

function closeEditModal() {
    document.getElementById('editPatientModal').style.display = 'none';
}

function updatePatient(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const patientId = document.getElementById('edit_patient_id').value;
    
    fetch('<?= base_url('admin/patients/update/') ?>' + patientId, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Patient updated successfully!');
                closeEditModal();
                location.reload();
            } else {
                let errorMsg = 'Error updating patient';
                if (data.errors) {
                    errorMsg += ': ' + Object.values(data.errors).join(', ');
                } else if (data.message) {
                    errorMsg += ': ' + data.message;
                }
                alert(errorMsg);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating patient');
        });
}

function deletePatient(id, name) {
    if (!confirm('Are you sure you want to delete patient "' + name + '"? This action cannot be undone.')) {
        return;
    }
    
    fetch('<?= base_url('admin/patients/delete/') ?>' + id, {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Patient deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting patient: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting patient');
        });
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-backdrop')) {
        closeViewModal();
        closeEditModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeViewModal();
        closeEditModal();
    }
});
</script>

<?= $this->endSection() ?>