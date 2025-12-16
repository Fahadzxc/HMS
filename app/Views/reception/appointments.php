<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<style>
.appointment-type-btn {
    transition: all 0.3s ease !important;
}

.appointment-type-btn:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2) !important;
}

.appointment-type-btn:active {
    transform: translateY(-2px) !important;
}

.appointment-type-selection {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.lab-test-option {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.lab-test-option:hover {
    background: #f1f5f9;
    border-color: #10b981;
}

.lab-test-option input[type="checkbox"] {
    margin-right: 0.75rem;
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #10b981;
}

.lab-test-option input[type="checkbox"]:checked + span {
    color: #10b981;
    font-weight: 600;
}

.lab-test-group {
    margin-bottom: 1.5rem;
}

.lab-test-group.hidden {
    display: none;
}

#selected_tests_chips .test-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    background: #d1fae5;
    color: #065f46;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
}

#selected_tests_chips .test-chip .remove-chip {
    margin-left: 0.5rem;
    cursor: pointer;
    color: #047857;
    font-weight: bold;
}

#selected_tests_chips .test-chip .remove-chip:hover {
    color: #064e3b;
}

/* Lab Test Modal - Higher z-index to appear above appointment modal */
#labTestModal {
    z-index: 2000 !important;
}

#labTestModal .modal-backdrop {
    z-index: 2001 !important;
}

#labTestModal .modal-dialog {
    z-index: 2002 !important;
    position: relative;
}
</style>

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
                                    <?php
                                    $type = strtolower($appointment['appointment_type'] ?? 'consultation');
                                    $typeBadge = 'primary';
                                    if ($type === 'emergency') {
                                        $typeBadge = 'danger';
                                    } elseif (in_array($type, ['lab_test', 'xray'])) {
                                        $typeBadge = 'info';
                                    } elseif ($type === 'vaccination') {
                                        $typeBadge = 'success';
                                    } elseif ($type === 'routine') {
                                        $typeBadge = 'secondary';
                                    }
                                    $typeDisplay = str_replace('_', ' ', $type);
                                    ?>
                                    <span class="badge badge-<?= $typeBadge ?>">
                                        <?= strtoupper($typeDisplay) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $appointment['status'] === 'confirmed' ? 'success' : 
                                        ($appointment['status'] === 'scheduled' ? 'warning' : 'secondary') 
                                    ?>">
                                        <?= strtoupper($appointment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] === 'scheduled'): ?>
                                        <span class="text-muted" style="cursor: pointer; text-decoration: underline;" onclick="checkInPatient(<?= $appointment['id'] ?>)">
                                            Check In
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted" style="text-decoration: underline;">
                                            Checked In
                                        </span>
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
                                <td>
                                    <?php
                                    $type = strtolower($appointment['appointment_type'] ?? 'consultation');
                                    $typeBadge = 'primary';
                                    if ($type === 'emergency') {
                                        $typeBadge = 'danger';
                                    } elseif (in_array($type, ['lab_test', 'xray'])) {
                                        $typeBadge = 'info';
                                    } elseif ($type === 'vaccination') {
                                        $typeBadge = 'success';
                                    } elseif ($type === 'routine') {
                                        $typeBadge = 'secondary';
                                    }
                                    $typeDisplay = str_replace('_', ' ', $type);
                                    ?>
                                    <span class="badge badge-<?= $typeBadge ?>">
                                        <?= strtoupper($typeDisplay) ?>
                                    </span>
                                </td>
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
                                        <span class="text-muted" style="cursor: pointer; text-decoration: underline;" onclick="checkInPatient(<?= $appointment['id'] ?>)">
                                            Check In
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted" style="cursor: pointer; text-decoration: underline;" onclick="editAppointment(<?= $appointment['id'] ?>)">
                                            Edit
                                        </span>
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

