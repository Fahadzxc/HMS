<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <h2>Nurse Schedule Management</h2>
        <p>Manage nurse schedules and shift assignments</p>
    </header>
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>All Nurses</span>
                <span><?= count($nurses ?? []) ?> total</span>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <div class="nurses-grid">
        <?php if (!empty($nurses)): ?>
            <?php foreach ($nurses as $nurse): ?>
                <div class="nurse-card">
                    <div class="nurse-header">
                        <div class="nurse-info">
                            <div class="nurse-avatar">
                                <div class="avatar-circle">
                                    <?= strtoupper(substr($nurse['name'] ?? 'N', 0, 2)) ?>
                                </div>
                            </div>
                            <div class="nurse-details">
                                <h3><?= esc($nurse['name'] ?? 'N/A') ?></h3>
                                <p class="nurse-email"><?= esc($nurse['email'] ?? '—') ?></p>
                                <span class="badge badge<?= match($nurse['status'] ?? '') {
                                    'active' => '-success',
                                    'inactive' => '-secondary',
                                    default => '-info'
                                } ?>">
                                    <?= ucfirst($nurse['status'] ?? 'unknown') ?>
                                </span>
                            </div>
                        </div>
                        <div class="nurse-stats">
                            <div class="stat-item">
                                <span class="stat-value"><?= $nurse['total_shifts'] ?? 0 ?></span>
                                <span class="stat-label">Total Shifts</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value"><?= !empty($nurse['shift_types']) ? count(explode(',', $nurse['shift_types'])) : 0 ?></span>
                                <span class="stat-label">Shift Types</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($nurse['schedules'])): ?>
                        <div class="schedules-section">
                            <h4>Weekly Schedule</h4>
                            <div class="schedule-grid">
                                <?php 
                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                $schedulesByDay = [];
                                foreach ($nurse['schedules'] as $schedule) {
                                    $schedulesByDay[$schedule['day_of_week']] = $schedule;
                                }
                                ?>
                                <?php foreach ($days as $day): ?>
                                    <div class="day-schedule">
                                        <div class="day-name"><?= ucfirst($day) ?></div>
                                        <?php if (isset($schedulesByDay[$day])): ?>
                                            <?php $schedule = $schedulesByDay[$day]; ?>
                                            <div class="shift-info">
                                                <span class="shift-type shift-<?= $schedule['shift_type'] ?>">
                                                    <?= ucfirst($schedule['shift_type']) ?>
                                                </span>
                                                <div class="shift-time">
                                                    <?= date('g:i A', strtotime($schedule['start_time'])) ?> - 
                                                    <?= date('g:i A', strtotime($schedule['end_time'])) ?>
                                                </div>
                                                <?php if (!empty($schedule['ward_assignment'])): ?>
                                                    <div class="ward-assignment">
                                                        Ward: <?= esc($schedule['ward_assignment']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-shift">Off</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-schedule">
                            <p class="text-muted">No schedule assigned</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="nurse-actions">
                        <button class="btn btn-primary" onclick="editSchedule(<?= $nurse['id'] ?>, '<?= esc($nurse['name']) ?>')">
                            <?= !empty($nurse['schedules']) ? 'Edit Schedule' : 'Create Schedule' ?>
                        </button>
                        <?php if (!empty($nurse['schedules'])): ?>
                            <button class="btn btn-secondary" onclick="viewSchedule(<?= $nurse['id'] ?>, '<?= esc($nurse['name']) ?>')">
                                View Details
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <div class="text-center text-muted" style="padding: 2rem;">
                    <p>No nurses found.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Schedule Edit Modal -->
<div id="scheduleModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Edit Schedule</h3>
            <span class="close" onclick="closeScheduleModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="scheduleForm">
                <input type="hidden" id="nurseId" name="nurse_id">
                <div class="schedule-form-grid">
                    <?php 
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    foreach ($days as $day): 
                    ?>
                        <div class="day-form-section">
                            <h4><?= ucfirst($day) ?></h4>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="<?= $day ?>_enabled" id="<?= $day ?>_enabled" onchange="toggleDaySchedule('<?= $day ?>')">
                                    Schedule this day
                                </label>
                            </div>
                            <div id="<?= $day ?>_schedule" class="day-schedule-form" style="display: none;">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Shift Type</label>
                                        <select name="<?= $day ?>_shift_type">
                                            <option value="morning">Morning</option>
                                            <option value="afternoon">Afternoon</option>
                                            <option value="night">Night</option>
                                            <option value="double">Double</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Start Time</label>
                                        <input type="time" name="<?= $day ?>_start_time">
                                    </div>
                                    <div class="form-group">
                                        <label>End Time</label>
                                        <input type="time" name="<?= $day ?>_end_time">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Ward Assignment</label>
                                    <input type="text" name="<?= $day ?>_ward_assignment" placeholder="e.g., ICU, Emergency, General">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeScheduleModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.nurses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.nurse-card {
    background: white;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.nurse-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.nurse-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.nurse-avatar {
    flex-shrink: 0;
}

.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}

.nurse-details h3 {
    margin: 0 0 0.25rem 0;
    color: #1e293b;
    font-size: 1.125rem;
}

.nurse-email {
    margin: 0 0 0.5rem 0;
    color: #64748b;
    font-size: 0.875rem;
}

.nurse-stats {
    display: flex;
    gap: 1rem;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #3b82f6;
}

.stat-label {
    font-size: 0.75rem;
    color: #64748b;
}

.schedules-section {
    padding: 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.schedules-section h4 {
    margin: 0 0 1rem 0;
    color: #374151;
}

.schedule-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}

.day-schedule {
    text-align: center;
    padding: 0.75rem 0.5rem;
    background: #f8fafc;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.day-name {
    font-weight: 600;
    font-size: 0.75rem;
    color: #374151;
    margin-bottom: 0.5rem;
}

.shift-info {
    font-size: 0.75rem;
}

.shift-type {
    display: inline-block;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.shift-morning { background: #fef3c7; color: #92400e; }
.shift-afternoon { background: #fed7aa; color: #9a3412; }
.shift-night { background: #ddd6fe; color: #5b21b6; }
.shift-double { background: #fecaca; color: #991b1b; }

.shift-time {
    color: #4b5563;
    margin-bottom: 0.25rem;
}

.ward-assignment {
    color: #6b7280;
    font-size: 0.625rem;
}

.no-shift {
    color: #9ca3af;
    font-style: italic;
    font-size: 0.75rem;
}

.no-schedule {
    padding: 1.5rem;
    text-align: center;
}

.nurse-actions {
    padding: 1rem 1.5rem;
    display: flex;
    gap: 0.75rem;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    border: none;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
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

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success { background-color: #c6f6d5; color: #22543d; }
.badge-secondary { background-color: #e2e8f0; color: #4a5568; }
.badge-info { background-color: #bee3f8; color: #2a4365; }

/* Modal Styles */
.modal {
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
    margin: 2% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
    margin: 0;
    color: #1e293b;
}

.close {
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    color: #6b7280;
}

.close:hover {
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}

.schedule-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.day-form-section {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 1rem;
}

.day-form-section h4 {
    margin: 0 0 1rem 0;
    color: #374151;
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
.form-group select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.form-group input[type="checkbox"] {
    width: auto;
    margin-right: 0.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.75rem;
}

.day-schedule-form {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f3f4f6;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.text-muted {
    color: #64748b;
}

@media (max-width: 768px) {
    .nurses-grid {
        grid-template-columns: 1fr;
    }
    
    .nurse-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .nurse-stats {
        gap: 1rem;
    }
    
    .schedule-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .schedule-form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let currentNurseId = null;

function editSchedule(nurseId, nurseName) {
    currentNurseId = nurseId;
    document.getElementById('modalTitle').textContent = `Edit Schedule - ${nurseName}`;
    document.getElementById('nurseId').value = nurseId;
    
    // Reset form
    document.getElementById('scheduleForm').reset();
    
    // Hide all day schedule forms
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    days.forEach(day => {
        document.getElementById(`${day}_enabled`).checked = false;
        document.getElementById(`${day}_schedule`).style.display = 'none';
    });
    
    // Load existing schedule
    fetch(`<?= site_url('admin/nurses/getSchedule') ?>/${nurseId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.schedule) {
                data.schedule.forEach(schedule => {
                    const day = schedule.day_of_week;
                    document.getElementById(`${day}_enabled`).checked = true;
                    document.getElementById(`${day}_schedule`).style.display = 'block';
                    document.querySelector(`[name="${day}_shift_type"]`).value = schedule.shift_type;
                    document.querySelector(`[name="${day}_start_time"]`).value = schedule.start_time;
                    document.querySelector(`[name="${day}_end_time"]`).value = schedule.end_time;
                    document.querySelector(`[name="${day}_ward_assignment"]`).value = schedule.ward_assignment || '';
                });
            }
        })
        .catch(error => {
            console.error('Error loading schedule:', error);
        });
    
    document.getElementById('scheduleModal').style.display = 'block';
}

function viewSchedule(nurseId, nurseName) {
    // For now, just open the edit modal in view mode
    editSchedule(nurseId, nurseName);
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
}

function toggleDaySchedule(day) {
    const enabled = document.getElementById(`${day}_enabled`).checked;
    const scheduleDiv = document.getElementById(`${day}_schedule`);
    scheduleDiv.style.display = enabled ? 'block' : 'none';
}

// Handle form submission
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const nurseId = formData.get('nurse_id');
    const schedules = [];
    
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    days.forEach(day => {
        const enabled = formData.get(`${day}_enabled`);
        if (enabled) {
            schedules.push({
                day_of_week: day,
                shift_type: formData.get(`${day}_shift_type`),
                start_time: formData.get(`${day}_start_time`),
                end_time: formData.get(`${day}_end_time`),
                ward_assignment: formData.get(`${day}_ward_assignment`) || null
            });
        }
    });
    
    // Send to server
    fetch('<?= site_url('admin/nurses/createSchedule') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            nurse_id: nurseId,
            schedules: schedules
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Schedule updated successfully!');
            closeScheduleModal();
            location.reload(); // Refresh to show updated schedule
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the schedule.');
    });
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('scheduleModal');
    if (event.target === modal) {
        closeScheduleModal();
    }
});
</script>
<?= $this->endSection() ?>
