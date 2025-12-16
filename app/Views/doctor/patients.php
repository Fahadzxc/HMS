<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>My Patients</h2>
        <p>View and manage your patient records</p>
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
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div class="card table-row">
            <div class="row between">
                    <div class="col-name">No patients assigned yet.</div>
                </div>
            </div>
        <?php endif; ?>
        </div>
</section>

<style>
/* Grid layout to guarantee perfect alignment */
.patients-grid {
    display: grid;
    grid-template-columns: 70px 200px 100px 140px 100px 130px 100px; /* ID, Name, Age, Contact, Status, Doctor, Actions */
    align-items: center;
    gap: 0.5rem;
}

/* Prevent legacy flex widths from interfering */
.patients-grid > div { 
    flex: none; 
    min-width: 0;
    padding: 0.75rem 1rem;
}

/* Make header and rows align by column widths (override space-between) */
.table-header .row,
.table-row .row {
    justify-content: flex-start;
    gap: 0;
}

.col-id {
    text-align: left;
    font-weight: 600;
}

.col-name {
    text-align: left;
}

.col-age {
    text-align: center;
}

.col-contact {
    text-align: center;
}

.col-status {
    text-align: center;
}

.col-doctor {
    text-align: center;
}

.col-actions {
    text-align: right;
}

/* Clean spacing for patient info */
.patient-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.patient-avatar {
    flex-shrink: 0;
}

.patient-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.patient-details strong {
    font-size: 0.95rem;
    color: #1e293b;
}

.blood-type {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
}

.phone, .email {
    margin: 0;
    font-size: 0.875rem;
    line-height: 1.4;
}

.phone {
    color: #1e293b;
    font-weight: 500;
}

.email {
    color: #64748b;
}

/* Responsive adjustments */
@media (max-width: 1400px) {
    .patients-grid {
        grid-template-columns: 70px 1fr 130px 240px 130px 180px 140px;
    }
    
    .patients-grid > div {
        padding: 0.65rem 0.85rem;
    }
}

@media (max-width: 768px) {
    .patients-grid {
        display: flex;
        flex-direction: column;
    }
    }
</style>

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
    fetch(`<?= site_url('doctor/patients/show') ?>/${patientId}`)
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

