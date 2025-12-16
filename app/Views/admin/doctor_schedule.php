<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📅</span>
                    Doctor Schedule: <?= esc($doctor['name'] ?? 'Unknown') ?>
                </h2>
                <p class="page-subtitle">
                    View and manage doctor's monthly availability schedule
                    <span class="date-text"> • <?= $monthName ?> <?= $currentYear ?></span>
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="<?= base_url('admin/doctors') ?>" style="padding: 0.6rem 1rem; background: #64748b; color: white; border: none; border-radius: 0.5rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center;">← Back to Doctors</a>
                <a href="<?= base_url('admin/doctors/schedule/' . $doctor['id'] . '?month=' . $prevMonth . '&year=' . $prevYear) ?>" style="padding: 0.6rem 1rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center;">← Prev</a>
                <a href="<?= base_url('admin/doctors/schedule/' . $doctor['id'] . '?month=' . date('n') . '&year=' . date('Y')) ?>" style="padding: 0.6rem 1rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center;">Today</a>
                <a href="<?= base_url('admin/doctors/schedule/' . $doctor['id'] . '?month=' . $nextMonth . '&year=' . $nextYear) ?>" style="padding: 0.6rem 1rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center;">Next →</a>
                <button class="btn-secondary" onclick="editRecurringSchedule()" style="padding: 0.6rem 1.2rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 500; cursor: pointer; margin-left: 0.5rem;">
                    <span>✏️</span> Edit Schedule
                </button>
                <button class="btn-primary" onclick="showAddScheduleModal()" style="padding: 0.6rem 1.2rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer; margin-left: 0.5rem;">
                    <span>➕</span> Add Schedule
                </button>
            </div>
        </div>
    </header>
</section>

