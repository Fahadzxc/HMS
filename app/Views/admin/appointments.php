<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Appointments Monitor</h2>
        <p>View current appointments across the facility</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Today's Appointments</div>
                    <div class="kpi-value"><?= count(array_filter($appointments ?? [], function($apt) { return $apt['appointment_date'] === date('Y-m-d'); })) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Confirmed</div>
                    <div class="kpi-value"><?= count(array_filter($appointments ?? [], function($apt) { return $apt['status'] === 'confirmed'; })) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending</div>
                    <div class="kpi-value"><?= count(array_filter($appointments ?? [], function($apt) { return $apt['status'] === 'scheduled'; })) ?></div>
                    <div class="kpi-change kpi-negative">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">This Week</div>
                    <div class="kpi-value"><?= count($appointments ?? []) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Appointment Schedule</h2>
        <div class="row between">
            <input type="text" placeholder="Search appointments..." class="search-input">
        </div>
    </header>
    
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>All appointments</span>
                <span><?= count($appointments ?? []) ?> total</span>
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
                        <th>Room</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr data-id="<?= $appointment['id'] ?>">
                                <td><?= $appointment['id'] ?></td>
                                <td><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></td>
                                <td><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($appointment['patient_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($appointment['doctor_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($appointment['room_number'] ?? '—') ?></td>
                                <td><?= ucfirst($appointment['appointment_type']) ?></td>
                                <td>
                                    <span class="badge badge<?= 
                                        $appointment['status'] === 'confirmed' ? '-success' : 
                                        ($appointment['status'] === 'scheduled' ? '-warning' : 
                                        ($appointment['status'] === 'completed' ? '-info' : '-secondary'))
                                    ?>">
                                        <?= ucfirst($appointment['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($appointment['notes'] ?? '—') ?></td>
                                <td class="actions-cell">
                                    <a href="#" class="action-link" onclick="viewAppointment(<?= $appointment['id'] ?>); return false;">View</a>
                                    <a href="#" class="action-link" onclick="editAppointment(<?= $appointment['id'] ?>); return false;">Edit</a>
                                    <a href="#" class="action-link action-delete" onclick="deleteAppointment(<?= $appointment['id'] ?>); return false;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No appointments found</td>
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

.modal-close:hover {
    color: #374151;
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

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.view-row {
    display: flex;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}

.view-label {
    font-weight: 600;
    color: #64748b;
    width: 120px;
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
            <h3>📋 Appointment Details</h3>
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
            <h3>✏️ Edit Appointment</h3>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm">
                <input type="hidden" id="editId" name="id">
                <div class="form-row">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="editDate" name="appointment_date" required>
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <input type="time" id="editTime" name="appointment_time" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Patient</label>
                        <select id="editPatient" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach (($patients ?? []) as $patient): ?>
                                <option value="<?= $patient['id'] ?>"><?= esc($patient['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Doctor</label>
                        <select id="editDoctor" name="doctor_id" required>
                            <option value="">Select Doctor</option>
                            <?php foreach (($doctors ?? []) as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>"><?= esc($doctor['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Type</label>
                        <select id="editType" name="appointment_type" required>
                            <option value="consultation">Consultation</option>
                            <option value="follow-up">Follow-up</option>
                            <option value="emergency">Emergency</option>
                            <option value="checkup">Checkup</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="editStatus" name="status" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Room</label>
                    <select id="editRoom" name="room_id">
                        <option value="">Select Room (Optional)</option>
                        <?php foreach (($rooms ?? []) as $room): ?>
                            <option value="<?= $room['id'] ?>"><?= esc($room['room_number']) ?> - <?= esc($room['room_type']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea id="editNotes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
            <button class="btn btn-primary" onclick="saveAppointment()">Save Changes</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>🗑️ Delete Appointment</h3>
            <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this appointment?</p>
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
// Store appointments data for quick access
const appointmentsData = <?= json_encode($appointments ?? []) ?>;

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function viewAppointment(id) {
    const apt = appointmentsData.find(a => a.id == id);
    if (!apt) return;
    
    const formatDate = (dateStr) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    };
    
    const formatTime = (timeStr) => {
        const [hours, minutes] = timeStr.split(':');
        const date = new Date();
        date.setHours(parseInt(hours), parseInt(minutes));
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    };
    
    const statusBadge = {
        'confirmed': '<span class="badge badge-success">Confirmed</span>',
        'scheduled': '<span class="badge badge-warning">Scheduled</span>',
        'completed': '<span class="badge badge-info">Completed</span>',
        'cancelled': '<span class="badge badge-secondary">Cancelled</span>'
    };
    
    const html = `
        <div class="view-row">
            <span class="view-label">ID:</span>
            <span class="view-value">#${apt.id}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Date:</span>
            <span class="view-value">${formatDate(apt.appointment_date)}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Time:</span>
            <span class="view-value">${formatTime(apt.appointment_time)}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Patient:</span>
            <span class="view-value">${apt.patient_name || 'N/A'}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Doctor:</span>
            <span class="view-value">${apt.doctor_name || 'N/A'}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Room:</span>
            <span class="view-value">${apt.room_number || '—'}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Type:</span>
            <span class="view-value">${apt.appointment_type ? apt.appointment_type.charAt(0).toUpperCase() + apt.appointment_type.slice(1) : '—'}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Status:</span>
            <span class="view-value">${statusBadge[apt.status] || apt.status}</span>
        </div>
        <div class="view-row">
            <span class="view-label">Notes:</span>
            <span class="view-value">${apt.notes || '—'}</span>
        </div>
        <div class="view-row" style="border-bottom: none;">
            <span class="view-label">Created:</span>
            <span class="view-value">${apt.created_at ? formatDate(apt.created_at) : '—'}</span>
        </div>
    `;
    
    document.getElementById('viewModalBody').innerHTML = html;
    document.getElementById('viewModal').style.display = 'block';
}

function editAppointment(id) {
    const apt = appointmentsData.find(a => a.id == id);
    if (!apt) return;
    
    document.getElementById('editId').value = apt.id;
    document.getElementById('editDate').value = apt.appointment_date;
    document.getElementById('editTime').value = apt.appointment_time;
    document.getElementById('editPatient').value = apt.patient_id;
    document.getElementById('editDoctor').value = apt.doctor_id;
    document.getElementById('editType').value = apt.appointment_type;
    document.getElementById('editStatus').value = apt.status;
    document.getElementById('editRoom').value = apt.room_id || '';
    document.getElementById('editNotes').value = apt.notes || '';
    
    document.getElementById('editModal').style.display = 'block';
}

function saveAppointment() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    fetch('<?= site_url('admin/appointments/update') ?>', {
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
            alert('✅ Appointment updated successfully!');
            closeModal('editModal');
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Failed to update appointment'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred. Please try again.');
    });
}

function deleteAppointment(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').style.display = 'block';
}

function confirmDelete() {
    const id = document.getElementById('deleteId').value;
    
    fetch('<?= site_url('admin/appointments/delete') ?>/' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ Appointment deleted successfully!');
            closeModal('deleteModal');
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Failed to delete appointment'));
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
