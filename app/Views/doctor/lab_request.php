<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<!-- Header Section -->
<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🔬</span>
                    Lab Test Requests
                </h2>
                <p class="page-subtitle">
                    Welcome, <?= esc($user_name ?? 'Dr. ' . session()->get('name') ?? 'Doctor') ?>, M.D.
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- New Lab Request Form Section -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>📋 New Lab Test Request</h2>
    </header>
    
    <div class="stack">
        <div class="card prescription-form-card">
            <form id="createLabRequestForm">
                <!-- Patient Information -->
                <div class="form-section">
                    <h3 class="section-title">Patient Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Select Patient <span class="req">*</span></label>
                            <select id="lab_patient_id" name="patient_id" class="form-input" required>
                                <option value="">Select patient...</option>
                                <?php foreach (($patients ?? []) as $pt): ?>
                                    <option value="<?= (int) $pt['id'] ?>" 
                                            data-age="<?= esc($pt['age'] ?? '') ?>"
                                            data-gender="<?= esc($pt['gender'] ?? '') ?>"
                                            data-name="<?= esc($pt['full_name']) ?>"
                                            data-patient-type="<?= esc(strtolower($pt['patient_type'] ?? 'outpatient')) ?>">
                                        <?= esc($pt['full_name']) ?>
                                        <?php if (!empty($pt['patient_id'])): ?>
                                            (ID: <?= esc($pt['patient_id']) ?>)
                                        <?php endif; ?>
                                        <?php if (!empty($pt['age']) && $pt['age'] !== '—'): ?>
                                            - Age: <?= esc($pt['age']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Age</label>
                            <input type="text" id="lab_patient_age" class="form-input" readonly>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <input type="text" id="lab_patient_gender" class="form-input" readonly>
                        </div>
                    </div>
                </div>

                <!-- Test Information -->
                <div class="form-section">
                    <h3 class="section-title">Test Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Test Type <span class="req">*</span></label>
                            <select id="test_type" name="test_type" class="form-input" required>
                                <option value="">Select test type...</option>
                                <optgroup label="Blood Tests">
                                    <option value="Complete Blood Count (CBC)">Complete Blood Count (CBC)</option>
                                    <option value="Blood Glucose">Blood Glucose</option>
                                    <option value="Lipid Profile">Lipid Profile</option>
                                    <option value="Liver Function Test (LFT)">Liver Function Test (LFT)</option>
                                    <option value="Kidney Function Test (KFT)">Kidney Function Test (KFT)</option>
                                    <option value="Thyroid Function Test">Thyroid Function Test</option>
                                    <option value="Hemoglobin A1C">Hemoglobin A1C</option>
                                    <option value="Blood Culture">Blood Culture</option>
                                    <option value="Blood Typing">Blood Typing</option>
                                    <option value="Coagulation Profile">Coagulation Profile</option>
                                </optgroup>
                                <optgroup label="Urine Tests">
                                    <option value="Urine Analysis">Urine Analysis</option>
                                    <option value="Urine Culture">Urine Culture</option>
                                    <option value="24-Hour Urine Collection">24-Hour Urine Collection</option>
                                    <option value="Urine Pregnancy Test">Urine Pregnancy Test</option>
                                </optgroup>
                                <optgroup label="Imaging Tests">
                                    <option value="X-Ray">X-Ray</option>
                                    <option value="CT Scan">CT Scan</option>
                                    <option value="MRI">MRI</option>
                                    <option value="Ultrasound">Ultrasound</option>
                                    <option value="Echocardiogram">Echocardiogram</option>
                                    <option value="Mammography">Mammography</option>
                                </optgroup>
                                <optgroup label="Microbiology">
                                    <option value="Sputum Culture">Sputum Culture</option>
                                    <option value="Stool Culture">Stool Culture</option>
                                    <option value="Throat Swab">Throat Swab</option>
                                    <option value="Wound Culture">Wound Culture</option>
                                </optgroup>
                                <optgroup label="Other Tests">
                                    <option value="ECG (Electrocardiogram)">ECG (Electrocardiogram)</option>
                                    <option value="Pulmonary Function Test">Pulmonary Function Test</option>
                                    <option value="Bone Density Scan">Bone Density Scan</option>
                                    <option value="Pap Smear">Pap Smear</option>
                                    <option value="Biopsy">Biopsy</option>
                                    <option value="Other">Other (Specify in Notes)</option>
                                </optgroup>
                            </select>
                            <div id="test_info" style="margin-top: 8px; padding: 8px; background: #f0f9ff; border-radius: 4px; display: none;">
                                <div id="test_price" style="font-weight: 600; color: #10b981; margin-bottom: 4px;"></div>
                                <div id="test_specimen" style="font-size: 12px; color: #666;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <select id="priority" name="priority" class="form-input">
                                <option value="normal" selected>Normal</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row" id="nurse_selection_row" style="display: none;">
                        <div class="form-group full-width">
                            <label>Assign Nurse for Specimen Collection <span class="req">*</span></label>
                            <select id="assigned_nurse_id" name="assigned_nurse_id" class="form-input" required>
                                <option value="">Select nurse...</option>
                                <?php if (!empty($nurses)): ?>
                                    <?php foreach ($nurses as $nurse): ?>
                                        <option value="<?= (int) $nurse['id'] ?>">
                                            <?= esc($nurse['name']) ?>
                                            <?php if (!empty($nurse['email'])): ?>
                                                (<?= esc($nurse['email']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small style="color: #666; display: block; margin-top: 4px;">
                                Select which nurse will collect the specimen for this test
                            </small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Notes / Instructions (Optional)</label>
                            <textarea id="notes" name="notes" class="form-input" rows="3" 
                                      placeholder="Additional notes or special instructions for the lab staff..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="lab-form-actions">
                    <button type="reset" class="lab-btn lab-btn-secondary">Clear Form</button>
                    <button type="submit" class="lab-btn lab-btn-primary">
                        <span>📤</span> Create Lab Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- My Lab Requests Table -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>📊 My Lab Test Requests</h2>
    </header>
    
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Patient</th>
                        <th>Patient Type</th>
                        <th>Test Type</th>
                        <th>Price</th>
                        <th>Specimen</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Requested Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $request): ?>
                            <?php
                            $priority = $request['priority'] ?? 'normal';
                            $status = $request['status'] ?? 'pending';
                            
                            $priorityClass = match($priority) {
                                'low' => 'bg-secondary',
                                'normal' => 'bg-info',
                                'high' => 'bg-warning',
                                'critical' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            
                            $statusClass = match($status) {
                                'pending' => 'bg-warning',
                                'sent_to_lab' => 'bg-primary', // Sent to lab by nurse
                                'in_progress' => 'bg-info',
                                'completed' => 'bg-success',
                                'cancelled' => 'bg-secondary',
                                default => 'bg-secondary'
                            };
                            
                            // Format status text for display
                            $statusText = match($status) {
                                'pending' => 'Pending (Nurse Review)',
                                'sent_to_lab' => 'Sent to Lab',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                default => ucfirst(str_replace('_', ' ', $status))
                            };
                            ?>
                            <tr>
                                <td><strong>#<?= str_pad((string)($request['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= esc($request['patient_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php
                                    // Determine patient type: if admission_id exists = INPATIENT, else = OUTPATIENT
                                    $hasAdmission = !empty($request['admission_id']);
                                    $patientType = $hasAdmission ? 'inpatient' : 'outpatient';
                                    // Fallback to patient_type from patients table if available
                                    if (!empty($request['patient_type'])) {
                                        $patientType = strtolower($request['patient_type']);
                                    }
                                    $patientTypeClass = ($patientType === 'inpatient') ? 'bg-primary' : 'bg-info';
                                    ?>
                                    <span class="badge <?= $patientTypeClass ?>"><?= ucfirst($patientType) ?></span>
                                </td>
                                <td><?= esc($request['test_type'] ?? '—') ?></td>
                                <td>
                                    <strong style="color: #10b981;">₱<?= number_format((float)($request['price'] ?? 0.00), 2) ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $requiresSpecimen = (int)($request['requires_specimen'] ?? 0);
                                    if ($requiresSpecimen === 1): 
                                    ?>
                                        <span class="badge bg-warning" style="padding: 4px 8px; border-radius: 4px; font-size: 11px; background: #f59e0b; color: white;">
                                            🧪 Requires Specimen
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 11px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $priorityClass ?>"><?= ucfirst($priority) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?>" title="<?= esc($statusText) ?>">
                                        <?= esc($statusText) ?>
                                    </span>
                                    <?php if ($status === 'sent_to_lab' && !empty($request['sent_at'])): ?>
                                        <br><small style="color: #666; font-size: 10px;">
                                            Sent: <?= date('M j, g:i A', strtotime($request['sent_at'])) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                                <?php $latestResult = $request['latest_result'] ?? null; ?>
                                <td>
                                    <?php if (!empty($latestResult)): ?>
                                        <?php
                                            $resultSummary = $latestResult['result_summary'] ?? '—';
                                            $resultDetails = $latestResult['detailed_report_path'] ?? '';
                                            $releasedDate = !empty($latestResult['released_at']) ? date('M j, Y g:i A', strtotime($latestResult['released_at'])) : '—';
                                            $criticalFlag = (int)($latestResult['critical_flag'] ?? 0);
                                            $resultStatus = ucfirst($latestResult['status'] ?? 'completed');
                                        ?>
                                        <button type="button"
                                            class="lab-btn lab-btn-outline"
                                            data-result-button="true"
                                            data-result-test="<?= esc($request['test_type'] ?? '—', 'attr') ?>"
                                            data-result-patient="<?= esc($request['patient_name'] ?? '—', 'attr') ?>"
                                            data-result-status="<?= esc($resultStatus, 'attr') ?>"
                                            data-result-date="<?= esc($releasedDate, 'attr') ?>"
                                            data-result-summary="<?= esc($resultSummary, 'attr') ?>"
                                            data-result-detail="<?= esc($resultDetails, 'attr') ?>"
                                            data-result-critical="<?= $criticalFlag ?>"
                                            onclick="openDoctorResultModal(this)">
                                            View Result
                                        </button>
                                    <?php elseif ($status === 'completed'): ?>
                                        <span class="text-muted small">Awaiting release</span>
                                    <?php else: ?>
                                        <span class="text-muted small">Processing</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No lab test requests found. Create your first request above.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all elements first
    const patientSelect = document.getElementById('lab_patient_id');
    const ageInput = document.getElementById('lab_patient_age');
    const genderInput = document.getElementById('lab_patient_gender');
    const testTypeSelect = document.getElementById('test_type');
    const testInfoDiv = document.getElementById('test_info');
    const testPriceDiv = document.getElementById('test_price');
    const testSpecimenDiv = document.getElementById('test_specimen');
    const nurseSelectionRow = document.getElementById('nurse_selection_row');
    const assignedNurseSelect = document.getElementById('assigned_nurse_id');
    
    // Function to check if nurse selection should be shown
    function checkNurseSelection() {
        const testType = testTypeSelect ? testTypeSelect.value : '';
        const patientType = patientSelect ? patientSelect.options[patientSelect.selectedIndex]?.getAttribute('data-patient-type') : '';
        const isInpatient = patientType && patientType.toLowerCase() === 'inpatient';
        
        // Show nurse selection if:
        // 1. Test requires specimen, OR
        // 2. Patient is inpatient
        if (nurseSelectionRow && assignedNurseSelect) {
            const testRequiresSpecimen = testSpecimenDiv && testSpecimenDiv.textContent.includes('Requires specimen');
            
            if (testRequiresSpecimen || isInpatient) {
                nurseSelectionRow.style.display = 'block';
                assignedNurseSelect.setAttribute('required', 'required');
            } else {
                nurseSelectionRow.style.display = 'none';
                assignedNurseSelect.removeAttribute('required');
                assignedNurseSelect.value = '';
            }
        }
    }
    
    if (patientSelect) {
        patientSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                ageInput.value = selectedOption.getAttribute('data-age') || '—';
                genderInput.value = selectedOption.getAttribute('data-gender') || '—';
                // Check if nurse selection is needed
                checkNurseSelection();
            } else {
                ageInput.value = '';
                genderInput.value = '';
                if (nurseSelectionRow) nurseSelectionRow.style.display = 'none';
            }
        });
    }
    
    // Update test info when test type is selected
    if (testTypeSelect && testInfoDiv) {
        testTypeSelect.addEventListener('change', async function() {
            const testType = this.value;
            if (!testType) {
                testInfoDiv.style.display = 'none';
                checkNurseSelection();
                return;
            }
            
            // Fetch test info from server
            try {
                const response = await fetch('<?= base_url('doctor/getTestInfo') ?>?test_type=' + encodeURIComponent(testType));
                const data = await response.json();
                
                if (data.success && data.test) {
                    testPriceDiv.textContent = 'Price: ₱' + parseFloat(data.test.price || 0).toFixed(2);
                    if (data.test.requires_specimen == 1) {
                        testSpecimenDiv.innerHTML = '<span style="color: #f59e0b;">⚠️ Requires specimen collection by nurse</span>';
                    } else {
                        testSpecimenDiv.innerHTML = '<span style="color: #10b981;">✓ No specimen required</span>';
                    }
                    testInfoDiv.style.display = 'block';
                    
                    // Check if nurse selection should be shown (consider both test and patient type)
                    checkNurseSelection();
                } else {
                    testInfoDiv.style.display = 'none';
                    checkNurseSelection();
                }
            } catch (error) {
                console.error('Error fetching test info:', error);
                testInfoDiv.style.display = 'none';
                checkNurseSelection();
            }
        });
    }
    
    // Handle form submission
    const form = document.getElementById('createLabRequestForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch('<?= base_url('doctor/createLabRequest') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message || 'Lab test request created successfully!');
                    // Reset form
                    form.reset();
                    ageInput.value = '';
                    genderInput.value = '';
                    testInfoDiv.style.display = 'none';
                    // Reload page to show new request
                    window.location.reload();
                } else {
                    alert(result.message || 'Error creating request. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
});
</script>

