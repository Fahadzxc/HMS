<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>My Schedule</h2>
        <p>Manage your weekly availability schedule</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Working Days</div>
                    <div class="kpi-value"><?= count($schedule ?? []) ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">This Week</div>
                    <div class="kpi-value"><?= date('M j - ') . date('M j', strtotime('+6 days')) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Weekly Schedule</h2>
        <div class="row between">
            <span>Set your availability for each day of the week</span>
            <button class="btn-primary" onclick="showAddScheduleModal()">+ Add Schedule</button>
        </div>
    </header>
    
    <div class="stack">
        <div class="schedule-grid">
            <?php 
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $dayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            // Group schedules by day
            $scheduleByDay = [];
            if (isset($schedule) && is_array($schedule)) {
                foreach ($schedule as $sched) {
                    $scheduleByDay[$sched['day_of_week']][] = $sched;
                }
            }
            ?>
            
            <?php foreach ($days as $index => $day): ?>
                <div class="schedule-day-card">
                    <div class="day-header">
                        <h3><?= $dayLabels[$index] ?></h3>
                        <button class="btn-sm btn-secondary" onclick="addScheduleForDay('<?= $day ?>')">+ Add</button>
                    </div>
                    
                    <div class="day-schedules">
                        <?php if (isset($scheduleByDay[$day]) && !empty($scheduleByDay[$day])): ?>
                            <?php foreach ($scheduleByDay[$day] as $sched): ?>
                                <div class="schedule-slot <?= $sched['is_available'] ? 'available' : 'unavailable' ?>">
                                    <div class="time-range">
                                        <?= date('g:i A', strtotime($sched['start_time'])) ?> - 
                                        <?= date('g:i A', strtotime($sched['end_time'])) ?>
                                    </div>
                                    <?php if ($sched['notes']): ?>
                                        <div class="schedule-notes"><?= htmlspecialchars($sched['notes']) ?></div>
                                    <?php endif; ?>
                                    <div class="schedule-actions">
                                        <button class="btn-xs btn-primary" onclick="editSchedule(<?= $sched['id'] ?>)">Edit</button>
                                        <button class="btn-xs btn-danger" onclick="deleteSchedule(<?= $sched['id'] ?>)">Delete</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-schedule">
                                <span class="text-muted">No schedule set</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Add/Edit Schedule Modal -->
<div id="scheduleModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="scheduleModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="scheduleModalTitle">
        <header class="panel-header modal-header">
            <h2 id="scheduleModalTitle">Add Schedule</h2>
            <button type="button" class="close" onclick="closeScheduleModal()">&times;</button>
        </header>
        <form id="scheduleForm" class="modal-body" action="<?= base_url('doctor/updateSchedule') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" id="schedule_id" name="schedule_id">
            <div class="form-grid">
                <div class="form-field">
                    <label>Day of Week <span class="req">*</span></label>
                    <select name="day_of_week" id="day_of_week" required>
                        <option value="">Select Day</option>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                        <option value="sunday">Sunday</option>
                    </select>
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
.schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.schedule-day-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    background: white;
}

.day-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.day-header h3 {
    margin: 0;
    color: #2d3748;
}

.day-schedules {
    min-height: 100px;
}

.schedule-slot {
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border-radius: 6px;
    border-left: 4px solid #48bb78;
}

.schedule-slot.unavailable {
    border-left-color: #f56565;
    background-color: #fed7d7;
}

.schedule-slot.available {
    background-color: #f0fff4;
}

.time-range {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.schedule-notes {
    font-size: 0.875rem;
    color: #718096;
    margin-bottom: 0.5rem;
}

.schedule-actions {
    display: flex;
    gap: 0.5rem;
}

.no-schedule {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 80px;
    border: 2px dashed #e2e8f0;
    border-radius: 6px;
}

.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>

<script>
// Schedule Modal Functions
function showAddScheduleModal() {
    document.getElementById('scheduleModalTitle').textContent = 'Add Schedule';
    document.getElementById('scheduleForm').reset();
    document.getElementById('schedule_id').value = '';
    document.getElementById('scheduleModal').style.display = 'block';
    document.getElementById('scheduleModal').setAttribute('aria-hidden', 'false');
}

function addScheduleForDay(day) {
    showAddScheduleModal();
    document.getElementById('day_of_week').value = day;
}

function closeScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

function editSchedule(scheduleId) {
    // This would typically fetch schedule data via AJAX
    // For now, just open the modal
    document.getElementById('scheduleModalTitle').textContent = 'Edit Schedule';
    document.getElementById('schedule_id').value = scheduleId;
    document.getElementById('scheduleModal').style.display = 'block';
    document.getElementById('scheduleModal').setAttribute('aria-hidden', 'false');
}

function deleteSchedule(scheduleId) {
    if (confirm('Are you sure you want to delete this schedule?')) {
        // Implement delete functionality
        alert('Delete functionality to be implemented');
    }
}

// Form submission
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Debug: Log form data
    console.log('Submitting schedule form...');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
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