<!-- Lab Test Selection Modal -->
<div id="labTestModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" onclick="closeLabTestModal()"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" style="max-width: 600px;">
        <header class="panel-header modal-header">
            <h2>Select Lab Test(s)</h2>
            <button type="button" class="close" onclick="closeLabTestModal()">&times;</button>
        </header>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div style="margin-bottom: 1rem;">
                <input type="text" id="lab_test_search" placeholder="Search lab tests..." style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.875rem;" onkeyup="filterLabTests()">
            </div>
            <div id="lab_test_options" style="display: grid; gap: 0.75rem;">
                <!-- Blood Tests -->
                <div class="lab-test-group">
                    <h4 style="margin: 0 0 0.75rem 0; color: #1f2937; font-size: 1rem; font-weight: 600; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.5rem;">Blood Tests</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Complete Blood Count (CBC)" onchange="updateSelectedLabTests()">
                            <span>Complete Blood Count (CBC)</span>
                        </label>
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Blood Glucose" onchange="updateSelectedLabTests()">
                            <span>Blood Glucose</span>
                        </label>
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Lipid Profile" onchange="updateSelectedLabTests()">
                            <span>Lipid Profile</span>
                        </label>
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Liver Function Test (LFT)" onchange="updateSelectedLabTests()">
                            <span>Liver Function Test (LFT)</span>
                        </label>
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Kidney Function Test (KFT)" onchange="updateSelectedLabTests()">
                            <span>Kidney Function Test (KFT)</span>
                        </label>
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Thyroid Function Test" onchange="updateSelectedLabTests()">
                            <span>Thyroid Function Test</span>
                        </label>
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Hemoglobin A1C" onchange="updateSelectedLabTests()">
                            <span>Hemoglobin A1C</span>
                        </label>
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Blood Culture" onchange="updateSelectedLabTests()">
                            <span>Blood Culture</span>
                        </label>
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Blood Typing" onchange="updateSelectedLabTests()">
                            <span>Blood Typing</span>
                        </label>
                        <label class="lab-test-option" data-category="blood">
                            <input type="checkbox" value="Coagulation Profile" onchange="updateSelectedLabTests()">
                            <span>Coagulation Profile</span>
                        </label>
                    </div>
                </div>
                
                <!-- Urine Tests -->
                <div class="lab-test-group">
                    <h4 style="margin: 0 0 0.75rem 0; color: #1f2937; font-size: 1rem; font-weight: 600; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.5rem;">Urine Tests</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        <label class="lab-test-option" data-category="urine">
                            <input type="checkbox" value="Urine Analysis" onchange="updateSelectedLabTests()">
                            <span>Urine Analysis</span>
                        </label>
                        <label class="lab-test-option" data-category="urine">
                            <input type="checkbox" value="Urinalysis" onchange="updateSelectedLabTests()">
                            <span>Urinalysis</span>
                        </label>
                        <label class="lab-test-option" data-category="urine">
                            <input type="checkbox" value="Urine Culture" onchange="updateSelectedLabTests()">
                            <span>Urine Culture</span>
                        </label>
                        <label class="lab-test-option" data-category="urine">
                            <input type="checkbox" value="24-Hour Urine Collection" onchange="updateSelectedLabTests()">
                            <span>24-Hour Urine Collection</span>
                        </label>
                        <label class="lab-test-option" data-category="urine">
                            <input type="checkbox" value="Urine Pregnancy Test" onchange="updateSelectedLabTests()">
                            <span>Urine Pregnancy Test</span>
                        </label>
                    </div>
                </div>
                
                <!-- Imaging Tests -->
                <div class="lab-test-group">
                    <h4 style="margin: 0 0 0.75rem 0; color: #1f2937; font-size: 1rem; font-weight: 600; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.5rem;">Imaging Tests</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        <label class="lab-test-option" data-category="imaging">
                            <input type="checkbox" value="X-Ray" onchange="updateSelectedLabTests()">
                            <span>X-Ray</span>
                        </label>
                        <label class="lab-test-option" data-category="imaging">
                            <input type="checkbox" value="CT Scan" onchange="updateSelectedLabTests()">
                            <span>CT Scan</span>
                        </label>
                        <label class="lab-test-option" data-category="imaging">
                            <input type="checkbox" value="MRI" onchange="updateSelectedLabTests()">
                            <span>MRI</span>
                        </label>
                        <label class="lab-test-option" data-category="imaging">
                            <input type="checkbox" value="Ultrasound" onchange="updateSelectedLabTests()">
                            <span>Ultrasound</span>
                        </label>
                        <label class="lab-test-option" data-category="imaging">
                            <input type="checkbox" value="Echocardiogram" onchange="updateSelectedLabTests()">
                            <span>Echocardiogram</span>
                        </label>
                        <label class="lab-test-option" data-category="imaging">
                            <input type="checkbox" value="Mammography" onchange="updateSelectedLabTests()">
                            <span>Mammography</span>
                        </label>
                    </div>
                </div>
                
                <!-- Microbiology -->
                <div class="lab-test-group">
                    <h4 style="margin: 0 0 0.75rem 0; color: #1f2937; font-size: 1rem; font-weight: 600; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.5rem;">Microbiology</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        <label class="lab-test-option" data-category="microbiology">
                            <input type="checkbox" value="Sputum Culture" onchange="updateSelectedLabTests()">
                            <span>Sputum Culture</span>
                        </label>
                        <label class="lab-test-option" data-category="microbiology">
                            <input type="checkbox" value="Stool Culture" onchange="updateSelectedLabTests()">
                            <span>Stool Culture</span>
                        </label>
                        <label class="lab-test-option" data-category="microbiology">
                            <input type="checkbox" value="Throat Swab" onchange="updateSelectedLabTests()">
                            <span>Throat Swab</span>
                        </label>
                        <label class="lab-test-option" data-category="microbiology">
                            <input type="checkbox" value="Wound Culture" onchange="updateSelectedLabTests()">
                            <span>Wound Culture</span>
                        </label>
                    </div>
                </div>
                
                <!-- Other Tests -->
                <div class="lab-test-group">
                    <h4 style="margin: 0 0 0.75rem 0; color: #1f2937; font-size: 1rem; font-weight: 600; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.5rem;">Other Tests</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        <label class="lab-test-option" data-category="other">
                            <input type="checkbox" value="ECG (Electrocardiogram)" onchange="updateSelectedLabTests()">
                            <span>ECG (Electrocardiogram)</span>
                        </label>
                        <label class="lab-test-option" data-category="other">
                            <input type="checkbox" value="ECG" onchange="updateSelectedLabTests()">
                            <span>ECG</span>
                        </label>
                        <label class="lab-test-option" data-category="other">
                            <input type="checkbox" value="Other" onchange="updateSelectedLabTests()">
                            <span>Other</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <footer class="modal-footer">
            <button type="button" class="btn-secondary" onclick="clearLabTestSelection()">Clear All</button>
            <button type="button" class="btn-secondary" onclick="closeLabTestModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="confirmLabTestSelection()">Confirm Selection</button>
        </footer>
    </div>
