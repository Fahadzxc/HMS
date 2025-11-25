<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📅</span>
                    My Schedule
                </h2>
                <p class="page-subtitle">
                    Manage your monthly availability schedule
                    <span class="date-text"> • <?= $monthName ?> <?= $currentYear ?></span>
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="<?= base_url('doctor/schedule?month=' . $prevMonth . '&year=' . $prevYear) ?>" style="padding: 0.6rem 1rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center;">← Prev</a>
                <a href="<?= base_url('doctor/schedule?month=' . date('n') . '&year=' . date('Y')) ?>" style="padding: 0.6rem 1rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center;">Today</a>
                <a href="<?= base_url('doctor/schedule?month=' . $nextMonth . '&year=' . $nextYear) ?>" style="padding: 0.6rem 1rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center;">Next →</a>
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
                            <div class="calendar-no-schedule">No schedule</div>
                        <?php endif; ?>
                        
                        <?php if (!empty($dayAppointments)): ?>
                            <?php foreach (array_slice($dayAppointments, 0, 3) as $apt): ?>
                                <div class="calendar-appointment-item" title="<?= esc($apt['patient_name'] ?? 'Patient') ?> - <?= date('g:i A', strtotime($apt['appointment_time'])) ?>">
                                    <div class="appointment-patient">
                                        👤 <?= esc(substr($apt['patient_name'] ?? 'Patient', 0, 15)) ?><?= strlen($apt['patient_name'] ?? '') > 15 ? '...' : '' ?>
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
        <form id="scheduleForm" class="modal-body" action="<?= base_url('doctor/updateSchedule') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" id="schedule_id" name="schedule_id">
            <div class="form-grid">
                <div class="form-field">
                    <label>Selected Date <span class="req">*</span></label>
                    <input type="date" name="schedule_date" id="schedule_date" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                    <small style="color: #64748b; margin-top: 0.25rem; display: block;" id="selected_date_label"></small>
                    <input type="hidden" name="day_of_week" id="day_of_week">
                    <div class="error" data-error-for="day_of_week"></div>
                </div>
                
                <div class="form-field">
                    <label>Available <span class="req">*</span></label>
                    <select name="is_available" id="is_available" required>
                        <option value="1">Yes - Available</option>
                        <option value="0">No - Not Available</option>
                    </select>
                    <div class="error" data-error-for="is_available"></div>
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

.calendar-schedule-more {
    font-size: 0.7rem;
    color: #64748b;
    font-style: italic;
    padding: 0.25rem 0.5rem;
}

.calendar-no-schedule {
    font-size: 0.7rem;
    color: #94a3b8;
    font-style: italic;
    padding: 0.25rem 0.5rem;
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
}

.appointment-patient {
    font-weight: 600;
    margin-bottom: 0.15rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
</style>

<script>
function showAddScheduleModal() {
    document.getElementById('scheduleModalTitle').textContent = 'Add Schedule';
    document.getElementById('scheduleForm').reset();
    document.getElementById('schedule_id').value = '';
    document.getElementById('schedule_date').value = '';
    document.getElementById('day_of_week').value = '';
    document.getElementById('selected_date_label').textContent = '';
    document.getElementById('scheduleModal').style.display = 'flex';
    document.getElementById('scheduleModal').setAttribute('aria-hidden', 'false');
}

function openDateSchedule(date, dayOfWeek) {
    showAddScheduleModal();
    const dateInput = document.getElementById('schedule_date');
    const dayOfWeekInput = document.getElementById('day_of_week');
    const dateLabel = document.getElementById('selected_date_label');
    
    dateInput.value = date;
    dayOfWeekInput.value = dayOfWeek;
    
    // Show formatted date
    const dateObj = new Date(date + 'T00:00:00'); // Add time to avoid timezone issues
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const formattedDate = dayNames[dateObj.getDay()] + ', ' + monthNames[dateObj.getMonth()] + ' ' + dateObj.getDate() + ', ' + dateObj.getFullYear();
    dateLabel.textContent = formattedDate;
}

// Auto-update day_of_week when date changes
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('schedule_date');
    const dayOfWeekInput = document.getElementById('day_of_week');
    const dateLabel = document.getElementById('selected_date_label');
    
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            if (this.value) {
                const dateObj = new Date(this.value + 'T00:00:00');
                const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                const dayLabels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const dayOfWeek = dayNames[dateObj.getDay()];
                const dayLabel = dayLabels[dateObj.getDay()];
                
                dayOfWeekInput.value = dayOfWeek;
                dateLabel.textContent = dayLabel + ', ' + this.value;
            }
        });
    }
});

function closeScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

function editSchedule(scheduleId) {
    document.getElementById('scheduleModalTitle').textContent = 'Edit Schedule';
    document.getElementById('schedule_id').value = scheduleId;
    document.getElementById('scheduleModal').style.display = 'flex';
    document.getElementById('scheduleModal').setAttribute('aria-hidden', 'false');
}

function deleteSchedule(scheduleId) {
    if (confirm('Are you sure you want to delete this schedule?')) {
        alert('Delete functionality to be implemented');
    }
}

// Form submission
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= base_url('doctor/updateSchedule') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Schedule saved successfully!');
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

// Backdrop click handler
document.addEventListener('DOMContentLoaded', function() {
    const backdrop = document.getElementById('scheduleModalBackdrop');
    if (backdrop) {
        backdrop.addEventListener('click', closeScheduleModal);
    }
});
</script>

<?= $this->endSection() ?>
