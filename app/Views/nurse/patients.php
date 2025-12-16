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
        $dischargeOrdersList = isset($discharge_orders) && is_array($discharge_orders) ? $discharge_orders : [];
        $totalPatients = count($patientsList);
        $pendingDischarges = count($dischargeOrdersList);
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
                    <div class="kpi-label">Pending Discharges</div>
                    <div class="kpi-value" style="color: <?= $pendingDischarges > 0 ? '#f59e0b' : '#64748b' ?>;"><?= $pendingDischarges ?></div>
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

<?php 
$readyList = isset($ready_for_discharge) && is_array($ready_for_discharge) ? $ready_for_discharge : [];
?>

<?php if (!empty($readyList)): ?>
<!-- Ready for Final Discharge Section -->
<section class="panel panel-spaced" style="border-left: 4px solid #10b981;">
    <header class="panel-header">
        <h2>✅ Ready for Discharge</h2>
        <p>Patients prepared and ready - waiting for billing clearance then final discharge</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Room</th>
                        <th>Doctor</th>
                        <th>Ready Since</th>
                        <th>Prepared By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($readyList as $ready): ?>
                        <tr>
                            <td>
                                <strong><?= esc($ready['patient_name']) ?></strong><br>
                                <span style="color: #64748b; font-size: 0.875rem;"><?= esc($ready['contact'] ?? '') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($ready['room_number'])): ?>
                                    <span class="room-badge"><?= esc($ready['room_number']) ?></span><br>
                                    <span style="color: #64748b; font-size: 0.875rem;"><?= esc($ready['room_type'] ?? '') ?></span>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($ready['doctor_name'] ?? '—') ?></td>
                            <td>
                                <strong><?= date('M j, Y', strtotime($ready['discharge_ready_at'])) ?></strong><br>
                                <span style="color: #64748b; font-size: 0.875rem;"><?= date('g:i A', strtotime($ready['discharge_ready_at'])) ?></span>
                            </td>
                            <td><?= esc($ready['ready_by_name'] ?? '—') ?></td>
                            <td>
                                <button class="btn btn-discharge-final" onclick="finalDischarge(<?= $ready['id'] ?>, '<?= esc($ready['patient_name']) ?>')">
                                    🏠 Final Discharge
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($dischargeOrdersList)): ?>
<!-- Pending Discharge Orders Section -->
<section class="panel panel-spaced" style="border-left: 4px solid #f59e0b;">
    <header class="panel-header">
        <h2>📋 Pending Discharge Orders</h2>
        <p>Patients with doctor's discharge order - prepare for discharge</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Room</th>
                        <th>Doctor</th>
                        <th>Ordered</th>
                        <th>Discharge Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dischargeOrdersList as $order): ?>
                        <tr>
                            <td>
                                <strong><?= esc($order['patient_name']) ?></strong><br>
                                <span style="color: #64748b; font-size: 0.875rem;"><?= esc($order['contact'] ?? '') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($order['room_number'])): ?>
                                    <span class="room-badge"><?= esc($order['room_number']) ?></span><br>
                                    <span style="color: #64748b; font-size: 0.875rem;"><?= esc($order['room_type'] ?? '') ?></span>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($order['doctor_name'] ?? '—') ?></td>
                            <td>
                                <strong><?= date('M j, Y', strtotime($order['discharge_ordered_at'])) ?></strong><br>
                                <span style="color: #64748b; font-size: 0.875rem;"><?= date('g:i A', strtotime($order['discharge_ordered_at'])) ?></span>
                            </td>
                            <td>
                                <span title="<?= esc($order['discharge_notes'] ?? '') ?>">
                                    <?= esc(substr($order['discharge_notes'] ?? '—', 0, 40)) ?><?= strlen($order['discharge_notes'] ?? '') > 40 ? '...' : '' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-prepare" onclick="markReady(<?= $order['id'] ?>, '<?= esc($order['patient_name']) ?>')">
                                    ✓ Mark Ready
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Patient Records</h2>
        <div class="row between">
            <input type="text" placeholder="Search patients..." class="search-input">
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
                <div class="col-type">TYPE</div>
                <div class="col-room">ROOM</div>
                <div class="col-doctor">DOCTOR</div>
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
                <div class="col-type">
                    <?php 
                    $visitType = $p['visit_type'] ?? 'consultation';
                    if (isset($p['patient_type']) && strtolower($p['patient_type']) === 'inpatient') {
                        $visitType = 'inpatient';
                    } elseif (empty($p['visit_type'])) {
                        // Determine type if not set
                        $hasConsultation = false;
                        $hasDoctor = false;
                        if (isset($p['assigned_doctor_name']) && !empty($p['assigned_doctor_name'])) {
                            $hasDoctor = true;
                        }
                        $visitType = ($hasConsultation || $hasDoctor) ? 'consultation' : 'walk-in';
                    }
                    $typeClass = ($visitType === 'walk-in') ? 'badge-orange' : (($visitType === 'inpatient') ? 'badge-blue' : 'badge-purple');
                    ?>
                    <span class="badge <?= $typeClass ?>"><?= ucfirst($visitType) ?></span>
                </div>
                <div class="col-room">
                    <?php 
                        $displayRoom = !empty($p['appointment_room_number']) 
                            ? $p['appointment_room_number'] 
                            : (!empty($p['room_number']) ? $p['room_number'] : null);
                    ?>
                    <?php if (!empty($displayRoom)): ?>
                        <div><?= esc($displayRoom) ?></div>
                        <?php if (isset($p['patient_type']) && $p['patient_type'] === 'inpatient'): ?>
                            <small class="text-muted">Inpatient</small>
                        <?php else: ?>
                            <small class="text-muted">Outpatient</small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">No room assigned</span>
                    <?php endif; ?>
                </div>
                <div class="col-doctor">
                    <?php if (!empty($p['assigned_doctor_name'])): ?>
                        <div><?= esc($p['assigned_doctor_name']) ?></div>
                        <?php if (!empty($p['last_appointment_date'])): ?>
                            <small class="text-muted">Last: <?= date('M j, Y', strtotime($p['last_appointment_date'])) ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">No appointments</span>
                    <?php endif; ?>
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