</div>

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
            
            <!-- Step 1: Appointment Type Selection -->
            <div id="appointment_type_selection" class="appointment-type-selection" style="text-align: center; padding: 2rem 1rem;">
                <h3 style="margin-bottom: 1.5rem; color: #1f2937; font-size: 1.25rem;">Select Appointment Type</h3>
                <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
                    <button type="button" class="appointment-type-btn" data-type="consultation" onclick="selectAppointmentType('consultation')" style="min-width: 200px; max-width: 300px; padding: 2rem; background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); color: white; border: 3px solid transparent; border-radius: 0.75rem; cursor: pointer; font-size: 1.1rem; font-weight: 600; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3); transition: all 0.3s ease;">
                        <i class="fas fa-user-md" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block;"></i>
                        <div style="font-size: 1.25rem; margin-bottom: 0.5rem; font-weight: 700;">Consultation</div>
                        <div style="font-size: 0.875rem; opacity: 0.95;">Doctor consultation appointment</div>
                    </button>
                </div>
                <p style="margin-top: 1.5rem; color: #6b7280; font-size: 0.875rem;">Choose the type of appointment you want to create</p>
            </div>
            
            <!-- Step 2: Appointment Form (Hidden initially) -->
            <div id="appointment_form_fields" class="form-grid" style="display: none;">
                <!-- Hidden field to store selected appointment type -->
                <input type="hidden" name="appointment_type" id="appointment_type_hidden">
                
                <div class="form-field">
                    <label>Select Patient <span class="req">*</span></label>
                    <select name="patient_id" id="patient_select" required onchange="loadPatientDetails()">
                        <option value="">Choose a patient...</option>
                        <?php if (isset($patients) && is_array($patients)): ?>
                            <?php foreach ($patients as $patient): ?>
                                <?php
                                    // Calculate age from date of birth
                                    $ageDob = '—';
                                    if (!empty($patient['date_of_birth']) && $patient['date_of_birth'] !== '0000-00-00' && $patient['date_of_birth'] !== '' && $patient['date_of_birth'] !== null) {
                                        try {
                                            $dateStr = $patient['date_of_birth'];
                                            if (strpos($dateStr, '/') !== false) {
                                                $parts = explode('/', $dateStr);
                                                if (count($parts) === 3) {
                                                    $dateStr = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                                }
                                            }
                                            $birthDate = new \DateTime($dateStr);
                                            $today = new \DateTime();
                                            $ageDiff = $today->diff($birthDate);
                                            $age = $ageDiff->y;
                                            $dobTimestamp = strtotime($dateStr);
                                            if ($dobTimestamp !== false) {
                                                $ageDob = "Age: {$age} years | DOB: " . date('M d, Y', $dobTimestamp);
                                            }
                                        } catch (\Exception $e) {
                                            $ageDob = '—';
                                        }
                                    }
                                    $gender = !empty($patient['gender']) ? htmlspecialchars($patient['gender']) : '—';
                                ?>
                                <option value="<?= $patient['id'] ?>" 
                                        data-name="<?= htmlspecialchars($patient['full_name']) ?>" 
                                        data-contact="<?= htmlspecialchars($patient['contact'] ?? '') ?>"
                                        data-age-dob="<?= htmlspecialchars($ageDob) ?>"
                                        data-gender="<?= htmlspecialchars($gender) ?>"
                                        data-patient-type="<?= htmlspecialchars(strtolower($patient['patient_type'] ?? 'outpatient')) ?>">
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
                <!-- Patient Age/DOB and Gender (for Lab Tests) -->
                <div class="form-field" id="patient_age_field" style="display: none;">
                    <label>Age / Date of Birth</label>
                    <input type="text" id="patient_age_dob" readonly>
                </div>
                <div class="form-field" id="patient_gender_field" style="display: none;">
                    <label>Gender</label>
                    <input type="text" id="patient_gender" readonly>
                </div>
                <!-- Doctor Field (Hidden for Lab Tests) -->
                <div class="form-field" id="doctor_field">
                    <label>Select Doctor <span class="req">*</span></label>
                    <select name="doctor_id" id="doctor_select" onchange="loadDoctorScheduleCalendar()">
                        <option value="">Choose a doctor...</option>
                        <?php if (isset($doctors) && is_array($doctors)): ?>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>" data-doctor-name="<?= htmlspecialchars($doctor['name']) ?>"><?= htmlspecialchars($doctor['name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="error" data-error-for="doctor_id"></div>
                    <small class="form-help">
                        <strong>Available Doctors:</strong>
                        <?php if (isset($doctors) && is_array($doctors)): ?>
                            <?php foreach ($doctors as $doctor): ?>
                                <a href="#" class="doctor-schedule-link" data-doctor-id="<?= $doctor['id'] ?>" style="margin-right: 10px; color: #3B82F6; text-decoration: underline;">
                                    <?= htmlspecialchars($doctor['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <br><em>Click on a doctor's name to view their full schedule</em>
                    </small>
                </div>
                <div class="form-field form-field--full" id="doctor_schedule_calendar_container" style="display: none;">
                    <label>Doctor's Schedule Calendar</label>
                    <div id="doctor_schedule_calendar" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1rem; margin-top: 0.5rem;">
                        <p style="color: #64748b; margin: 0; text-align: center;">Select a doctor to view their schedule</p>
                    </div>
                </div>
                <!-- Appointment type is now selected via buttons, hidden field stores the value -->
                <!-- Lab Test Fields (Hidden for Consultation) -->
                <div class="form-field form-field--full" id="lab_test_field" style="display: none;">
                    <label>Lab Test(s) <span class="req">*</span></label>
                    <button type="button" id="btnSelectLabTests" onclick="showLabTestModal()" style="width: 100%; padding: 0.75rem 1rem; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 0.5rem; cursor: pointer; text-align: left; color: #64748b; font-size: 0.875rem; transition: all 0.2s ease;">
                        <i class="fas fa-flask" style="margin-right: 0.5rem; color: #10b981;"></i>
                        <span id="lab_test_selected_text">Click to select lab test(s)...</span>
                        <i class="fas fa-chevron-right" style="float: right; margin-top: 0.25rem;"></i>
                    </button>
                    <input type="hidden" name="lab_test_type" id="lab_test_type_hidden" value="">
                    <div class="error" data-error-for="lab_test_type"></div>
                    <div id="selected_lab_tests_display" style="margin-top: 0.75rem; display: none;">
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;" id="selected_tests_chips"></div>
                    </div>
                </div>
                <div class="form-field">
                    <label>Appointment Date <span class="req">*</span></label>
                    <input type="date" name="appointment_date" id="appointment_date" required min="<?= date('Y-m-d') ?>" onchange="checkDoctorAvailability()">
                    <div class="error" data-error-for="appointment_date"></div>
                    <div id="date_availability_warning" style="display: none; color: #e53e3e; font-size: 0.875rem; margin-top: 0.25rem;">
                        <i class="fas fa-exclamation-triangle"></i> Selected doctor is not available on this date.
                    </div>
                    <div id="date_availability_info" style="display: none; color: #10b981; font-size: 0.875rem; margin-top: 0.25rem;">
                        <i class="fas fa-check-circle"></i> <span id="availability_times"></span>
                    </div>
                </div>
                <div class="form-field">
                    <label id="appointment_time_label">Appointment Time <span class="req">*</span></label>
                    <input type="time" name="appointment_time" id="appointment_time" required>
                    <div class="error" data-error-for="appointment_time"></div>
                </div>
                <div class="form-field">
                    <label>Status <span class="req">*</span></label>
                    <select name="status" required>
                        <option value="scheduled">Scheduled</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="pending">Pending</option>
                    </select>
                    <div class="error" data-error-for="status"></div>
                </div>
                <!-- Payment Status (For Lab Tests only) -->
                <div class="form-field" id="payment_status_field" style="display: none;">
                    <label>Payment Status</label>
                    <select name="payment_status" id="payment_status">
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="partially_paid">Partially Paid</option>
                    </select>
                    <div class="error" data-error-for="payment_status"></div>
                </div>
                <div class="form-field" id="room_field" style="display: none;">
                    <label>Select Room</label>
                    <select name="room_id" id="room_select">
                        <option value="">Choose a room...</option>
                    </select>
                    <div class="error" data-error-for="room_id"></div>
                    <small class="form-help" id="room_help_text">Select a room for inpatient appointment</small>
                </div>
                <!-- Notes (Consultation Notes - Hidden for Lab Tests) -->
                <div class="form-field form-field--full" id="consultation_notes_field">
                    <label>Consultation Notes</label>
                    <textarea name="notes" id="consultation_notes" rows="3" placeholder="Additional notes or instructions..."></textarea>
                    <div class="error" data-error-for="notes"></div>
                </div>
                <!-- Remarks/Notes (For Lab Tests) -->
                <div class="form-field form-field--full" id="lab_remarks_field" style="display: none;">
                    <label>Remarks / Notes</label>
                    <textarea name="lab_remarks" id="lab_remarks" rows="3" placeholder="Special instructions (e.g., fasting required, specimen collection time, etc.)..."></textarea>
                    <div class="error" data-error-for="lab_remarks"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" id="back_to_selection_btn" onclick="backToAppointmentTypeSelection()" style="display: none;">← Back</button>
                <button type="button" class="btn-secondary" onclick="closeAddAppointmentModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="submit_appointment_btn" style="display: none;">Add Appointment</button>
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

    // Hide room field by default (outpatients don't need rooms)
    resetRoomField();

    // Show selection step, hide form
    showAppointmentTypeSelection();
}

function closeAddAppointmentModal() {
    const modal = document.getElementById('addAppointmentModal');
    const form = document.getElementById('addAppointmentForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
    
    // Reset form fields
    resetRoomField();
    
    // Reset to selection step
    showAppointmentTypeSelection();
}

// Show appointment type selection buttons
function showAppointmentTypeSelection() {
    const selectionDiv = document.getElementById('appointment_type_selection');
    const formFields = document.getElementById('appointment_form_fields');
    const submitBtn = document.getElementById('submit_appointment_btn');
    const backBtn = document.getElementById('back_to_selection_btn');
    
    if (selectionDiv) selectionDiv.style.display = 'block';
    if (formFields) formFields.style.display = 'none';
    if (submitBtn) submitBtn.style.display = 'none';
    if (backBtn) backBtn.style.display = 'none';
    
    // Reset appointment type
    const hiddenField = document.getElementById('appointment_type_hidden');
    if (hiddenField) hiddenField.value = '';
}

// Select appointment type and show form
function selectAppointmentType(type) {
    const selectionDiv = document.getElementById('appointment_type_selection');
    const formFields = document.getElementById('appointment_form_fields');
    const submitBtn = document.getElementById('submit_appointment_btn');
    const backBtn = document.getElementById('back_to_selection_btn');
    const hiddenField = document.getElementById('appointment_type_hidden');
    
    // Set the appointment type
    if (hiddenField) hiddenField.value = type;
    
    // Hide selection, show form
    if (selectionDiv) selectionDiv.style.display = 'none';
    if (formFields) formFields.style.display = 'grid';
    if (submitBtn) submitBtn.style.display = 'inline-block';
    if (backBtn) backBtn.style.display = 'inline-block';
    
    // Toggle fields based on type
    toggleAppointmentFields();
    
    // Load rooms based on appointment type
    loadOPDRooms();
}

// Back to appointment type selection
function backToAppointmentTypeSelection() {
    showAppointmentTypeSelection();
}

// Lab Test Modal Functions
function showLabTestModal() {
    const modal = document.getElementById('labTestModal');
    if (modal) {
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
        // Restore previously selected tests
        restoreLabTestSelection();
    }
}

function closeLabTestModal() {
    const modal = document.getElementById('labTestModal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
}

function filterLabTests() {
    const searchTerm = document.getElementById('lab_test_search').value.toLowerCase();
    const options = document.querySelectorAll('.lab-test-option');
    const groups = document.querySelectorAll('.lab-test-group');
    
    let hasVisibleOptions = false;
    
    groups.forEach(group => {
        const groupOptions = group.querySelectorAll('.lab-test-option');
        let groupHasVisible = false;
        
        groupOptions.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                option.style.display = 'flex';
                groupHasVisible = true;
                hasVisibleOptions = true;
            } else {
                option.style.display = 'none';
            }
        });
        
        // Show/hide group based on visible options
        if (groupHasVisible) {
            group.classList.remove('hidden');
        } else {
            group.classList.add('hidden');
        }
    });
}

