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
        <form id="walkInForm" class="modal-body" method="post" action="<?= site_url('admin/walkin/create') ?>">
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
                        <option value="">Select Test Type</option>
                        <option value="Complete Blood Count (CBC)">Complete Blood Count (CBC)</option>
                        <option value="Blood Chemistry">Blood Chemistry</option>
                        <option value="Urinalysis">Urinalysis</option>
                        <option value="X-Ray">X-Ray</option>
                        <option value="Ultrasound">Ultrasound</option>
                        <option value="CT Scan">CT Scan</option>
                        <option value="MRI">MRI</option>
                        <option value="ECG">ECG</option>
                        <option value="Blood Sugar">Blood Sugar</option>
                        <option value="Lipid Profile">Lipid Profile</option>
                        <option value="Liver Function Test">Liver Function Test</option>
                        <option value="Kidney Function Test">Kidney Function Test</option>
                        <option value="Thyroid Function Test">Thyroid Function Test</option>
                        <option value="Other">Other</option>
                    </select>
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
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

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