<style>
/* Patient Records Section - Matching Reception Design */
.panel-header {
    margin-bottom: 1.5rem;
}

.panel-header .row.between {
    margin-top: 0.75rem;
}

/* Table header styling - matching reception */
.table-header {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.75rem;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Table row styling - matching reception */
.table-row {
    border-bottom: 1px solid #f1f5f9;
}

.table-row:hover {
    background: #f8fafc;
}

/* Column widths - matching reception layout */
.col-id {
    width: 100px;
    flex-shrink: 0;
}

.col-name {
    flex: 1;
    min-width: 200px;
}

.col-age {
    width: 130px;
    flex-shrink: 0;
}

.col-contact {
    width: 200px;
    flex-shrink: 0;
}

.col-status {
    width: 120px;
    flex-shrink: 0;
}

.col-type {
    width: 120px;
    flex-shrink: 0;
}

.col-room {
    width: 150px;
    flex-shrink: 0;
}

.col-doctor {
    flex: 1;
    min-width: 150px;
}

/* Patient info styling - matching reception */
.patient-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.patient-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e0f2fe;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.patient-details {
    flex: 1;
}

.patient-details strong {
    display: block;
    margin-bottom: 0.25rem;
}

.blood-type {
    margin: 0;
    color: #64748b;
    font-size: 0.9rem;
}

/* Contact styling - matching reception */
.phone {
    margin: 0;
}

.email {
    margin: 0;
    color: #64748b;
    font-size: 0.9rem;
}

/* Age/Gender styling - matching reception */
.col-age div:first-child {
    font-weight: 500;
    color: #374151;
}

.col-age div:last-child {
    color: #6b7280;
    font-size: 0.9rem;
}

/* Badge styles - matching reception colors */
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.badge-green {
    background: #dcfce7;
    color: #166534;
}

.badge-red {
    background: #fecaca;
    color: #991b1b;
}

.badge-gray {
    background: #f3f4f6;
    color: #374151;
}

.badge-yellow {
    background: #fef3c7;
    color: #92400e;
}

.badge-orange {
    background: #fed7aa;
    color: #9a3412;
}

.badge-purple {
    background: #e9d5ff;
    color: #6b21a8;
}

.badge-blue {
    background: #dbeafe;
    color: #1e40af;
}

.text-muted {
    color: #64748b;
    font-size: 0.875rem;
}

/* Discharge section styles */
.btn-prepare {
    background: #10b981;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
}
.btn-prepare:hover { background: #059669; }

.btn-discharge-final {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
}
.btn-discharge-final:hover { background: #2563eb; }

.room-badge {
    background: #dbeafe;
    color: #1d4ed8;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}
.data-table th,
.data-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.data-table th {
    background: #f8fafc;
    font-weight: 600;
    color: #475569;
    font-size: 0.8125rem;
    text-transform: uppercase;
}
.data-table tbody tr:hover {
    background: #f8fafc;
}

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
    max-width: 450px;
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
.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    border: none;
    font-weight: 500;
    cursor: pointer;
}
.btn-primary { background: #3b82f6; color: white; }
.btn-secondary { background: #6b7280; color: white; }
.btn-success { background: #10b981; color: white; }
</style>

<!-- Mark Ready Modal -->
<div id="readyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✓ Mark Patient Ready</h3>
            <button class="modal-close" onclick="closeModal('readyModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Mark <strong id="readyPatientName"></strong> as ready for discharge?</p>
            <p style="color: #64748b; font-size: 0.875rem;">This confirms the patient has been prepared and is ready to leave after billing clearance.</p>
            <input type="hidden" id="readyAdmissionId">
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('readyModal')">Cancel</button>
            <button class="btn btn-success" onclick="confirmReady()">Confirm Ready</button>
        </div>
    </div>
</div>

<!-- Final Discharge Modal -->
<div id="dischargeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🏠 Final Discharge</h3>
            <button class="modal-close" onclick="closeModal('dischargeModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Complete discharge for <strong id="dischargePatientName"></strong>?</p>
            <p style="color: #64748b; font-size: 0.875rem;">Please ensure billing has been cleared before proceeding.</p>
            <p style="color: #f59e0b; font-size: 0.875rem;">⚠️ This action will mark the patient as discharged and change their status to outpatient.</p>
            <input type="hidden" id="dischargeAdmissionId">
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('dischargeModal')">Cancel</button>
            <button class="btn btn-primary" onclick="confirmFinalDischarge()">Complete Discharge</button>
        </div>
    </div>
</div>

<script>
function markReady(admissionId, patientName) {
    document.getElementById('readyAdmissionId').value = admissionId;
    document.getElementById('readyPatientName').textContent = patientName;
    document.getElementById('readyModal').style.display = 'block';
}

function finalDischarge(admissionId, patientName) {
    // Check if button is disabled (not fully paid)
    const btn = event.target;
    if (btn.disabled) {
        alert('⚠️ Cannot discharge patient. Payment is required first.\n\nPlease ensure all bills are fully paid before discharging.');
        return;
    }
    document.getElementById('dischargeAdmissionId').value = admissionId;
    document.getElementById('dischargePatientName').textContent = patientName;
    document.getElementById('dischargeModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function confirmReady() {
    const admissionId = document.getElementById('readyAdmissionId').value;
    
    fetch('<?= site_url('nurse/markDischargeReady') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ admission_id: admissionId })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ ' + result.message);
            closeModal('readyModal');
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Failed to mark patient ready'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred. Please try again.');
    });
}

function confirmFinalDischarge() {
    const admissionId = document.getElementById('dischargeAdmissionId').value;
    
    fetch('<?= site_url('nurse/finalDischarge') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ admission_id: admissionId })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ ' + result.message);
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

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
});
</script>

<?= $this->endSection() ?>