function updateSelectedLabTests() {
    // This function is called when checkboxes change
    // The actual update happens when modal is confirmed
}

function restoreLabTestSelection() {
    const hiddenField = document.getElementById('lab_test_type_hidden');
    if (!hiddenField || !hiddenField.value) return;
    
    const selectedTests = hiddenField.value.split(',').map(t => t.trim()).filter(t => t);
    selectedTests.forEach(testName => {
        const checkbox = document.querySelector(`#labTestModal input[type="checkbox"][value="${testName.replace(/"/g, '&quot;')}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}

function confirmLabTestSelection() {
    const checkboxes = document.querySelectorAll('#labTestModal input[type="checkbox"]:checked');
    const selectedTests = Array.from(checkboxes).map(cb => cb.value);
    const hiddenField = document.getElementById('lab_test_type_hidden');
    const selectedText = document.getElementById('lab_test_selected_text');
    const displayDiv = document.getElementById('selected_lab_tests_display');
    const chipsContainer = document.getElementById('selected_tests_chips');
    
    if (selectedTests.length === 0) {
        alert('Please select at least one lab test');
        return;
    }
    
    // Update hidden field (comma-separated values)
    if (hiddenField) {
        hiddenField.value = selectedTests.join(', ');
    }
    
    // Update button text
    if (selectedText) {
        if (selectedTests.length === 1) {
            selectedText.textContent = selectedTests[0];
        } else {
            selectedText.textContent = `${selectedTests.length} test(s) selected`;
        }
    }
    
    // Update button style
    const btn = document.getElementById('btnSelectLabTests');
    if (btn) {
        btn.style.background = '#d1fae5';
        btn.style.borderColor = '#10b981';
        btn.style.color = '#065f46';
    }
    
    // Display selected tests as chips
    if (displayDiv) displayDiv.style.display = 'block';
    if (chipsContainer) {
        chipsContainer.innerHTML = '';
        selectedTests.forEach(test => {
            const chip = document.createElement('span');
            chip.className = 'test-chip';
            chip.innerHTML = `${test} <span class="remove-chip" onclick="removeLabTest('${test.replace(/'/g, "\\'")}')">×</span>`;
            chipsContainer.appendChild(chip);
        });
    }
    
    closeLabTestModal();
}

