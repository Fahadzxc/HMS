<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Nurse Schedule</h2>
        <p>Manage your work schedule and shift assignments</p>
    </header>
    <div class="stack">
        <div class="row between">
            <input type="text" placeholder="Search schedule..." class="search-input">
            <a href="#" class="btn-primary" onclick="showScheduleModal()">+ Update Schedule</a>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Current Schedule</h2>
        <p>Your current work schedule</p>
    </header>
    <div class="stack">
        <div class="card">
            <p>No schedule assigned yet. Contact your supervisor to set up your work schedule.</p>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Schedule Management</h2>
        <p>Manage your schedule and request changes</p>
    </header>
    <div class="stack">
        <div class="actions-grid">
            <a class="action-tile" href="#" onclick="showScheduleModal()">
                <span class="icon icon-schedule"></span>
                <span>Update Schedule</span>
            </a>
            <a class="action-tile" href="#" onclick="showScheduleChangeModal()">
                <span class="icon icon-request"></span>
                <span>Request Change</span>
            </a>
            <a class="action-tile" href="#" onclick="showShiftSwapModal()">
                <span class="icon icon-swap"></span>
                <span>Shift Swap</span>
            </a>
            <a class="action-tile" href="#" onclick="showOvertimeModal()">
                <span class="icon icon-overtime"></span>
                <span>Overtime Request</span>
            </a>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Upcoming Shifts</h2>
        <p>Your upcoming work shifts</p>
    </header>
    <div class="stack">
        <div class="card">
            <p>No upcoming shifts scheduled.</p>
        </div>
    </div>
</section>

<!-- Update Schedule Modal -->
<div id="scheduleModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="scheduleModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="scheduleTitle">
        <header class="panel-header modal-header">
            <h2 id="scheduleTitle">Update Schedule</h2>
            <button type="button" class="close" onclick="closeScheduleModal()">&times;</button>
        </header>
        <form id="scheduleForm" class="modal-body" action="<?= base_url('nurse/updateSchedule') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Date <span class="req">*</span></label>
                    <input type="date" name="date" required>
                    <div class="error" data-error-for="date"></div>
                </div>
                <div class="form-field">
                    <label>Shift Start <span class="req">*</span></label>
                    <input type="time" name="shift_start" required>
                    <div class="error" data-error-for="shift_start"></div>
                </div>
                <div class="form-field">
                    <label>Shift End <span class="req">*</span></label>
                    <input type="time" name="shift_end" required>
                    <div class="error" data-error-for="shift_end"></div>
                </div>
                <div class="form-field">
                    <label>Department <span class="req">*</span></label>
                    <select name="department" required>
                        <option value="">Select Department</option>
                        <option value="emergency">Emergency</option>
                        <option value="icu">ICU</option>
                        <option value="surgery">Surgery</option>
                        <option value="pediatrics">Pediatrics</option>
                        <option value="cardiology">Cardiology</option>
                        <option value="orthopedics">Orthopedics</option>
                    </select>
                    <div class="error" data-error-for="department"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Notes</label>
                    <textarea name="notes" rows="3"></textarea>
                    <div class="error" data-error-for="notes"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeScheduleModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Schedule</button>
            </footer>
        </form>
    </div>
</div>

<!-- Schedule Change Request Modal -->
<div id="scheduleChangeModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="scheduleChangeModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="scheduleChangeTitle">
        <header class="panel-header modal-header">
            <h2 id="scheduleChangeTitle">Request Schedule Change</h2>
            <button type="button" class="close" onclick="closeScheduleChangeModal()">&times;</button>
        </header>
        <form id="scheduleChangeForm" class="modal-body" action="<?= base_url('nurse/requestScheduleChange') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Current Date <span class="req">*</span></label>
                    <input type="date" name="current_date" required>
                    <div class="error" data-error-for="current_date"></div>
                </div>
                <div class="form-field">
                    <label>Requested Date <span class="req">*</span></label>
                    <input type="date" name="requested_date" required>
                    <div class="error" data-error-for="requested_date"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Reason for Change <span class="req">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="Please explain why you need this schedule change..."></textarea>
                    <div class="error" data-error-for="reason"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeScheduleChangeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Submit Request</button>
            </footer>
        </form>
    </div>
</div>

