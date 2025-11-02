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
                                    <span class="badge badge-<?= $appointment['appointment_type'] === 'emergency' ? 'danger' : 'primary' ?>">
                                        <?= ucfirst($appointment['appointment_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $appointment['status'] === 'confirmed' ? 'success' : 
                                        ($appointment['status'] === 'scheduled' ? 'warning' : 'secondary') 
                                    ?>">
                                        <?= ucfirst($appointment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] === 'scheduled'): ?>
                                        <button class="btn-sm btn-success" onclick="checkInPatient(<?= $appointment['id'] ?>)">
                                            Check In
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">Checked In</span>
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
                                <td><?= ucfirst($appointment['appointment_type']) ?></td>
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
                                        <button class="btn-xs btn-success" onclick="checkInPatient(<?= $appointment['id'] ?>)">
                                            Check In
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-xs btn-primary" onclick="editAppointment(<?= $appointment['id'] ?>)">
                                            Edit
                                        </button>
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
                    <select name="doctor_id" required>
                        <option value="">Choose a doctor...</option>
                        <?php if (isset($doctors) && is_array($doctors)): ?>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>"><?= htmlspecialchars($doctor['name']) ?></option>
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
                        <br><em>Click on a doctor's name to view their schedule</em>
                    </small>
                </div>
                <div class="form-field">
                    <label>Appointment Type <span class="req">*</span></label>
                    <select name="appointment_type" required>
                        <option value="">Select Type</option>
                        <option value="consultation">Consultation</option>
                        <option value="follow-up">Follow-up</option>
                        <option value="emergency">Emergency</option>
                        <option value="routine">Routine Checkup</option>
                        <option value="vaccination">Vaccination</option>
                        <option value="lab_test">Lab Test</option>
                        <option value="xray">X-Ray</option>
                    </select>
                    <div class="error" data-error-for="appointment_type"></div>
                </div>
                <div class="form-field">
                    <label>Appointment Date <span class="req">*</span></label>
                    <input type="date" name="appointment_date" id="appointment_date" required min="<?= date('Y-m-d') ?>">
                    <div class="error" data-error-for="appointment_date"></div>
                    <div id="date_availability_warning" style="display: none; color: #e53e3e; font-size: 0.875rem; margin-top: 0.25rem;">
                        <i class="fas fa-exclamation-triangle"></i> Selected doctor is not available on this date.
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
                        <?php if (isset($rooms) && is_array($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= $room['id'] ?>" 
                                        data-type="<?= $room['room_type'] ?>"
                                        data-floor="<?= $room['floor'] ?>"
                                        data-capacity="<?= $room['capacity'] ?>"
                                        data-occupancy="<?= $room['current_occupancy'] ?>">
                                    <?= esc($room['room_number']) ?> - <?= esc($room['specialization']) ?> 
                                    (Floor <?= $room['floor'] ?>, <?= $room['capacity'] - $room['current_occupancy'] ?> available)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="error" data-error-for="room_id"></div>
                    <small class="form-help">Optional: Select a specific room for the appointment</small>
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
}

// Load Patient Details when patient is selected
function loadPatientDetails() {
    const select = document.getElementById('patient_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        document.getElementById('patient_name').value = selectedOption.getAttribute('data-name');
        document.getElementById('patient_contact').value = selectedOption.getAttribute('data-contact');
    } else {
        document.getElementById('patient_name').value = '';
        document.getElementById('patient_contact').value = '';
    }
}

// Event listeners
document.getElementById('btnOpenAddAppointment').addEventListener('click', function(e) {
    e.preventDefault();
    showAddAppointmentModal();
});

// Add appointment form submission
document.getElementById('addAppointmentForm').addEventListener('submit', function(e) {
    // Let the form submit normally for now to debug
    console.log('Form is being submitted...');
    
    // Validate required fields
    const patientId = document.querySelector('select[name="patient_id"]').value;
    const doctorId = document.querySelector('select[name="doctor_id"]').value;
    const appointmentDate = document.querySelector('input[name="appointment_date"]').value;
    const appointmentTime = document.querySelector('input[name="appointment_time"]').value;
    const appointmentType = document.querySelector('select[name="appointment_type"]').value;
    
    if (!patientId || !doctorId || !appointmentDate || !appointmentTime || !appointmentType) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    // Check if selected date is unavailable for the doctor
    if (appointmentDate && isDateUnavailable(appointmentDate, currentDoctorUnavailableDays)) {
        e.preventDefault();
        alert('The selected doctor is not available on this date. Please choose a different date or doctor.');
        return false;
    }
    
    console.log('All fields filled and date is available, submitting form...');
    // Form will submit normally
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
    
    // Fetch doctor schedule
    fetch(`<?= site_url('reception/getDoctorSchedule') ?>/${doctorId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Doctor schedule data:', data); // Debug log
            if (data.status === 'success') {
                title.textContent = `${data.doctor.name}'s Schedule`;
                displaySchedule(data.schedule);
            } else {
                content.innerHTML = `<div class="no-schedule">Error: ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="no-schedule">Failed to load schedule</div>';
        });
}

function displaySchedule(schedule) {
    const content = document.getElementById('doctorScheduleContent');
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    const dayNames = {
        'monday': 'Monday',
        'tuesday': 'Tuesday', 
        'wednesday': 'Wednesday',
        'thursday': 'Thursday',
        'friday': 'Friday',
        'saturday': 'Saturday',
        'sunday': 'Sunday'
    };
    
    let html = '<div class="schedule-grid">';
    
    days.forEach(day => {
        html += `<div class="schedule-day">`;
        html += `<h4>${dayNames[day]}</h4>`;
        
        if (schedule[day] && schedule[day].length > 0) {
            schedule[day].forEach(slot => {
                const availableClass = slot.is_available ? '' : ' unavailable';
                const statusText = slot.is_available ? 'Available' : 'Not Available';
                html += `<div class="schedule-slot${availableClass}">`;
                html += `<strong>${slot.start_time} - ${slot.end_time}</strong><br>`;
                html += `<small style="color: ${slot.is_available ? '#22543d' : '#e53e3e'}; font-weight: 600;">${statusText}</small>`;
                if (slot.notes) {
                    html += `<br><em style="color: #4a5568;">${slot.notes}</em>`;
                }
                html += `</div>`;
            });
        } else {
            html += '<div class="no-schedule">No schedule set</div>';
        }
        
        html += `</div>`;
    });
    
    html += '</div>';
    content.innerHTML = html;
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
        dateInput.addEventListener('change', updateDateAvailabilityWarning);
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