function clearLabTestSelection() {
    const checkboxes = document.querySelectorAll('#labTestModal input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);
}

function removeLabTest(testName) {
    const hiddenField = document.getElementById('lab_test_type_hidden');
    if (!hiddenField) return;
    
    const currentTests = hiddenField.value.split(', ').filter(t => t && t !== testName);
    hiddenField.value = currentTests.join(', ');
    
    // Update display
    if (currentTests.length === 0) {
        const selectedText = document.getElementById('lab_test_selected_text');
        const displayDiv = document.getElementById('selected_lab_tests_display');
        const btn = document.getElementById('btnSelectLabTests');
        
        if (selectedText) selectedText.textContent = 'Click to select lab test(s)...';
        if (displayDiv) displayDiv.style.display = 'none';
        if (btn) {
            btn.style.background = '#f8fafc';
            btn.style.borderColor = '#cbd5e1';
            btn.style.color = '#64748b';
        }
    } else {
        // Re-confirm to update display
        const checkboxes = document.querySelectorAll('#labTestModal input[type="checkbox"]');
        checkboxes.forEach(cb => {
            cb.checked = currentTests.includes(cb.value);
        });
        confirmLabTestSelection();
    }
}

// Load Patient Details when patient is selected
function loadPatientDetails() {
    const select = document.getElementById('patient_select');
    const selectedOption = select.options[select.selectedIndex];
    const roomField = document.getElementById('room_field');
    const roomSelect = document.getElementById('room_select');

    if (selectedOption.value) {
        // Fill patient details
        document.getElementById('patient_name').value = selectedOption.getAttribute('data-name');
        document.getElementById('patient_contact').value = selectedOption.getAttribute('data-contact');

        // Check patient type and show/hide room field
        const patientType = selectedOption.getAttribute('data-patient-type') || 'outpatient';
        if (patientType.toLowerCase() === 'inpatient') {
            // Show room field for inpatients
            if (roomField) {
                roomField.style.display = 'block';
            }
            if (roomSelect) {
                roomSelect.setAttribute('required', 'required');
            }
            // Load inpatient rooms
            loadInpatientRooms();
        } else {
            // Hide room field for outpatients
            if (roomField) {
                roomField.style.display = 'none';
            }
            if (roomSelect) {
                roomSelect.removeAttribute('required');
                roomSelect.value = '';
            }
        }

        // Fill age/DOB and gender if available (for lab tests)
        const ageDob = selectedOption.getAttribute('data-age-dob');
        const gender = selectedOption.getAttribute('data-gender');
        if (ageDob) {
            document.getElementById('patient_age_dob').value = ageDob;
        }
        if (gender) {
            document.getElementById('patient_gender').value = gender;
        }
        
        // Load OPD rooms for outpatient appointments
        loadOPDRooms();
    } else {
        document.getElementById('patient_name').value = '';
        document.getElementById('patient_contact').value = '';
        document.getElementById('patient_age_dob').value = '';
        document.getElementById('patient_gender').value = '';
        resetRoomField();
    }
}

// Toggle fields based on appointment type
function toggleAppointmentFields() {
    const hiddenField = document.getElementById('appointment_type_hidden');
    if (!hiddenField) return;
    
    const appointmentType = hiddenField.value;
    const doctorField = document.getElementById('doctor_field');
    const doctorSelect = document.getElementById('doctor_select');
    const doctorScheduleContainer = document.getElementById('doctor_schedule_calendar_container');
    const labTestField = document.getElementById('lab_test_field');
    const labTestType = document.getElementById('lab_test_type');
    const paymentStatusField = document.getElementById('payment_status_field');
    const consultationNotesField = document.getElementById('consultation_notes_field');
    const labRemarksField = document.getElementById('lab_remarks_field');
    const patientAgeField = document.getElementById('patient_age_field');
    const patientGenderField = document.getElementById('patient_gender_field');
    const roomHelpText = document.getElementById('room_help_text');
    
    if (appointmentType === 'laboratory_test') {
        // Lab Test: Hide doctor, show lab fields
        if (doctorField) doctorField.style.display = 'none';
        if (doctorSelect) {
            doctorSelect.removeAttribute('required');
            doctorSelect.value = '';
        }
        if (doctorScheduleContainer) doctorScheduleContainer.style.display = 'none';
        
        if (labTestField) {
            labTestField.style.display = 'block';
            // Lab test is now handled via modal button
        }
        
        if (paymentStatusField) paymentStatusField.style.display = 'block';
        
        if (consultationNotesField) consultationNotesField.style.display = 'none';
        if (labRemarksField) labRemarksField.style.display = 'block';
        
        if (patientAgeField) patientAgeField.style.display = 'block';
        if (patientGenderField) patientGenderField.style.display = 'block';
        
        if (roomHelpText) {
            roomHelpText.textContent = 'Select a laboratory room for specimen collection';
        }
        
        // Update appointment time label for lab tests
        const timeLabel = document.getElementById('appointment_time_label');
        if (timeLabel) {
            timeLabel.innerHTML = 'Appointment Time / Slot <span class="req">*</span>';
        }
        
    } else {
        // Consultation: Show doctor, hide lab fields
        if (doctorField) doctorField.style.display = 'block';
        if (doctorSelect) doctorSelect.setAttribute('required', 'required');
        
        if (labTestField) labTestField.style.display = 'none';
        // Clear lab test selection when switching to consultation
        const hiddenField = document.getElementById('lab_test_type_hidden');
        const selectedText = document.getElementById('lab_test_selected_text');
        const displayDiv = document.getElementById('selected_lab_tests_display');
        const btn = document.getElementById('btnSelectLabTests');
        
        if (hiddenField) hiddenField.value = '';
        if (selectedText) selectedText.textContent = 'Click to select lab test(s)...';
        if (displayDiv) displayDiv.style.display = 'none';
        if (btn) {
            btn.style.background = '#f8fafc';
            btn.style.borderColor = '#cbd5e1';
            btn.style.color = '#64748b';
        }
        
        if (paymentStatusField) paymentStatusField.style.display = 'none';
        
        if (consultationNotesField) consultationNotesField.style.display = 'block';
        if (labRemarksField) labRemarksField.style.display = 'none';
        
        if (patientAgeField) patientAgeField.style.display = 'none';
        if (patientGenderField) patientGenderField.style.display = 'none';
        
        if (roomHelpText) {
            roomHelpText.textContent = 'Optional: Select an OPD clinic room for outpatient appointment';
        }
        
        // Reset appointment time label
        const timeLabel = document.getElementById('appointment_time_label');
        if (timeLabel) {
            timeLabel.innerHTML = 'Appointment Time <span class="req">*</span>';
        }
    }
    
    // Reload rooms based on appointment type
    loadOPDRooms();
}

// Load inpatient rooms
function loadInpatientRooms() {
    const roomSelect = document.getElementById('room_select');
    if (!roomSelect) return;
    
    // Reset room dropdown
    roomSelect.innerHTML = '<option value="">Loading rooms...</option>';
    
    // Fetch inpatient rooms from backend
    fetch(`<?= base_url('reception/rooms') ?>?type=inpatient`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.rooms && data.rooms.length > 0) {
                roomSelect.innerHTML = '<option value="">Choose a room...</option>';
                
                data.rooms.forEach(room => {
                    const available = room.capacity - room.current_occupancy;
                    const option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = `${room.room_number} - ${room.specialization} (Floor ${room.floor}, ${available} available)`;
                    roomSelect.appendChild(option);
                });
            } else {
                roomSelect.innerHTML = '<option value="">No rooms available</option>';
            }
        })
        .catch(error => {
            console.error('Error loading rooms:', error);
            roomSelect.innerHTML = '<option value="">Error loading rooms</option>';
        });
}

