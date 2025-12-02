<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🏥</span>
                    Inpatients
                </h2>
                <p class="page-subtitle">
                    Your current inpatient assignments
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Inpatient List</h2>
        <p>Patients admitted under your care</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission Date</th>
                        <th>Patient</th>
                        <th>Room</th>
                        <th>Case Type</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inpatients)): ?>
                        <?php foreach ($inpatients as $row): ?>
                            <tr data-id="<?= $row['id'] ?>">
                                <td>
                                    <strong><?= !empty($row['admission_date']) ? date('M j, Y', strtotime($row['admission_date'])) : '—' ?></strong>
                                </td>
                                <td>
                                    <strong><?= esc($row['patient_name'] ?? 'N/A') ?></strong><br>
                                    <span style="color: #64748b; font-size: 0.875rem;">
                                        <?php if (!empty($row['age'])): ?>Age: <?= esc($row['age']) ?><?php endif; ?>
                                        <?php if (!empty($row['gender'])): ?> • <?= ucfirst(esc($row['gender'])) ?><?php endif; ?>
                                        <?php if (!empty($row['contact'])): ?><br><?= esc($row['contact']) ?><?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($row['room_number'])): ?>
                                        <strong><?= esc($row['room_number']) ?></strong><br>
                                        <span style="color: #64748b; font-size: 0.875rem;"><?= esc($row['room_type'] ?? '') ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $row['case_type'] === 'Emergency' ? 'badge-danger' : 'badge-info' ?>">
                                        <?= esc($row['case_type'] ?? 'Regular') ?>
                                    </span>
                                </td>
                                <td>
                                    <span title="<?= esc($row['reason_for_admission'] ?? '') ?>">
                                        <?= esc(substr($row['reason_for_admission'] ?? '—', 0, 30)) ?><?= strlen($row['reason_for_admission'] ?? '') > 30 ? '...' : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($row['discharge_ordered_at'])): ?>
                                        <?php if (!empty($row['discharge_ready_at'])): ?>
                                            <span class="badge badge-success">Ready for Discharge</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Discharge Ordered</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-info">Admitted</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($row['discharge_ordered_at'])): ?>
                                        <button class="btn btn-discharge" onclick="orderDischarge(<?= $row['id'] ?>, '<?= esc($row['patient_name']) ?>')">
                                            📋 Order Discharge
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #10b981; font-size: 0.875rem;">✓ Ordered <?= date('M j', strtotime($row['discharge_ordered_at'])) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                                No inpatients assigned
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Order Discharge Modal -->
<div id="dischargeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📋 Order Discharge</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>You are ordering discharge for: <strong id="patientName"></strong></p>
            <input type="hidden" id="admissionId">
            <div class="form-group">
                <label>Discharge Notes / Instructions</label>
                <textarea id="dischargeNotes" rows="4" placeholder="Enter discharge instructions, medications to continue, follow-up schedule, etc."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="confirmDischarge()">Confirm Discharge Order</button>
        </div>
    </div>
</div>

<style>
.badge-info { background: #dbeafe; color: #1d4ed8; padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-danger { background: #fee2e2; color: #dc2626; padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-warning { background: #fef3c7; color: #d97706; padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-success { background: #d1fae5; color: #059669; padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }

.btn-discharge {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
}
.btn-discharge:hover { background: #d97706; }

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
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 8px 8px 0 0;
}
.modal-header h3 { margin: 0; color: #1e293b; }
.modal-close {
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    color: #6b7280;
    background: none;
    border: none;
}
.modal-body { padding: 1.5rem; }
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
.form-group { margin-bottom: 1rem; }
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    resize: vertical;
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
</style>

<script>
function orderDischarge(admissionId, patientName) {
    document.getElementById('admissionId').value = admissionId;
    document.getElementById('patientName').textContent = patientName;
    document.getElementById('dischargeNotes').value = '';
    document.getElementById('dischargeModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('dischargeModal').style.display = 'none';
}

function confirmDischarge() {
    const admissionId = document.getElementById('admissionId').value;
    const dischargeNotes = document.getElementById('dischargeNotes').value;
    
    fetch('<?= site_url('doctor/orderDischarge') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            admission_id: admissionId,
            discharge_notes: dischargeNotes
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ ' + result.message);
            closeModal();
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Failed to order discharge'));
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
        closeModal();
    }
});
</script>

<?= $this->endSection() ?>


