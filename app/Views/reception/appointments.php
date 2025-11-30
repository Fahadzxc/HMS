<?= $this->extend('template') ?>

<?= $this->section('content') ?>

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
                        <th>Room</th>
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
                                <td><?= htmlspecialchars($appointment['room_number'] ?? '—') ?></td>
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
            <div class="form-grid">
                <div class="form-field">
                    <label>Select Patient <span class="req">*</span></label>
                    <select name="patient_id" id="patient_select" required onchange="loadPatientDetails()">
                        <option value="">Choose a patient...</option>
                        <?php if (isset($patients) && is_array($patients)): ?>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['id'] ?>" 
                                        data-name="<?= htmlspecialchars($patient['full_name']) ?>" 
                                        data-contact="<?= htmlspecialchars($patient['contact']) ?>">
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
                <div class="form-field">
                    <label>Select Doctor <span class="req">*</span></label>
                    <select name="doctor_id" id="doctor_select" required onchange="loadDoctorScheduleCalendar()">
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
                <div class="form-field" id="appointment_type_field">
                    <label>Appointment Type <span class="req">*</span></label>
                    <select name="appointment_type" id="appointment_type_select">
                        <option value="">Select Type</option>
                        <option value="consultation">Consultation</option>
                        <option value="follow-up">Follow-up</option>
                        <option value="procedure">Procedure</option>
                        <option value="laboratory_test">Laboratory Test</option>
                        <option value="imaging">Imaging / X-ray / Ultrasound</option>
                    </select>
                    <div class="error" data-error-for="appointment_type"></div>
                    <small class="form-help" style="color: #64748b;">
                        For outpatient appointments only
                    </small>
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
                    <label>Appointment Time <span class="req">*</span></label>
                    <input type="time" name="appointment_time" required>
                    <div class="error" data-error-for="appointment_time"></div>
                </div>
                <div class="form-field">
                    <label>Status <span class="req">*</span></label>
                    <select name="status" required>
                        <option value="scheduled">Scheduled</option>
                        <option value="confirmed">Confirmed</option>
                    </select>
                    <div class="error" data-error-for="status"></div>
                </div>
                <div class="form-field">
                    <label>Select Room</label>
                    <select name="room_id" id="room_select">
                        <option value="">Choose a room...</option>
                    </select>
                    <div class="error" data-error-for="room_id"></div>
                    <small class="form-help">Optional: Select an OPD clinic room for outpatient appointment</small>
                </div>
                <div class="form-field form-field--full">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" placeholder="Additional notes or instructions..."></textarea>
                    <div class="error" data-error-for="notes"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAddAppointmentModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Appointment</button>
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
}

function closeAddAppointmentModal() {
    const modal = document.getElementById('addAppointmentModal');
    const form = document.getElementById('addAppointmentForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
    
    // Reset form fields
    resetRoomField();
}

// Load Patient Details when patient is selected
function loadPatientDetails() {
    const select = document.getElementById('patient_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        // Fill patient details
        document.getElementById('patient_name').value = selectedOption.getAttribute('data-name');
        document.getElementById('patient_contact').value = selectedOption.getAttribute('data-contact');
        
        // Load OPD rooms for outpatient appointments
        loadOPDRooms();
    } else {
        document.getElementById('patient_name').value = '';
        document.getElementById('patient_contact').value = '';
        resetRoomField();
    }
}

// Load OPD rooms for outpatient appointments based on appointment type
function loadOPDRooms() {
    const roomSelect = document.getElementById('room_select');
    const appointmentTypeSelect = document.getElementById('appointment_type_select');
    const appointmentType = appointmentTypeSelect ? appointmentTypeSelect.value : null;
    
    // Reset room dropdown
    roomSelect.innerHTML = '<option value="">Loading rooms...</option>';
    
    // Build URL with appointment type if available
    let url = `<?= base_url('reception/rooms') ?>?type=outpatient`;
    if (appointmentType) {
        url += `&appointment_type=${encodeURIComponent(appointmentType)}`;
    }
    
    // Fetch OPD rooms from backend
    fetch(url)
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
                roomSelect.innerHTML = '<option value="">No rooms available for this appointment type</option>';
            }
        })
        .catch(error => {
            console.error('Error loading rooms:', error);
            roomSelect.innerHTML = '<option value="">Error loading rooms</option>';
        });
}

// Reset room field
function resetRoomField() {
    const roomSelect = document.getElementById('room_select');
    roomSelect.innerHTML = '<option value="">Choose a room...</option>';
    roomSelect.removeAttribute('required');
}

// Event listeners
document.getElementById('btnOpenAddAppointment').addEventListener('click', function(e) {
    e.preventDefault();
    showAddAppointmentModal();
});

// Reload rooms when appointment type changes
const appointmentTypeSelect = document.getElementById('appointment_type_select');
if (appointmentTypeSelect) {
    appointmentTypeSelect.addEventListener('change', function() {
        // Only reload rooms if a patient is already selected
        const patientSelect = document.getElementById('patient_select');
        if (patientSelect && patientSelect.value) {
            loadOPDRooms();
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
    const appointmentType = document.querySelector('select[name="appointment_type"]').value;
    const roomId = document.querySelector('select[name="room_id"]').value;
    
    // Validate required fields
    if (!patientId || !doctorId || !appointmentDate || !appointmentTime || !appointmentType) {
        alert('Please fill in all required fields');
        return false;
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