// Load OPD rooms for outpatient appointments based on appointment type (DEPRECATED - outpatients don't need rooms)
function loadOPDRooms() {
    // Outpatients don't need rooms anymore - this function is kept for backward compatibility
    const roomField = document.getElementById('room_field');
    if (roomField) {
        roomField.style.display = 'none';
    }
}

// Reset room field
function resetRoomField() {
    const roomField = document.getElementById('room_field');
    const roomSelect = document.getElementById('room_select');
    if (roomField) {
        roomField.style.display = 'none';
    }
    if (roomSelect) {
        roomSelect.innerHTML = '<option value="">Choose a room...</option>';
        roomSelect.removeAttribute('required');
        roomSelect.value = '';
    }
}

// Event listeners
document.getElementById('btnOpenAddAppointment').addEventListener('click', function(e) {
    e.preventDefault();
    showAddAppointmentModal();
});

// Reload rooms when appointment type changes (only for inpatients)
const appointmentTypeSelect = document.getElementById('appointment_type_select');
if (appointmentTypeSelect) {
    appointmentTypeSelect.addEventListener('change', function() {
        // Check if current patient is inpatient, if so reload rooms
        const patientSelect = document.getElementById('patient_select');
        if (patientSelect && patientSelect.value) {
            const selectedOption = patientSelect.options[patientSelect.selectedIndex];
            const patientType = selectedOption.getAttribute('data-patient-type') || 'outpatient';
            if (patientType.toLowerCase() === 'inpatient') {
                loadInpatientRooms();
            } else {
                // Outpatients don't need rooms
                resetRoomField();
            }
        }
    });
}