<!-- Calendar -->
<section class="panel panel-spaced">
    <div class="calendar-container">
        <!-- Calendar Header -->
        <div class="calendar-header">
            <?php 
            $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            foreach ($dayNames as $dayName): 
            ?>
                <div class="calendar-day-header"><?= $dayName ?></div>
            <?php endforeach; ?>
        </div>
        
        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <?php
            // Fill empty cells before first day
            for ($i = 0; $i < $startDay; $i++):
            ?>
                <div class="calendar-day empty"></div>
            <?php endfor; ?>
            
            <?php
            // Fill calendar days
            $today = date('Y-m-d');
            for ($day = 1; $day <= $daysInMonth; $day++):
                $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
                $dayOfWeekName = strtolower(date('l', strtotime($date)));
                $isToday = ($date === $today);
                
                // Get date-specific schedules first
                $daySchedules = $scheduleByDate[$date] ?? [];
                
                // If no date-specific schedule, check for weekly schedule
                if (empty($daySchedules)) {
                    $daySchedules = $scheduleByDay[$dayOfWeekName] ?? [];
                }
                
                // Get appointments for this date
                $dayAppointments = $appointmentsByDate[$date] ?? [];
            ?>
                <div class="calendar-day <?= $isToday ? 'today' : '' ?>" onclick="openDateSchedule('<?= $date ?>', '<?= $dayOfWeekName ?>')">
                    <div class="calendar-day-number"><?= $day ?></div>
                    <div class="calendar-day-schedules">
                        <?php if (!empty($daySchedules)): ?>
                            <?php foreach (array_slice($daySchedules, 0, 1) as $sched): ?>
                                <div class="calendar-schedule-item <?= $sched['is_available'] ? 'available' : 'unavailable' ?>">
                                    <div class="schedule-time">
                                        <?= date('g:i A', strtotime($sched['start_time'])) ?> - <?= date('g:i A', strtotime($sched['end_time'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="calendar-no-schedule">Not Available</div>
                        <?php endif; ?>
                        
                        <?php if (!empty($dayAppointments)): ?>
                            <?php foreach (array_slice($dayAppointments, 0, 3) as $apt): ?>
                                <?php 
                                $appointmentType = ucfirst(str_replace('_', ' ', $apt['appointment_type'] ?? 'consultation'));
                                $appointmentStatus = strtolower($apt['status'] ?? 'scheduled');
                                $isCompleted = ($appointmentStatus === 'completed');
                                
                                $typeBadge = 'primary';
                                if (strtolower($appointmentType) === 'follow-up') {
                                    $typeBadge = 'info';
                                } elseif (strtolower($appointmentType) === 'procedure') {
                                    $typeBadge = 'warning';
                                } elseif (strtolower($appointmentType) === 'laboratory_test') {
                                    $typeBadge = 'success';
                                }
                                
                                // Add completed class if appointment is completed
                                $appointmentClass = $isCompleted ? 'calendar-appointment-item completed' : 'calendar-appointment-item';
                                ?>
                                <div class="<?= $appointmentClass ?>" title="<?= esc($apt['patient_name'] ?? 'Patient') ?> - <?= date('g:i A', strtotime($apt['appointment_time'])) ?> - <?= esc($appointmentType) ?> - <?= esc(ucfirst($appointmentStatus)) ?>">
                                    <div class="appointment-patient">
                                        <?php if ($isCompleted): ?>
                                            <span style="text-decoration: line-through; opacity: 0.8;">👤 <?= esc(substr($apt['patient_name'] ?? 'Patient', 0, 15)) ?><?= strlen($apt['patient_name'] ?? '') > 15 ? '...' : '' ?></span>
                                            <span style="margin-left: 0.25rem; font-size: 0.6rem; font-weight: 700;">✓ DONE</span>
                                        <?php else: ?>
                                            👤 <?= esc(substr($apt['patient_name'] ?? 'Patient', 0, 15)) ?><?= strlen($apt['patient_name'] ?? '') > 15 ? '...' : '' ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="appointment-type">
                                        <span class="appointment-type-badge appointment-type-<?= $typeBadge ?>">
                                            <?= esc($appointmentType) ?>
                                        </span>
                                    </div>
                                    <div class="appointment-time">
                                        <?= date('g:i A', strtotime($apt['appointment_time'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($dayAppointments) > 3): ?>
                                <div class="calendar-appointment-more">+<?= count($dayAppointments) - 3 ?> more appointment<?= count($dayAppointments) - 3 > 1 ? 's' : '' ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- Add/Edit Schedule Modal -->
<div id="scheduleModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="scheduleModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="scheduleModalTitle" style="max-width: 500px;">
        <header class="panel-header modal-header">
            <h2 id="scheduleModalTitle">Add Schedule</h2>
            <button type="button" class="close" onclick="closeScheduleModal()">&times;</button>
        </header>
        <form id="scheduleForm" class="modal-body">
            <?= csrf_field() ?>
            <input type="hidden" id="doctor_id" name="doctor_id" value="<?= $doctor['id'] ?>">
            <input type="hidden" id="schedule_id" name="schedule_id">
            <div class="form-grid">
                <div class="form-field form-field--full">
                    <label>Select Days <span class="req">*</span></label>
                    <small style="color: #64748b; margin-bottom: 0.75rem; display: block;">Select the days of the week for recurring schedule. This will apply to the whole year.</small>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                        <?php 
                        $daysOfWeek = [
                            'monday' => 'Monday',
                            'tuesday' => 'Tuesday',
                            'wednesday' => 'Wednesday',
                            'thursday' => 'Thursday',
                            'friday' => 'Friday',
                            'saturday' => 'Saturday',
                            'sunday' => 'Sunday'
                        ];
                        foreach ($daysOfWeek as $dayValue => $dayLabel): 
                        ?>
                            <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;">
                                <input type="checkbox" name="selected_days[]" value="<?= $dayValue ?>" class="day-checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                                <span style="font-weight: 500; color: #1e293b;"><?= $dayLabel ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="error" data-error-for="selected_days"></div>
                </div>
                
                <div class="form-field">
                    <label>Start Time <span class="req">*</span></label>
                    <input type="time" name="start_time" id="start_time" required>
                    <div class="error" data-error-for="start_time"></div>
                </div>
                
                <div class="form-field">
                    <label>End Time <span class="req">*</span></label>
                    <input type="time" name="end_time" id="end_time" required>
                    <div class="error" data-error-for="end_time"></div>
                </div>
                
                <div class="form-field form-field--full">
                    <label>
                        <input type="checkbox" name="is_available" id="is_available" value="1" checked style="margin-right: 0.5rem;">
                        Available (uncheck for rest day)
                    </label>
                </div>
                
                <div class="form-field form-field--full">
                    <label>Notes</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Any special notes for this schedule..."></textarea>
                    <div class="error" data-error-for="notes"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeScheduleModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Schedule</button>
            </footer>
        </form>
    </div>
</div>

<style>
.calendar-container {
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}

.calendar-day-header {
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    color: #475569;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #e2e8f0;
}

.calendar-day {
    min-height: 120px;
    background: white;
    padding: 0.75rem;
    cursor: pointer;
    transition: background-color 0.2s;
    position: relative;
}

.calendar-day:hover {
    background: #f8fafc;
}

.calendar-day.empty {
    background: #f8fafc;
    cursor: default;
}

.calendar-day.today {
    background: #eff6ff;
    border: 2px solid #3b82f6;
}

.calendar-day.today .calendar-day-number {
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.calendar-day-number {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.calendar-day-schedules {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.calendar-schedule-item {
    padding: 0.35rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    line-height: 1.3;
}

.calendar-schedule-item.available {
    background: #dcfce7;
    color: #166534;
    border-left: 3px solid #22c55e;
}

.calendar-schedule-item.unavailable {
    background: #fee2e2;
    color: #991b1b;
    border-left: 3px solid #ef4444;
}

.schedule-time {
    font-weight: 500;
}

.calendar-no-schedule {
    font-size: 0.7rem;
    color: #dc2626;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    background: #fee2e2;
    border-radius: 4px;
    border-left: 3px solid #ef4444;
}

.calendar-appointment-item {
    padding: 0.4rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    line-height: 1.3;
    background: #dbeafe;
    color: #1e40af;
    border-left: 3px solid #3b82f6;
    margin-top: 0.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.calendar-appointment-item.completed {
    background: #fecaca !important;
    color: #7f1d1d !important;
    border-left: 4px solid #dc2626 !important;
    border: 2px solid #dc2626 !important;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
    font-weight: 500;
}

.appointment-patient {
    font-weight: 600;
    margin-bottom: 0.15rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.appointment-type-badge {
    display: inline-block;
    padding: 0.15rem 0.4rem;
    border-radius: 3px;
    font-size: 0.6rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.appointment-type-primary {
    background: #3b82f6;
    color: white;
}

.appointment-type-info {
    background: #06b6d4;
    color: white;
}

.appointment-type-warning {
    background: #f59e0b;
    color: white;
}

.appointment-type-success {
    background: #10b981;
    color: white;
}

.appointment-time {
    font-size: 0.65rem;
    color: #1e3a8a;
    font-weight: 500;
}

.calendar-appointment-more {
    font-size: 0.65rem;
    color: #3b82f6;
    font-style: italic;
    padding: 0.2rem 0.5rem;
    margin-top: 0.25rem;
    font-weight: 500;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-dialog {
    position: relative;
    z-index: 1001;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
    color: #1e293b;
}

.modal-header .close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
}

.modal-header .close:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.form-field {
    display: flex;
    flex-direction: column;
}

.form-field--full {
    grid-column: 1 / -1;
}

.form-field label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #475569;
    font-size: 0.875rem;
}

.form-field .req {
    color: #ef4444;
}

.form-field input,
.form-field select,
.form-field textarea {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-primary {
    padding: 0.5rem 1rem;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    padding: 0.5rem 1rem;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

label:has(input.day-checkbox) {
    transition: all 0.2s;
}

label:has(input.day-checkbox):hover {
    background: #f1f5f9 !important;
    border-color: #3b82f6 !important;
}

label:has(input.day-checkbox:checked) {
    background: #eff6ff !important;
    border-color: #3b82f6 !important;
}

label:has(input.day-checkbox:checked) span {
    color: #3b82f6;
    font-weight: 600;
}
</style>

<script>
const doctorId = <?= $doctor['id'] ?>;

function showAddScheduleModal() {
    document.getElementById('scheduleModalTitle').textContent = 'Add Weekly Recurring Schedule';
    document.getElementById('scheduleForm').reset();
    document.getElementById('schedule_id').value = '';
    document.getElementById('doctor_id').value = doctorId;
    document.getElementById('is_available').checked = true;
    document.querySelectorAll('.day-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('start_time').value = '';
    document.getElementById('end_time').value = '';
    document.getElementById('notes').value = '';
    document.getElementById('scheduleModal').style.display = 'flex';
    document.getElementById('scheduleModal').setAttribute('aria-hidden', 'false');
}

function editRecurringSchedule() {
    fetch('<?= base_url('admin/doctors/getRecurringSchedules/' . $doctor['id']) ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('scheduleModalTitle').textContent = 'Edit Weekly Recurring Schedule';
            document.getElementById('scheduleForm').reset();
            
            const schedules = data.schedules || [];
            document.querySelectorAll('.day-checkbox').forEach(cb => cb.checked = false);
            
            if (schedules.length > 0) {
                const firstSchedule = schedules[0];
                document.getElementById('start_time').value = firstSchedule.start_time || '';
                document.getElementById('end_time').value = firstSchedule.end_time || '';
                document.getElementById('notes').value = firstSchedule.notes || '';
                document.getElementById('is_available').checked = firstSchedule.is_available !== false;
                
                schedules.forEach(schedule => {
                    const dayCheckbox = document.querySelector(`input.day-checkbox[value="${schedule.day_of_week}"]`);
                    if (dayCheckbox) {
                        dayCheckbox.checked = true;
                    }
                });
            }
            
            document.getElementById('scheduleModal').style.display = 'flex';
            document.getElementById('scheduleModal').setAttribute('aria-hidden', 'false');
        } else {
            alert('Error: ' + (data.message || 'Failed to load schedules'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading schedules');
    });
}

function openDateSchedule(date, dayOfWeek) {
    showAddScheduleModal();
    const dayCheckbox = document.querySelector(`input.day-checkbox[value="${dayOfWeek}"]`);
    if (dayCheckbox) {
        dayCheckbox.checked = true;
    }
}

function closeScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const selectedDays = Array.from(document.querySelectorAll('.day-checkbox:checked')).map(cb => cb.value);
    
    if (selectedDays.length === 0) {
        alert('Please select at least one day');
        return;
    }
    
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const notes = document.getElementById('notes').value;
    const isAvailable = document.getElementById('is_available').checked;
    
    if (!startTime || !endTime) {
        alert('Please enter start time and end time');
        return;
    }
    
    const schedules = selectedDays.map(day => ({
        doctor_id: doctorId,
        day_of_week: day,
        schedule_date: null,
        start_time: startTime,
        end_time: endTime,
        is_available: isAvailable,
        notes: notes || null
    }));
    
    const isEditMode = document.getElementById('scheduleModalTitle').textContent.includes('Edit');
    
    const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]')?.value || '';
    fetch('<?= base_url('admin/doctors/saveSchedule') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ 
            schedules: schedules,
            action: isEditMode ? 'replace' : 'add'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message || 'Schedule saved successfully!');
            closeScheduleModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save schedule'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the schedule');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const backdrop = document.getElementById('scheduleModalBackdrop');
    if (backdrop) {
        backdrop.addEventListener('click', closeScheduleModal);
    }
});
</script>

<?= $this->endSection() ?>

