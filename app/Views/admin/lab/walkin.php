<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Walk In - Lab Tests</h2>
        <p>Manage lab test requests for walk-in patients (without doctor consultation)</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Walk-In Requests</div>
                    <div class="kpi-value"><?= count($walkInRequests ?? []) + count($labAppointments ?? []) ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending</div>
                    <div class="kpi-value"><?= count(array_filter($walkInRequests ?? [], function($r) { return ($r['status'] ?? '') === 'pending'; })) ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">In Progress</div>
                    <div class="kpi-value"><?= count(array_filter($walkInRequests ?? [], function($r) { return ($r['status'] ?? '') === 'in_progress'; })) ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Completed</div>
                    <div class="kpi-value"><?= count(array_filter($walkInRequests ?? [], function($r) { return ($r['status'] ?? '') === 'completed'; })) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Create Walk-In Lab Request</h2>
        <div class="row between">
            <button id="btnOpenWalkInModal" class="btn-primary">+ New Walk-In Request</button>
        </div>
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
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('walkInModal');
    const openBtn = document.getElementById('btnOpenWalkInModal');
    const closeBtn = document.getElementById('btnCloseWalkInModal');
    const backdrop = document.getElementById('walkInModalBackdrop');
    const form = document.getElementById('walkInForm');

    function openModal() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        form.reset();
        // Reset test info display
        const testInfoDiv = document.getElementById('walkInTestInfo');
        if (testInfoDiv) {
            testInfoDiv.style.display = 'none';
        }
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

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
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Walk-in lab request created successfully!');
                    closeModal();
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