<!-- Shift Swap Modal -->
<div id="shiftSwapModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="shiftSwapModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="shiftSwapTitle">
        <header class="panel-header modal-header">
            <h2 id="shiftSwapTitle">Request Shift Swap</h2>
            <button type="button" class="close" onclick="closeShiftSwapModal()">&times;</button>
        </header>
        <form id="shiftSwapForm" class="modal-body" action="<?= base_url('nurse/requestShiftSwap') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Date to Swap <span class="req">*</span></label>
                    <input type="date" name="swap_date" required>
                    <div class="error" data-error-for="swap_date"></div>
                </div>
                <div class="form-field">
                    <label>Swap With (Nurse ID) <span class="req">*</span></label>
                    <input type="text" name="swap_with" required placeholder="Enter nurse ID or name">
                    <div class="error" data-error-for="swap_with"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Reason for Swap <span class="req">*</span></label>
                    <textarea name="swap_reason" rows="3" required placeholder="Please explain why you need this shift swap..."></textarea>
                    <div class="error" data-error-for="swap_reason"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeShiftSwapModal()">Cancel</button>
                <button type="submit" class="btn-primary">Request Swap</button>
            </footer>
        </form>
    </div>
</div>

<!-- Overtime Request Modal -->
<div id="overtimeModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="overtimeModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="overtimeTitle">
        <header class="panel-header modal-header">
            <h2 id="overtimeTitle">Request Overtime</h2>
            <button type="button" class="close" onclick="closeOvertimeModal()">&times;</button>
        </header>
        <form id="overtimeForm" class="modal-body" action="<?= base_url('nurse/requestOvertime') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Date <span class="req">*</span></label>
                    <input type="date" name="overtime_date" required>
                    <div class="error" data-error-for="overtime_date"></div>
                </div>
                <div class="form-field">
                    <label>Start Time <span class="req">*</span></label>
                    <input type="time" name="overtime_start" required>
                    <div class="error" data-error-for="overtime_start"></div>
                </div>
                <div class="form-field">
                    <label>End Time <span class="req">*</span></label>
                    <input type="time" name="overtime_end" required>
                    <div class="error" data-error-for="overtime_end"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Reason for Overtime <span class="req">*</span></label>
                    <textarea name="overtime_reason" rows="3" required placeholder="Please explain why overtime is needed..."></textarea>
                    <div class="error" data-error-for="overtime_reason"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeOvertimeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Request Overtime</button>
            </footer>
        </form>
    </div>
</div>

<script>
// Schedule Modal
function showScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Schedule Change Modal
function showScheduleChangeModal() {
    const modal = document.getElementById('scheduleChangeModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeScheduleChangeModal() {
    const modal = document.getElementById('scheduleChangeModal');
    const form = document.getElementById('scheduleChangeForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Shift Swap Modal
function showShiftSwapModal() {
    const modal = document.getElementById('shiftSwapModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeShiftSwapModal() {
    const modal = document.getElementById('shiftSwapModal');
    const form = document.getElementById('shiftSwapForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Overtime Modal
function showOvertimeModal() {
    const modal = document.getElementById('overtimeModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeOvertimeModal() {
    const modal = document.getElementById('overtimeModal');
    const form = document.getElementById('overtimeForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Add backdrop click handlers
document.addEventListener('DOMContentLoaded', function() {
    // Schedule Modal
    const scheduleBackdrop = document.getElementById('scheduleModalBackdrop');
    if (scheduleBackdrop) {
        scheduleBackdrop.addEventListener('click', closeScheduleModal);
    }
    
    // Schedule Change Modal
    const scheduleChangeBackdrop = document.getElementById('scheduleChangeModalBackdrop');
    if (scheduleChangeBackdrop) {
        scheduleChangeBackdrop.addEventListener('click', closeScheduleChangeModal);
    }
    
    // Shift Swap Modal
    const shiftSwapBackdrop = document.getElementById('shiftSwapModalBackdrop');
    if (shiftSwapBackdrop) {
        shiftSwapBackdrop.addEventListener('click', closeShiftSwapModal);
    }
    
    // Overtime Modal
    const overtimeBackdrop = document.getElementById('overtimeModalBackdrop');
    if (overtimeBackdrop) {
        overtimeBackdrop.addEventListener('click', closeOvertimeModal);
    }
});
</script>

<?= $this->endSection() ?>