<!-- View Result Modal -->
<div class="modal" id="doctorResultModal" style="display: none;">
    <div class="doctor-result-dialog">
        <div class="doctor-result-card">
            <div class="doctor-result-header">
                <div>
                    <h3>Lab Result Details</h3>
                    <p id="doc_result_status">Status —</p>
                </div>
                <button type="button" class="modal-close" onclick="closeDoctorResultModal()" aria-label="Close">&times;</button>
            </div>
            <div class="doctor-result-info">
                <div class="info-row">
                    <span>Patient</span>
                    <strong id="doc_result_patient">—</strong>
                </div>
                <div class="info-row">
                    <span>Test Type</span>
                    <strong id="doc_result_test">—</strong>
                </div>
                <div class="info-row">
                    <span>Released Date</span>
                    <strong id="doc_result_date">—</strong>
                </div>
                <div class="info-row">
                    <span>Critical Flag</span>
                    <strong id="doc_result_critical">No</strong>
                </div>
            </div>
            <div class="doctor-result-body">
                <div class="result-block">
                    <h4>Result Summary</h4>
                    <p id="doc_result_summary">—</p>
                </div>
                <div class="result-block">
                    <h4>Detailed Report</h4>
                    <p id="doc_result_detail">—</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openDoctorResultModal(button) {
    if (!button) return;
    const modal = document.getElementById('doctorResultModal');
    if (!modal) return;
    
    const testType = button.getAttribute('data-result-test') || '—';
    const patient = button.getAttribute('data-result-patient') || '—';
    const status = button.getAttribute('data-result-status') || '—';
    const date = button.getAttribute('data-result-date') || '—';
    const summary = button.getAttribute('data-result-summary') || '—';
    const detail = button.getAttribute('data-result-detail') || '—';
    const critical = parseInt(button.getAttribute('data-result-critical') || '0', 10) === 1;
    
    document.getElementById('doc_result_test').textContent = testType;
    document.getElementById('doc_result_patient').textContent = patient;
    document.getElementById('doc_result_status').textContent = status;
    document.getElementById('doc_result_date').textContent = date;
    document.getElementById('doc_result_summary').textContent = summary || '—';
    document.getElementById('doc_result_detail').textContent = detail || 'No additional details provided.';
    document.getElementById('doc_result_critical').textContent = critical ? 'Yes – Critical' : 'No';
    document.getElementById('doc_result_critical').className = critical ? 'text-critical' : '';
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDoctorResultModal() {
    const modal = document.getElementById('doctorResultModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const resultModal = document.getElementById('doctorResultModal');
    if (resultModal) {
        resultModal.addEventListener('click', function(e) {
            if (e.target === resultModal) {
                closeDoctorResultModal();
            }
        });
    }
});
</script>

<?= $this->endSection() ?>

