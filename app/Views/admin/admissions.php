<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Admissions Management</h2>
        <p>Manage inpatient admissions and discharges</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Admissions</div>
                    <div class="kpi-value"><?= $total_admissions ?? 0 ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Active Admissions</div>
                    <div class="kpi-value"><?= $active_admissions ?? 0 ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Admitted Today</div>
                    <div class="kpi-value"><?= $today_admissions ?? 0 ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Discharged Today</div>
                    <div class="kpi-value"><?= $discharged_today ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Inpatient Records</h2>
        <div class="row between">
            <input type="text" placeholder="Search admissions..." class="search-input" id="searchInput" onkeyup="filterTable()">
        </div>
    </header>
    
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>All Inpatients</span>
                <span><?= count($admissions ?? []) ?> total</span>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="admissionsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Admission Date</th>
                        <th>Room</th>
                        <th>Doctor</th>
                        <th>Case Type</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($admissions)): ?>
                        <?php foreach ($admissions as $admission): ?>
                            <tr data-id="<?= $admission['admission_id'] ?>">
                                <td><?= $admission['admission_id'] ?></td>
                                <td>
                                    <strong><?= esc($admission['patient_name']) ?></strong>
                                    <br><small class="text-muted"><?= esc($admission['gender'] ?? '') ?> | <?= esc($admission['contact'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?= !empty($admission['admission_date']) ? date('M j, Y', strtotime($admission['admission_date'])) : '—' ?>
                                </td>
                                <td>
                                    <?php if (!empty($admission['room_number'])): ?>
                                        <strong><?= esc($admission['room_number']) ?></strong>
                                        <br><small class="text-muted"><?= esc($admission['room_type'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($admission['doctor_name'] ?? '—') ?></td>
                                <td>
                                    <span class="badge <?= $admission['case_type'] === 'Emergency' ? 'badge-danger' : 'badge-info' ?>">
                                        <?= esc($admission['case_type'] ?? '—') ?>
                                    </span>
                                </td>
                                <td>
                                    <span title="<?= esc($admission['reason_for_admission'] ?? '') ?>">
                                        <?= esc(substr($admission['reason_for_admission'] ?? '—', 0, 25)) ?><?= strlen($admission['reason_for_admission'] ?? '') > 25 ? '...' : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($admission['status'] ?? '') {
                                        'Admitted' => 'badge-success',
                                        'Discharged' => 'badge-info',
                                        'Transferred' => 'badge-warning',
                                        default => 'badge-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= esc($admission['status'] ?? 'Unknown') ?></span>
                                </td>
                                <td class="actions-cell">
                                    <a href="#" class="action-link" onclick="viewAdmission(<?= $admission['admission_id'] ?>); return false;">View</a>
                                    <a href="#" class="action-link" onclick="editAdmission(<?= $admission['admission_id'] ?>); return false;">Edit</a>
                                    <?php if ($admission['status'] !== 'Discharged'): ?>
                                        <a href="#" class="action-link" style="color: #10b981;" onclick="dischargePatient(<?= $admission['admission_id'] ?>); return false;">Discharge</a>
                                    <?php endif; ?>
                                    <a href="#" class="action-link action-delete" onclick="deleteAdmission(<?= $admission['admission_id'] ?>); return false;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No inpatient admissions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
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
.badge-danger { background-color: #fed7d7; color: #822727; }

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 85vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.modal-header h3 {
    margin: 0;
    color: #1e293b;
}

.modal-close {
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    color: #6b7280;
    background: none;
    border: none;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.25rem;
    font-weight: 500;
    color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    border: none;
    font-weight: 500;
    cursor: pointer;
}

.btn-primary { background: #3b82f6; color: white; }
.btn-primary:hover { background: #2563eb; }
.btn-secondary { background: #6b7280; color: white; }
.btn-secondary:hover { background: #4b5563; }
.btn-success { background: #10b981; color: white; }
.btn-success:hover { background: #059669; }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover { background: #dc2626; }

.view-row {
    display: flex;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}

.view-label {
    font-weight: 600;
    color: #64748b;
    width: 130px;
    flex-shrink: 0;
}

.view-value {
    color: #1e293b;
}
</style>

<!-- View Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🏥 Admission Details</h3>
            <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="modal-body" id="viewModalBody">
            <!-- Content will be loaded here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✏️ Edit Admission</h3>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm">
                <input type="hidden" id="editAdmissionId" name="admission_id">
                <div class="form-row">
                    <div class="form-group">
                        <label>Doctor</label>
                        <select id="editDoctor" name="doctor_id" required>
                            <option value="">Select Doctor</option>
                            <?php foreach (($doctors ?? []) as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>"><?= esc($doctor['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Room</label>
                        <select id="editRoom" name="room_id">
                            <option value="">Select Room</option>
                            <?php foreach (($rooms ?? []) as $room): ?>
                                <option value="<?= $room['id'] ?>"><?= esc($room['room_number']) ?> - <?= esc($room['room_type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Case Type</label>
                        <select id="editCaseType" name="case_type" required>
                            <option value="Regular">Regular</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="editStatus" name="status" required>
                            <option value="Admitted">Admitted</option>
                            <option value="Discharged">Discharged</option>
                            <option value="Transferred">Transferred</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea id="editNotes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
            <button class="btn btn-primary" onclick="saveAdmission()">Save Changes</button>
        </div>
    </div>
</div>

<!-- Discharge Confirmation Modal -->
<div id="dischargeModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>🏠 Discharge Patient</h3>
            <button class="modal-close" onclick="closeModal('dischargeModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to discharge this patient?</p>
            <p class="text-muted" style="font-size: 0.875rem;">This will mark the admission as completed and change patient type to outpatient.</p>
            <input type="hidden" id="dischargeId">
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('dischargeModal')">Cancel</button>
            <button class="btn btn-success" onclick="confirmDischarge()">Discharge</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>🗑️ Delete Admission</h3>
            <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this admission record?</p>
            <p class="text-muted" style="font-size: 0.875rem;">This action cannot be undone.</p>
            <input type="hidden" id="deleteId">
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
            <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
// Store admissions data for quick access
const admissionsData = <?= json_encode($admissions ?? []) ?>;

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('admissionsTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 0; j < cells.length - 1; j++) {
            if (cells[j].textContent.toLowerCase().includes(input)) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
}

function viewAdmission(id) {
    const adm = admissionsData.find(a => a.admission_id == id);
    if (!adm) return;
    
    const formatDate = (dateStr) => {
        if (!dateStr) return '—';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    };
    
    const statusBadge = {
        'Admitted': '<span class="badge badge-success">Admitted</span>',
        'Discharged': '<span class="badge badge-info">Discharged</span>',
        'Transferred': '<span class="badge badge-warning">Transferred</span>'
    };
    
    const caseTypeBadge = adm.case_type === 'Emergency' 
        ? '<span class="badge badge-danger">Emergency</span>' 
        : '<span class="badge badge-info">Regular</span>';
    
    const html = `
        <div class="view-row">
            <span class="view-label">Admission ID:</span>
            <span class="view-value">#${adm.admission_id}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Patient:</span>
            <span class="view-value"><strong>${adm.patient_name || 'N/A'}</strong></span>
        </div>
        <div class="view-row">
            <span class="view-label">Gender:</span>
            <span class="view-value">${adm.gender || '—'}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Contact:</span>
            <span class="view-value">${adm.contact || '—'}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Admission Date:</span>
            <span class="view-value">${formatDate(adm.admission_date)}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Room:</span>
            <span class="view-value">${adm.room_number || '—'} ${adm.room_type ? '(' + adm.room_type + ')' : ''}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Doctor:</span>
            <span class="view-value">${adm.doctor_name || '—'}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Case Type:</span>
            <span class="view-value">${caseTypeBadge}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Reason:</span>
            <span class="view-value">${adm.reason_for_admission || '—'}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Status:</span>
            <span class="view-value">${statusBadge[adm.status] || adm.status}</span>
        </div>
        <div class="view-row" style="border-bottom: none;">
            <span class="view-label">Notes:</span>
            <span class="view-value">${adm.notes || '—'}</span>
        </div>
    `;
    
    document.getElementById('viewModalBody').innerHTML = html;
    document.getElementById('viewModal').style.display = 'block';
}

function editAdmission(id) {
    const adm = admissionsData.find(a => a.admission_id == id);
    if (!adm) return;
    
    document.getElementById('editAdmissionId').value = adm.admission_id;
    document.getElementById('editDoctor').value = adm.doctor_id || '';
    document.getElementById('editRoom').value = adm.room_id || '';
    document.getElementById('editCaseType').value = adm.case_type || 'Regular';
    document.getElementById('editStatus').value = adm.status || 'Admitted';
    document.getElementById('editNotes').value = adm.notes || '';
    
    document.getElementById('editModal').style.display = 'block';
}

function saveAdmission() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    fetch('<?= site_url('admin/admissions/update') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ Admission updated successfully!');
            closeModal('editModal');
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Failed to update admission'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred. Please try again.');
    });
}

function dischargePatient(id) {
    document.getElementById('dischargeId').value = id;
    document.getElementById('dischargeModal').style.display = 'block';
}

function confirmDischarge() {
    const id = document.getElementById('dischargeId').value;
    
    fetch('<?= site_url('admin/admissions/discharge') ?>/' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ Patient discharged successfully!');
            closeModal('dischargeModal');
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Failed to discharge patient'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred. Please try again.');
    });
}

function deleteAdmission(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').style.display = 'block';
}

function confirmDelete() {
    const id = document.getElementById('deleteId').value;
    
    fetch('<?= site_url('admin/admissions/delete') ?>/' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ Admission deleted successfully!');
            closeModal('deleteModal');
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Failed to delete admission'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred. Please try again.');
    });
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
});
</script>

<?= $this->endSection() ?>