// Add appointment form submission
document.getElementById('addAppointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form values
    const patientId = document.querySelector('select[name="patient_id"]').value;
    const doctorId = document.querySelector('select[name="doctor_id"]').value;
    const appointmentDate = document.querySelector('input[name="appointment_date"]').value;
    const appointmentTime = document.querySelector('input[name="appointment_time"]').value;
    const appointmentType = document.getElementById('appointment_type_hidden')?.value || '';
    const roomId = document.querySelector('select[name="room_id"]').value;
    const labTestType = document.getElementById('lab_test_type');
    
    // Basic validation
    if (!patientId || !appointmentDate || !appointmentTime || !appointmentType) {
        alert('Please fill in all required fields');
        return false;
    }
    
    // For consultation: doctor is required
    if (appointmentType === 'consultation' && !doctorId) {
        alert('Please select a doctor for consultation appointments');
        return false;
    }
    
    // For lab test: lab test type is required
    if (appointmentType === 'laboratory_test') {
        const hiddenField = document.getElementById('lab_test_type_hidden');
        const selectedTests = hiddenField && hiddenField.value ? hiddenField.value.split(', ') : [];
        if (selectedTests.length === 0 || (selectedTests.length === 1 && selectedTests[0] === '')) {
            alert('Please select at least one lab test');
            return false;
        }
    }
    
    // Check if selected date is unavailable for the doctor
    if (appointmentDate && typeof isDateUnavailable === 'function' && isDateUnavailable(appointmentDate, currentDoctorUnavailableDays)) {
        alert('The selected doctor is not available on this date. Please choose a different date or doctor.');
        return false;
    }
    
    // Submit via AJAX
    const formData = new FormData(document.getElementById('addAppointmentForm'));
    
    fetch('<?= base_url('reception/createAppointment') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, read as text to see what we got
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Please check the console for details.');
            });
        }
    })
    .then(data => {
        if (data.status === 'success') {
            alert(data.message || 'Appointment created successfully');
            closeAddAppointmentModal();
            location.reload();
        } else {
            alert(data.message || 'Failed to create appointment');
            // Display field errors if any
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errorDiv = document.querySelector(`[data-error-for="${field}"]`);
                    if (errorDiv) {
                        errorDiv.textContent = data.errors[field];
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
    });
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

/* Doctor Schedule Modal Styles */
.doctor-schedule-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.doctor-schedule-content {
    background-color: white;
    margin: 5% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

.schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.schedule-day {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 15px;
}

.schedule-day h4 {
    margin: 0 0 10px 0;
    color: #2d3748;
    font-size: 1rem;
    font-weight: 600;
}

.schedule-slot {
    background-color: #f8fafc;
    padding: 8px 12px;
    border-radius: 4px;
    margin-bottom: 8px;
    border-left: 4px solid #48bb78;
}

.schedule-slot.unavailable {
    background-color: #fed7d7;
    border-left-color: #f56565;
}

.no-schedule {
    color: #a0aec0;
    font-style: italic;
    text-align: center;
    padding: 20px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e2e8f0;
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #a0aec0;
}

        .close-modal:hover {
            color: #2d3748;
        }

        /* Fix modal to make save button always visible */
        #addAppointmentModal .modal-dialog {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #addAppointmentModal .modal-body {
            overflow-y: auto;
            flex: 1;
            max-height: calc(90vh - 140px); /* Subtract header and footer height */
            padding-bottom: 20px;
        }

        #addAppointmentModal .modal-footer {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 16px 20px;
            margin-top: 0;
            z-index: 10;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
        }

        #addAppointmentModal .modal-header {
            flex-shrink: 0;
        }

        /* Reception Calendar Styles */
        .reception-calendar-container {
            background: white;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .reception-calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .reception-calendar-day-header {
            padding: 0.75rem;
            text-align: center;
            font-weight: 600;
            color: #475569;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .reception-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e2e8f0;
        }

        .reception-calendar-day {
            min-height: 80px;
            background: white;
            padding: 0.5rem;
            position: relative;
        }

        .reception-calendar-day.empty {
            background: #f8fafc;
        }

        .reception-calendar-day.today {
            background: #eff6ff;
            border: 2px solid #3b82f6;
        }

        .reception-calendar-day.today .reception-calendar-day-number {
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .reception-calendar-day-number {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
            font-size: 0.85rem;
        }

        .reception-calendar-day-schedules {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .reception-calendar-schedule-item {
            padding: 0.25rem 0.4rem;
            border-radius: 3px;
            font-size: 0.7rem;
            line-height: 1.2;
        }

        .reception-calendar-schedule-item.available {
            background: #dcfce7;
            color: #166534;
            border-left: 2px solid #22c55e;
        }

        .reception-calendar-schedule-item.unavailable {
            background: #fee2e2;
            color: #991b1b;
            border-left: 2px solid #ef4444;
        }

        .reception-schedule-time {
            font-weight: 500;
        }

        .reception-calendar-schedule-more {
            font-size: 0.65rem;
            color: #64748b;
            font-style: italic;
            padding: 0.2rem 0.4rem;
        }

        .reception-calendar-no-schedule {
            font-size: 0.65rem;
            color: #94a3b8;
            font-style: italic;
            padding: 0.2rem 0.4rem;
        }
</style>

<!-- Doctor Schedule Modal -->
<div id="doctorScheduleModal" class="doctor-schedule-modal">
    <div class="doctor-schedule-content">
        <div class="modal-header">
            <h2 id="doctorScheduleTitle">Doctor Schedule</h2>
            <button class="close-modal" onclick="closeDoctorScheduleModal()">&times;</button>
        </div>
        <div id="doctorScheduleContent">
            <div class="no-schedule">Loading schedule...</div>
        </div>
    </div>
</div>

<script>
// Doctor Schedule Modal Functions
function showDoctorSchedule(doctorId) {
    const modal = document.getElementById('doctorScheduleModal');
    const content = document.getElementById('doctorScheduleContent');
    const title = document.getElementById('doctorScheduleTitle');
    
    modal.style.display = 'block';
    content.innerHTML = '<div class="no-schedule">Loading schedule...</div>';
    title.textContent = 'Doctor Schedule';
    
    // Get current month for calendar
    const today = new Date();
    const currentMonth = today.getMonth() + 1;
    const currentYear = today.getFullYear();
    
    // Fetch doctor schedule with current month
    fetch(`<?= site_url('reception/getDoctorSchedule') ?>/${doctorId}?date=${currentYear}-${String(currentMonth).padStart(2, '0')}-01`)
        .then(response => response.json())
        .then(data => {
            console.log('Doctor schedule data:', data);
            if (data.status === 'success') {
                title.textContent = `${data.doctor.name}'s Schedule`;
                displayScheduleCalendar(data, currentMonth, currentYear);
            } else {
                content.innerHTML = `<div class="no-schedule">Error: ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="no-schedule">Failed to load schedule</div>';
        });
}

function displayScheduleCalendar(data, month, year) {
    const content = document.getElementById('doctorScheduleContent');
    
    // Calculate calendar
    const firstDay = new Date(year, month - 1, 1);
    const daysInMonth = new Date(year, month, 0).getDate();
    const startDay = firstDay.getDay(); // 0 = Sunday
    const startDayMonday = startDay === 0 ? 6 : startDay - 1; // Convert to Monday = 0
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    
    let html = `
        <div style="margin-bottom: 1rem; text-align: center;">
            <h3 style="margin: 0; color: #1e293b;">${monthNames[month - 1]} ${year}</h3>
        </div>
        <div class="reception-calendar-container">
            <div class="reception-calendar-header">
    `;
    
    dayNames.forEach(day => {
        html += `<div class="reception-calendar-day-header">${day}</div>`;
    });
    
    html += `</div><div class="reception-calendar-grid">`;
    
    // Empty cells before first day
    for (let i = 0; i < startDayMonday; i++) {
        html += `<div class="reception-calendar-day empty"></div>`;
    }
    
    // Calendar days
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month - 1, day);
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][date.getDay()];
        const isToday = date.toDateString() === today.toDateString();
        
        // Get schedules for this date
        let daySchedules = [];
        if (data.scheduleByDate && data.scheduleByDate[dateStr]) {
            daySchedules = data.scheduleByDate[dateStr];
        } else if (data.schedule && data.schedule[dayOfWeek]) {
            daySchedules = data.schedule[dayOfWeek];
        }
        
        html += `<div class="reception-calendar-day ${isToday ? 'today' : ''}">`;
        html += `<div class="reception-calendar-day-number">${day}</div>`;
        html += `<div class="reception-calendar-day-schedules">`;
        
        if (daySchedules.length > 0) {
            daySchedules.slice(0, 2).forEach(sched => {
                const availableClass = sched.is_available ? 'available' : 'unavailable';
                html += `<div class="reception-calendar-schedule-item ${availableClass}">`;
                html += `<div class="reception-schedule-time">${sched.start_time} - ${sched.end_time}</div>`;
                html += `</div>`;
            });
            if (daySchedules.length > 2) {
                html += `<div class="reception-calendar-schedule-more">+${daySchedules.length - 2}</div>`;
            }
        } else {
            html += `<div class="reception-calendar-no-schedule">No schedule</div>`;
        }
        
        html += `</div></div>`;
    }
    
    html += `</div></div>`;
    content.innerHTML = html;
}

function loadDoctorScheduleCalendar() {
    const doctorSelect = document.getElementById('doctor_select');
    const container = document.getElementById('doctor_schedule_calendar_container');
    const calendarDiv = document.getElementById('doctor_schedule_calendar');
    
    if (!doctorSelect || !doctorSelect.value) {
        container.style.display = 'none';
        return;
    }
    
    const doctorId = doctorSelect.value;
    const doctorName = doctorSelect.options[doctorSelect.selectedIndex].getAttribute('data-doctor-name');
    const appointmentDate = document.getElementById('appointment_date')?.value;
    
    container.style.display = 'block';
    calendarDiv.innerHTML = '<p style="color: #64748b; margin: 0; text-align: center;">Loading schedule...</p>';
    
    // Get month from appointment date or use current month
    let currentMonth, currentYear;
    if (appointmentDate) {
        const dateObj = new Date(appointmentDate + 'T00:00:00');
        currentMonth = dateObj.getMonth() + 1;
        currentYear = dateObj.getFullYear();
    } else {
        const today = new Date();
        currentMonth = today.getMonth() + 1;
        currentYear = today.getFullYear();
    }
    const dateParam = appointmentDate || `${currentYear}-${String(currentMonth).padStart(2, '0')}-01`;
    
    fetch(`<?= site_url('reception/getDoctorSchedule') ?>/${doctorId}?date=${dateParam}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayDoctorScheduleInForm(data, currentMonth, currentYear, doctorName);
                checkDoctorAvailability();
            } else {
                calendarDiv.innerHTML = `<p style="color: #e53e3e; margin: 0;">Error: ${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            calendarDiv.innerHTML = '<p style="color: #e53e3e; margin: 0;">Failed to load schedule</p>';
        });
}

function displayDoctorScheduleInForm(data, month, year, doctorName) {
    const calendarDiv = document.getElementById('doctor_schedule_calendar');
    
    const firstDay = new Date(year, month - 1, 1);
    const daysInMonth = new Date(year, month, 0).getDate();
    const startDay = firstDay.getDay();
    const startDayMonday = startDay === 0 ? 6 : startDay - 1;
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    
    let html = `
        <div style="margin-bottom: 0.75rem;">
            <strong style="color: #1e293b;">${doctorName}'s Schedule - ${monthNames[month - 1]} ${year}</strong>
        </div>
        <div class="reception-calendar-container">
            <div class="reception-calendar-header">
    `;
    
    dayNames.forEach(day => {
        html += `<div class="reception-calendar-day-header">${day}</div>`;
    });
    
    html += `</div><div class="reception-calendar-grid">`;
    
    for (let i = 0; i < startDayMonday; i++) {
        html += `<div class="reception-calendar-day empty"></div>`;
    }
    
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month - 1, day);
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][date.getDay()];
        const isToday = date.toDateString() === today.toDateString();
        
        let daySchedules = [];
        if (data.scheduleByDate && data.scheduleByDate[dateStr]) {
            daySchedules = data.scheduleByDate[dateStr];
        } else if (data.schedule && data.schedule[dayOfWeek]) {
            daySchedules = data.schedule[dayOfWeek];
        }
        
        html += `<div class="reception-calendar-day ${isToday ? 'today' : ''}">`;
        html += `<div class="reception-calendar-day-number">${day}</div>`;
        html += `<div class="reception-calendar-day-schedules">`;
        
        if (daySchedules.length > 0) {
            daySchedules.slice(0, 1).forEach(sched => {
                const availableClass = sched.is_available ? 'available' : 'unavailable';
                html += `<div class="reception-calendar-schedule-item ${availableClass}">`;
                html += `<div class="reception-schedule-time">${sched.start_time} - ${sched.end_time}</div>`;
                html += `</div>`;
            });
        } else {
            html += `<div class="reception-calendar-no-schedule">—</div>`;
        }
        
        html += `</div></div>`;
    }
    
    html += `</div></div>`;
    calendarDiv.innerHTML = html;
}

function checkDoctorAvailability() {
    const doctorId = document.getElementById('doctor_select')?.value;
    const appointmentDate = document.getElementById('appointment_date')?.value;
    const warning = document.getElementById('date_availability_warning');
    const info = document.getElementById('date_availability_info');
    const availabilityTimes = document.getElementById('availability_times');
    
    if (!doctorId || !appointmentDate) {
        if (warning) warning.style.display = 'none';
        if (info) info.style.display = 'none';
        return;
    }
    
    fetch(`<?= site_url('reception/getDoctorSchedule') ?>/${doctorId}?date=${appointmentDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.selectedDateAvailability && data.selectedDateAvailability.length > 0) {
                // Doctor is available
                if (warning) warning.style.display = 'none';
                if (info) {
                    const times = data.selectedDateAvailability.map(s => `${s.start_time} - ${s.end_time}`).join(', ');
                    availabilityTimes.textContent = `Available: ${times}`;
                    info.style.display = 'block';
                }
            } else {
                // Doctor is not available
                if (warning) warning.style.display = 'block';
                if (info) info.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function closeDoctorScheduleModal() {
    document.getElementById('doctorScheduleModal').style.display = 'none';
}

// Doctor availability tracking
let currentDoctorUnavailableDays = [];

// Check if a date falls on an unavailable day
function isDateUnavailable(dateString, unavailableDays) {
    if (!dateString || unavailableDays.length === 0) return false;
    
    const date = new Date(dateString);
    const dayOfWeek = date.getDay(); // 0=Sunday, 1=Monday, etc.
    
    return unavailableDays.includes(dayOfWeek);
}

// Update date availability warning
function updateDateAvailabilityWarning() {
    const dateInput = document.getElementById('appointment_date');
    const warning = document.getElementById('date_availability_warning');
    
    if (!dateInput || !warning) return;
    
    const selectedDate = dateInput.value;
    if (selectedDate && isDateUnavailable(selectedDate, currentDoctorUnavailableDays)) {
        warning.style.display = 'block';
        dateInput.style.borderColor = '#e53e3e';
    } else {
        warning.style.display = 'none';
        dateInput.style.borderColor = '';
    }
}

// Load doctor unavailable dates
function loadDoctorAvailability(doctorId) {
    if (!doctorId) {
        currentDoctorUnavailableDays = [];
        updateDateAvailabilityWarning();
        return;
    }
    
    fetch(`<?= site_url('reception/getDoctorUnavailableDates') ?>/${doctorId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Doctor unavailable days:', data);
            if (data.status === 'success') {
                currentDoctorUnavailableDays = data.unavailable_days || [];
                updateDateAvailabilityWarning();
            } else {
                console.error('Error loading doctor availability:', data.message);
                currentDoctorUnavailableDays = [];
            }
        })
        .catch(error => {
            console.error('Error:', error);
            currentDoctorUnavailableDays = [];
        });
}

// Add click event listeners to doctor links
document.addEventListener('DOMContentLoaded', function() {
    const doctorLinks = document.querySelectorAll('.doctor-schedule-link');
    doctorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const doctorId = this.getAttribute('data-doctor-id');
            showDoctorSchedule(doctorId);
        });
    });
    
    // Doctor selection change handler
    const doctorSelect = document.querySelector('select[name="doctor_id"]');
    if (doctorSelect) {
        doctorSelect.addEventListener('change', function() {
            const doctorId = this.value;
            loadDoctorAvailability(doctorId);
        });
    }
    
    // Date selection change handler
    const dateInput = document.getElementById('appointment_date');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            updateDateAvailabilityWarning();
            checkDoctorAvailability();
            // Reload calendar if doctor is selected
            const doctorSelect = document.getElementById('doctor_select');
            if (doctorSelect && doctorSelect.value) {
                loadDoctorScheduleCalendar();
            }
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('doctorScheduleModal');
        if (event.target === modal) {
            closeDoctorScheduleModal();
        }
    });
});
</script>

<?= $this->endSection() ?>
