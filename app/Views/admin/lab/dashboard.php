<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🧪</span>
                    Laboratory Dashboard
                </h2>
                <p class="page-subtitle">
                    Monitor lab performance and critical alerts across all branches
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
            <div>
                <button id="btnOpenWalkInModal" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <span>➕</span>
                    Create Walk-In Request
                </button>
            </div>
        </div>
    </header>
    <div class="stack">
        <?php if (!empty($loadError)): ?>
            <div class="alert alert-warning">
                <?= esc($loadError) ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Test Requests</div>
                <div class="kpi-value"><?= number_format($metrics['pendingRequests'] ?? 0) ?></div>
                <div class="kpi-change kpi-warning">Awaiting action</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Completed Tests Today</div>
                <div class="kpi-value"><?= number_format($metrics['completedToday'] ?? 0) ?></div>
                <div class="kpi-change kpi-positive">Today</div>
            </div>
        </div>
        <div class="kpi-card kpi-critical">
            <div class="kpi-content">
                <div class="kpi-label">Critical Results</div>
                <div class="kpi-value"><?= number_format($metrics['criticalResults'] ?? 0) ?></div>
                <div class="kpi-change kpi-negative">Requires attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Active Lab Staff</div>
                <div class="kpi-value"><?= number_format($metrics['activeStaff'] ?? 0) ?></div>
                <div class="kpi-change kpi-positive">On duty</div>
            </div>
        </div>
    </div>
</section>

<!-- Walk-In Lab Tests Section -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Walk In - Lab Tests</h2>
        <p style="margin-top: 0.5rem; color: #64748b;">Manage lab test requests for walk-in patients (without doctor consultation)</p>
    </header>
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>Walk-In Lab Requests</span>
                <span><?= count($walkInRequests ?? []) ?> total</span>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Contact</th>
                        <th>Test Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $hasData = !empty($walkInRequests) || !empty($labAppointments);
                    ?>
                    <?php if (!empty($walkInRequests)): ?>
                        <?php foreach ($walkInRequests as $request): ?>
                            <tr>
                                <td>REQ-<?= str_pad((string)($request['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></td>
                                <td><?= !empty($request['requested_at']) ? date('M d, Y H:i', strtotime($request['requested_at'])) : '—' ?></td>
                                <td><?= esc($request['patient_name'] ?? 'Unknown') ?></td>
                                <td><?= esc($request['patient_contact'] ?? '—') ?></td>
                                <td><?= esc($request['test_type'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-<?= ($request['priority'] ?? 'normal') === 'critical' ? 'danger' : (($request['priority'] ?? 'normal') === 'high' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($request['priority'] ?? 'normal') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= ($request['status'] ?? 'pending') === 'completed' ? 'success' : (($request['status'] ?? 'pending') === 'in_progress' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst(str_replace('_', ' ', $request['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= esc($request['notes'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($labAppointments)): ?>
                        <?php foreach ($labAppointments as $appointment): ?>
                            <tr>
                                <td>APT-<?= str_pad((string)($appointment['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></td>
                                <td><?= !empty($appointment['appointment_date']) ? date('M d, Y', strtotime($appointment['appointment_date'])) . ' ' . (!empty($appointment['appointment_time']) ? date('H:i', strtotime($appointment['appointment_time'])) : '') : '—' ?></td>
                                <td><?= esc($appointment['patient_name'] ?? 'Unknown') ?></td>
                                <td><?= esc($appointment['patient_contact'] ?? '—') ?></td>
                                <td><?= esc($appointment['notes'] ?? 'Lab Test Appointment') ?></td>
                                <td>
                                    <span class="badge badge-info">Normal</span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= ($appointment['status'] ?? 'scheduled') === 'completed' ? 'success' : (($appointment['status'] ?? 'scheduled') === 'confirmed' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($appointment['status'] ?? 'scheduled') ?>
                                    </span>
                                </td>
                                <td><?= esc($appointment['notes'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!$hasData): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
                                <p>No walk-in lab requests found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Test Requests</h2>
        <a href="#" onclick="event.preventDefault(); openAllRequestsModal();" style="color: #4299e1; text-decoration: none; font-weight: 500; cursor: pointer;">View all</a>
    </header>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Requesting Doctor</th>
                    <th>Test Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date Requested</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentRequests)): ?>
                    <?php foreach (array_slice($recentRequests, 0, 8) as $request): ?>
                        <tr>
                            <td><?= esc($request['patient_name'] ?? 'N/A') ?></td>
                            <td><?= esc($request['doctor_name'] ?? 'N/A') ?></td>
                            <td><?= esc($request['test_type'] ?? '—') ?></td>
                            <td><span class="badge badge-priority badge-<?= esc($request['priority']) ?>"><?= ucfirst($request['priority'] ?? 'normal') ?></span></td>
                            <td><span class="badge badge-status badge-<?= esc($request['status']) ?>"><?= ucfirst(str_replace('_', ' ', $request['status'] ?? 'pending')) ?></span></td>
                            <td><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No recent requests.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Test Results</h2>
        <a href="#" onclick="event.preventDefault(); openAllResultsModal();" style="color: #4299e1; text-decoration: none; font-weight: 500; cursor: pointer;">View all</a>
    </header>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Test Type</th>
                    <th>Result Summary</th>
                    <th>Released</th>
                    <th>Critical</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentResults)): ?>
                    <?php foreach (array_slice($recentResults, 0, 8) as $result): ?>
                        <tr>
                            <td><?= esc($result['patient_name'] ?? 'N/A') ?></td>
                            <td><?= esc($result['test_type'] ?? '—') ?></td>
                            <td><?= esc($result['result_summary'] ?? '—') ?></td>
                            <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : '—' ?></td>
                            <td><?= !empty($result['critical_flag']) ? '<span class="badge badge-critical">Yes</span>' : 'No' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No recent results.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- All Test Requests Modal -->
<div id="allRequestsModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeAllRequestsModal()"></div>
    <div class="modal-dialog" style="max-width: 1200px;">
        <div class="modal-header">
            <h3>All Test Requests</h3>
            <button class="modal-close" onclick="closeAllRequestsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Requesting Doctor</th>
                            <th>Test Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date Requested</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentRequests)): ?>
                            <?php foreach ($recentRequests as $request): ?>
                                <tr>
                                    <td><?= esc($request['patient_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($request['doctor_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($request['test_type'] ?? '—') ?></td>
                                    <td><span class="badge badge-priority badge-<?= esc($request['priority']) ?>"><?= ucfirst($request['priority'] ?? 'normal') ?></span></td>
                                    <td><span class="badge badge-status badge-<?= esc($request['status']) ?>"><?= ucfirst(str_replace('_', ' ', $request['status'] ?? 'pending')) ?></span></td>
                                    <td><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- All Test Results Modal -->
<div id="allResultsModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeAllResultsModal()"></div>
    <div class="modal-dialog lab-detail-modal" style="max-width: 1100px;">
        <div class="modal-header">
            <div>
                <h3>All Test Results</h3>
                <p class="text-muted" style="margin: 4px 0 0;">Full details of every result entered by laboratory staff</p>
            </div>
            <button class="modal-close" onclick="closeAllResultsModal()">&times;</button>
        </div>
        <div class="modal-body lab-detail-modal-body">
            <?php if (!empty($recentResults)): ?>
                <div class="lab-result-detail-list">
                    <?php foreach ($recentResults as $result): ?>
                        <?php
                            $status = $result['status'] ?? 'pending';
                            $isCritical = !empty($result['critical_flag']);
                            $statusLabel = ucfirst(str_replace('_', ' ', $status));
                            $statusClass = match($status) {
                                'completed' => 'badge-success',
                                'released' => 'badge-success',
                                'pending' => 'badge-warning',
                                default => 'badge-secondary'
                            };
                        ?>
                        <article class="lab-result-detail-card">
                            <header class="lab-result-detail-card-head">
                                <div>
                                    <h4><?= esc($result['patient_name'] ?? 'Unknown Patient') ?></h4>
                                    <p>
                                        <?= esc($result['test_type'] ?? '—') ?>
                                        • <?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : 'Not released' ?>
                                    </p>
                                </div>
                                <div class="lab-result-detail-status">
                                    <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                    <?php if ($isCritical): ?>
                                        <span class="badge badge-critical">Critical</span>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <section class="lab-result-detail-section">
                                <p class="lab-result-detail-label">Result Summary</p>
                                <p class="lab-result-detail-text"><?= nl2br(esc($result['result_summary'] ?? 'No summary provided')) ?></p>
                            </section>

                            <section class="lab-result-detail-section">
                                <p class="lab-result-detail-label">Detailed Notes</p>
                                <p class="lab-result-detail-text">
                                    <?= !empty($result['detailed_report_path'])
                                        ? nl2br(esc($result['detailed_report_path']))
                                        : 'No detailed notes recorded by the laboratory staff.' ?>
                                </p>
                            </section>

                            <footer class="lab-result-detail-footer">
                                <div>
                                    <span class="lab-result-detail-label">Last Updated</span>
                                    <p class="lab-result-detail-text mb-0">
                                        <?= !empty($result['updated_at']) ? date('M j, Y g:i A', strtotime($result['updated_at'])) : '—' ?>
                                    </p>
                                </div>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted" style="margin: 2rem 0;">No test results found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openAllRequestsModal() {
    const modal = document.getElementById('allRequestsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAllRequestsModal() {
    const modal = document.getElementById('allRequestsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function openAllResultsModal() {
    const modal = document.getElementById('allResultsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAllResultsModal() {
    const modal = document.getElementById('allResultsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllRequestsModal();
        closeAllResultsModal();
        closeWalkInModal();
    }
});
</script>

<!-- Walk-In Request Modal -->
<div id="walkInModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="walkInModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="walkInModalTitle">
        <header class="panel-header modal-header">
            <h2 id="walkInModalTitle">New Walk-In Lab Request</h2>
            <button id="btnCloseWalkInModal" class="icon-button" aria-label="Close">×</button>
        </header>
        <form id="walkInForm" class="modal-body" method="post" action="<?= site_url('admin/lab/walkin/create') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Patient <span class="req">*</span></label>
                    <select name="patient_id" id="walkInPatient" required>
                        <option value="">Select Patient</option>
                        <?php if (!empty($patients)): ?>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['id'] ?>"><?= esc($patient['full_name']) ?> - <?= esc($patient['contact'] ?? '') ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="error" data-error-for="patient_id"></div>
                </div>
                <div class="form-field">
                    <label>Test Type <span class="req">*</span></label>
                    <select name="test_type" id="walkInTestType" required>
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
                    <div id="walkInTestInfo" style="margin-top: 8px; padding: 8px; background: #f0f9ff; border-radius: 4px; display: none;">
                        <div id="walkInTestPrice" style="font-weight: 600; color: #10b981; margin-bottom: 4px;"></div>
                        <div id="walkInTestSpecimen" style="font-size: 12px; color: #666;"></div>
                    </div>
                    <div class="error" data-error-for="test_type"></div>
                </div>
                <div class="form-field">
                    <label>Priority</label>
                    <select name="priority" id="walkInPriority">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="form-field form-field--full">
                    <label>Notes</label>
                    <textarea name="notes" id="walkInNotes" rows="3" placeholder="Additional notes or instructions..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnCancelWalkIn" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Create Request</button>
            </div>
        </form>
    </div>
</div>

<script>
// Walk-In Modal Functions
document.addEventListener('DOMContentLoaded', function() {
    const walkInModal = document.getElementById('walkInModal');
    const openWalkInBtn = document.getElementById('btnOpenWalkInModal');
    const closeWalkInBtn = document.getElementById('btnCloseWalkInModal');
    const walkInBackdrop = document.getElementById('walkInModalBackdrop');
    const walkInForm = document.getElementById('walkInForm');

    function openWalkInModal() {
        if (walkInModal) {
            walkInModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeWalkInModal() {
        if (walkInModal) {
            walkInModal.style.display = 'none';
            document.body.style.overflow = '';
            if (walkInForm) {
                walkInForm.reset();
                // Reset test info display
                const testInfoDiv = document.getElementById('walkInTestInfo');
                if (testInfoDiv) {
                    testInfoDiv.style.display = 'none';
                }
            }
        }
    }

    if (openWalkInBtn) openWalkInBtn.addEventListener('click', openWalkInModal);
    if (closeWalkInBtn) closeWalkInBtn.addEventListener('click', closeWalkInModal);
    if (walkInBackdrop) walkInBackdrop.addEventListener('click', closeWalkInModal);

    // Update test info when test type is selected
    const testTypeSelect = document.getElementById('walkInTestType');
    const testInfoDiv = document.getElementById('walkInTestInfo');
    const testPriceDiv = document.getElementById('walkInTestPrice');
    const testSpecimenDiv = document.getElementById('walkInTestSpecimen');
    
    if (testTypeSelect && testInfoDiv) {
        testTypeSelect.addEventListener('change', async function() {
            const testType = this.value;
            if (!testType) {
                testInfoDiv.style.display = 'none';
                return;
            }
            
            // Fetch test info from server
            try {
                const response = await fetch('<?= base_url('admin/lab/walkin/getTestInfo') ?>?test_type=' + encodeURIComponent(testType));
                const data = await response.json();
                
                if (data.success && data.test) {
                    testPriceDiv.textContent = 'Price: ₱' + parseFloat(data.test.price || 0).toFixed(2);
                    if (data.test.requires_specimen == 1) {
                        testSpecimenDiv.innerHTML = '<span style="color: #f59e0b;">⚠️ Requires specimen collection by nurse</span>';
                    } else {
                        testSpecimenDiv.innerHTML = '<span style="color: #10b981;">✓ No specimen required</span>';
                    }
                    testInfoDiv.style.display = 'block';
                } else {
                    testInfoDiv.style.display = 'none';
                }
            } catch (error) {
                console.error('Error fetching test info:', error);
                testInfoDiv.style.display = 'none';
            }
        });
    }

    // Form submission
    if (walkInForm) {
        walkInForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = walkInForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            try {
                const formData = new FormData(walkInForm);
                const response = await fetch(walkInForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Walk-in lab request created successfully!');
                    closeWalkInModal();
                    location.reload();
                } else {
                    alert(data.message || 'Error creating request. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Request';
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
