<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>💉</span>
                    Treatment Updates
                </h2>
                <p class="page-subtitle">
                    Welcome, <?= esc($user_name ?? session()->get('name') ?? 'Nurse') ?>
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Active Patients</h2>
        <div class="row between">
            <input type="text" id="searchPatients" placeholder="Search patients..." class="search-input">
        </div>
    </header>
    
    <div class="stack">
        <!-- Patient Cards -->
        <?php if (!empty($patients)): ?>
            <?php foreach ($patients as $p): ?>
                <?php
                    $pid = 'P' . str_pad((string) $p['id'], 3, '0', STR_PAD_LEFT);
                    $bloodType = !empty($p['blood_type']) ? $p['blood_type'] : 'O+';
                    $displayRoom = !empty($p['appointment_room_number']) 
                        ? $p['appointment_room_number'] 
                        : (!empty($p['room_number']) ? $p['room_number'] : 'Not assigned');
                    
                    // Calculate age
                    $age = '—';
                    if (!empty($p['date_of_birth']) && $p['date_of_birth'] !== '0000-00-00') {
                        try {
                            $dateStr = $p['date_of_birth'];
                            if (strpos($dateStr, '/') !== false) {
                                $parts = explode('/', $dateStr);
                                if (count($parts) === 3) {
                                    $dateStr = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                }
                            }
                            $birthDate = new DateTime($dateStr);
                            $today = new DateTime();
                            $age = $today->diff($birthDate)->y;
                        } catch (Exception $e) {
                            $age = '—';
                        }
                    }
                ?>
                <div class="card treatment-card">
                    <div class="treatment-header">
                        <div class="patient-summary">
                            <div class="patient-avatar-large">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#3B82F6"/>
                                    <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="#3B82F6"/>
                                </svg>
                            </div>
                            <div class="patient-main-info">
                                <h3><?= esc($p['full_name']) ?></h3>
                                <div class="patient-meta">
                                    <span class="meta-item"><strong>ID:</strong> <?= esc($pid) ?></span>
                                    <span class="meta-item"><strong>Age:</strong> <?= esc($age) ?> years</span>
                                    <span class="meta-item"><strong>Gender:</strong> <?= esc($p['gender']) ?></span>
                                    <span class="meta-item"><strong>Blood:</strong> <?= esc($bloodType) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="patient-status-info">
                            <div class="info-row">
                                <strong>Room:</strong> <span class="room-badge"><?= esc($displayRoom) ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Doctor:</strong> <?= !empty($p['assigned_doctor_name']) ? esc($p['assigned_doctor_name']) : 'Not assigned' ?>
                            </div>
                            <div class="info-row">
                                <strong>Type:</strong> <span class="badge badge-green"><?= ucfirst($p['patient_type'] ?? 'outpatient') ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="treatment-body">
                        <div class="treatment-section">
                            <h4>Vital Signs</h4>
                            <div class="vital-signs-grid">
                                <div class="vital-item">
                                    <label>Time</label>
                                    <input type="time" class="vital-input" id="vital-time-input-<?= $p['id'] ?>" data-patient="<?= $p['id'] ?>">
                                    <div class="time-output" id="vital-time-output-<?= $p['id'] ?>"></div>
                                    <div class="time-history" id="vital-time-history-<?= $p['id'] ?>"></div>
                                </div>
                                <div class="vital-item">
                                    <label>Blood Pressure</label>
                                    <input type="text" class="vital-input" placeholder="120/80 mmHg" data-patient="<?= $p['id'] ?>" data-field="bp">
                                </div>
                                <div class="vital-item">
                                    <label>Heart Rate</label>
                                    <input type="text" class="vital-input" placeholder="72 bpm" data-patient="<?= $p['id'] ?>" data-field="hr">
                                </div>
                                <div class="vital-item">
                                    <label>Temperature</label>
                                    <input type="text" class="vital-input" placeholder="37°C" data-patient="<?= $p['id'] ?>" data-field="temp">
                                </div>
                                <div class="vital-item">
                                    <label>Oxygen Saturation</label>
                                    <input type="text" class="vital-input" placeholder="98%" data-patient="<?= $p['id'] ?>" data-field="o2">
                                </div>
                                <div class="vital-item vital-actions-right">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary btn-small" onclick="saveVitalTime(<?= $p['id'] ?>)">Save Time</button>
                                </div>
                            </div>
                        </div>

                        <div class="treatment-section">
                            <div class="vital-history">
                                <div class="vh-grid vh-header">
                                    <div>Time</div>
                                    <div>Blood Pressure</div>
                                    <div>Heart Rate</div>
                                    <div>Temperature</div>
                                    <div>Oxygen Saturation</div>
                                    <div>Nurse</div>
                                </div>
                                <div id="vital-history-list-<?= $p['id'] ?>" class="vh-grid-list">
                                    <?php 
                                    // Debug: Log what we're trying to display
                                    $patientIdForDebug = $p['id'];
                                    $updates = $treatmentUpdatesByPatient[$patientIdForDebug] ?? [];
                                    // Debug output (remove in production)
                                    if (ENVIRONMENT !== 'production') {
                                        echo "<!-- DEBUG: Patient ID: {$patientIdForDebug}, Updates count: " . count($updates) . " -->";
                                    }
                                    
                                    if (!empty($updates)): 
                                        foreach ($updates as $update): 
                                    ?>
                                        <div class="vh-row">
                                            <div><?= esc($update['time'] ?? '—') ?></div>
                                            <div><?= esc($update['blood_pressure'] ?? '—') ?></div>
                                            <div><?= esc($update['heart_rate'] ?? '—') ?></div>
                                            <div><?= esc($update['temperature'] ?? '—') ?></div>
                                            <div><?= esc($update['oxygen_saturation'] ?? '—') ?></div>
                                            <div><strong><?= esc($update['nurse_name'] ?? '—') ?></strong></div>
                                        </div>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <!-- No vital signs history found for this patient -->
                                    <?php 
                                    endif; 
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="treatment-section">
                            <h4>Treatment Notes</h4>
                            <textarea class="treatment-textarea" placeholder="Enter treatment updates, observations, or care notes..." data-patient="<?= $p['id'] ?>"></textarea>
                        </div>
                        
                        <div class="treatment-actions">
                            <button class="btn btn-primary" onclick="saveTreatmentUpdate(<?= $p['id'] ?>)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Save Update
                            </button>
                            <button class="btn btn-secondary" onclick="clearForm(<?= $p['id'] ?>)">Clear</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <p class="text-center text-muted">No active patients found.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Pending Prescriptions Section -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>⏳ Pending Prescriptions</h2>
        <p>Prescriptions waiting to be given to patients</p>
    </header>
    
    <div class="stack">
        <?php if (!empty($pending_prescriptions)): ?>
            <div class="prescriptions-table-wrapper">
                <table class="prescriptions-table">
                    <thead>
                        <tr>
                            <th>RX#</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Medication</th>
                            <th>Frequency</th>
                            <th>Meal Instruction</th>
                            <th>Duration</th>
                            <th>Pharmacy Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_prescriptions as $rx): ?>
                            <?php 
                            $items = $rx['items_with_stock'] ?? (json_decode($rx['items_json'] ?? '[]', true) ?: []);
                            $firstItem = $items[0] ?? [];
                            $durationDays = $rx['duration_days'] ?? 0;
                            $isDailyTracking = $rx['is_daily_tracking'] ?? false;
                            $currentDay = $rx['current_day'] ?? 1;
                            $daysRemaining = $rx['days_remaining'] ?? 0;
                            $stockInfo = $rx['first_item_stock'] ?? ($firstItem['stock'] ?? null);
                            
                            $stockStatus = $stockInfo['status'] ?? 'unknown';
                            $stockQuantity = $stockInfo['quantity'] ?? null;
                            $stockReorder = $stockInfo['reorder_level'] ?? null;
                            $stockClass = 'stock-indicator';
                            $stockText = 'No stock data';
                            
                            if ($stockStatus === 'in_stock') {
                                $stockClass .= ' stock-in';
                                $stockText = 'In stock' . ($stockQuantity !== null ? ' • ' . $stockQuantity . ' units' : '');
                            } elseif ($stockStatus === 'low_stock') {
                                $stockClass .= ' stock-low';
                                $labelQuantity = $stockQuantity !== null ? $stockQuantity . ' units' : 'Low quantity';
                                $stockText = 'Low stock • ' . $labelQuantity;
                                if ($stockReorder !== null) {
                                    $stockText .= ' (reorder at ' . $stockReorder . ')';
}
                            } elseif ($stockStatus === 'out_of_stock') {
                                $stockClass .= ' stock-out';
                                $stockText = 'Out of stock';
                            } elseif ($stockStatus === 'unknown') {
                                $stockClass .= ' stock-unknown';
                                $stockText = 'No stock data';
                            } else {
                                $stockClass .= ' stock-muted';
                                $stockText = 'Not tracked';
}
                            ?>
                            <tr>
                                <td><strong>RX#<?= str_pad((string)$rx['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= esc($rx['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($rx['doctor_name'] ?? 'N/A') ?></td>
                                <td><?= esc($firstItem['name'] ?? 'N/A') ?></td>
                                <td><?= esc($firstItem['frequency'] ?? '—') ?></td>
                                <td><?= esc($firstItem['meal_instruction'] ?? '—') ?></td>
                                <td>
                                    <?= esc($firstItem['duration'] ?? '—') ?>
                                    <?php if ($durationDays > 0 && $isDailyTracking): ?>
                                        <div class="wizard-progress-inline">
                                            <div class="wizard-steps">
                                                <?php for ($i = 1; $i <= $durationDays; $i++): ?>
                                                    <div class="wizard-step <?= $i <= $currentDay ? 'completed' : '' ?> <?= $i == $currentDay ? 'active' : '' ?>">
                                                        <?= $i ?>
                                                    </div>
                                                    <?php if ($i < $durationDays): ?>
                                                        <div class="wizard-line <?= $i < $currentDay ? 'completed' : '' ?>"></div>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="wizard-text">Day <?= $currentDay ?>/<?= $durationDays ?> (<?= $daysRemaining ?> days left)</div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="<?= $stockClass ?>">
                                        <span class="stock-dot"></span>
                                        <?= esc($stockText) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isDailyTracking): ?>
                                        <span class="status-badge status-pending">
                                            <span class="status-dot"></span>
                                            Day <?= $currentDay ?>/<?= $durationDays ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">
                                            <span class="status-dot"></span>
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn-mark-given" onclick="markAsGiven(<?= $rx['id'] ?>)">
                                        <span>✅</span> Mark as Given
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card">
                <p class="text-muted text-center-empty">No pending prescriptions at this time.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Completed Prescriptions Section -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>✅ Completed Prescriptions</h2>
        <p>Prescriptions that have been given to patients</p>
    </header>
    
    <div class="stack">
        <?php if (!empty($completed_prescriptions)): ?>
            <div class="prescriptions-table-wrapper">
                <table class="prescriptions-table">
                    <thead>
                        <tr>
                            <th>RX#</th>
                            <th>Patient</th>
                            <th>Medication</th>
                            <th>Duration</th>
                            <th>Pharmacy Stock</th>
                            <th>Progress</th>
                            <th>Date Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completed_prescriptions as $rx): ?>
                            <?php 
                            $items = $rx['items_with_stock'] ?? (json_decode($rx['items_json'] ?? '[]', true) ?: []);
                            $firstItem = $items[0] ?? [];
                            $durationDays = $rx['duration_days'] ?? 0;
                            $currentDay = $rx['current_day'] ?? 0;
                            $daysRemaining = $rx['days_remaining'] ?? 0;
                            $isCompleted = $rx['is_completed_duration'] ?? false;
                            $stockInfo = $rx['first_item_stock'] ?? ($firstItem['stock'] ?? null);
                            
                            $stockStatus = $stockInfo['status'] ?? 'unknown';
                            $stockQuantity = $stockInfo['quantity'] ?? null;
                            $stockReorder = $stockInfo['reorder_level'] ?? null;
                            $stockClass = 'stock-indicator';
                            $stockText = 'No stock data';
                            
                            if ($stockStatus === 'in_stock') {
                                $stockClass .= ' stock-in';
                                $stockText = 'In stock' . ($stockQuantity !== null ? ' • ' . $stockQuantity . ' units' : '');
                            } elseif ($stockStatus === 'low_stock') {
                                $stockClass .= ' stock-low';
                                $labelQuantity = $stockQuantity !== null ? $stockQuantity . ' units' : 'Low quantity';
                                $stockText = 'Low stock • ' . $labelQuantity;
                                if ($stockReorder !== null) {
                                    $stockText .= ' (reorder at ' . $stockReorder . ')';
}
                            } elseif ($stockStatus === 'out_of_stock') {
                                $stockClass .= ' stock-out';
                                $stockText = 'Out of stock';
                            } elseif ($stockStatus === 'unknown') {
                                $stockClass .= ' stock-unknown';
                                $stockText = 'No stock data';
                            } else {
                                $stockClass .= ' stock-muted';
                                $stockText = 'Not tracked';
                            }
                            ?>
                            <tr>
                                <td><strong>RX#<?= str_pad((string)$rx['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= esc($rx['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($firstItem['name'] ?? 'N/A') ?></td>
                                <td><?= esc($firstItem['duration'] ?? '—') ?></td>
                                <td>
                                    <span class="<?= $stockClass ?>">
                                        <span class="stock-dot"></span>
                                        <?= esc($stockText) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($durationDays > 0): ?>
                                        <div class="wizard-progress">
                                            <div class="wizard-steps">
                                                <?php for ($i = 1; $i <= $durationDays; $i++): ?>
                                                    <div class="wizard-step <?= $i <= $currentDay ? 'completed' : '' ?> <?= $i == $currentDay ? 'active' : '' ?>">
                                                        <?= $i ?>
                                                    </div>
                                                    <?php if ($i < $durationDays): ?>
                                                        <div class="wizard-line <?= $i < $currentDay ? 'completed' : '' ?>"></div>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="wizard-text">
                                                <?php if ($isCompleted): ?>
                                                    <span class="badge badge-success">✅ Completed</span>
                                                <?php else: ?>
                                                    Day <?= $currentDay ?>/<?= $durationDays ?> (<?= $daysRemaining ?> days left)
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($rx['updated_at']) ? date('M j, Y', strtotime($rx['updated_at'])) : date('M j, Y', strtotime($rx['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card">
                <p class="text-muted text-center-empty">No completed prescriptions yet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- All CSS moved to template.php for centralized styling -->

<script>
// Get nurse name from PHP - must be defined before functions use it
const nurseName = '<?= esc($user_name ?? session()->get('name') ?? 'Unknown') ?>';

// NEW SIMPLE SAVE FUNCTION - Directly saves to database
function saveTreatmentUpdate(patientId) {
    // Get time input
    const timeInput = document.getElementById('vital-time-input-' + patientId);
    let timeValue = '';
    if (timeInput && timeInput.value) {
        const [h, m] = timeInput.value.split(':');
        let hour = parseInt(h, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12; if (hour === 0) hour = 12;
        timeValue = hour + ':' + m + ' ' + ampm;
    }
    
    // Get vital signs directly
    const bpInput = document.querySelector(`.vital-input[data-patient="${patientId}"][data-field="bp"]`);
    const hrInput = document.querySelector(`.vital-input[data-patient="${patientId}"][data-field="hr"]`);
    const tempInput = document.querySelector(`.vital-input[data-patient="${patientId}"][data-field="temp"]`);
    const o2Input = document.querySelector(`.vital-input[data-patient="${patientId}"][data-field="o2"]`);
    const notesTextarea = document.querySelector(`.treatment-textarea[data-patient="${patientId}"]`);
    
    // Prepare simple data structure
    const data = {
        patient_id: patientId,
        time: timeValue,
        blood_pressure: bpInput ? bpInput.value.trim() : '',
        heart_rate: hrInput ? hrInput.value.trim() : '',
        temperature: tempInput ? tempInput.value.trim() : '',
        oxygen_saturation: o2Input ? o2Input.value.trim() : '',
        nurse_name: nurseName,
        notes: notesTextarea ? notesTextarea.value.trim() : ''
    };
    
    // Validate - at least one vital sign or notes
    if (!data.blood_pressure && !data.heart_rate && !data.temperature && !data.oxygen_saturation && !data.notes) {
        alert('Please enter at least one vital sign or treatment notes.');
        return;
    }
    
    console.log('Saving vital signs:', data);
    
    // Show loading
    const saveBtn = document.querySelector(`button[onclick*="saveTreatmentUpdate(${patientId})"]`);
    const originalBtnText = saveBtn ? saveBtn.innerHTML : '';
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Saving...';
    }
    
    // Send to NEW simple endpoint
    fetch('<?= site_url('nurse/saveVitalSigns') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('Save result:', result);
        
        // Re-enable button
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnText;
        }
        
        if (result && result.success) {
            alert('✅ Vital signs saved successfully!');
            // Clear form
            clearForm(patientId);
            // Reload page to show saved data
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('❌ Error: ' + (result?.message || 'Failed to save vital signs'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnText;
        }
        alert('Error saving vital signs: ' + error.message);
    });
}

function clearForm(patientId) {
    // Clear vital signs
    const vitalInputs = document.querySelectorAll(`.vital-input[data-patient="${patientId}"]`);
    vitalInputs.forEach(input => input.value = '');
    
    // Clear treatment notes
    const notesTextarea = document.querySelector(`.treatment-textarea[data-patient="${patientId}"]`);
    if (notesTextarea) notesTextarea.value = '';
}

// Search functionality
document.getElementById('searchPatients').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.treatment-card');
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});

function saveVitalTime(patientId) {
    const input = document.getElementById('vital-time-input-' + patientId);
    const output = document.getElementById('vital-time-output-' + patientId);
    if (!input) return;
    const val = input.value;
    if (!val) {
        alert('Please select a time first.');
        return;
    }
    
    // Format time to HH:MM AM/PM for display
    const [h, m] = val.split(':');
    let hour = parseInt(h, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12; if (hour === 0) hour = 12;
    const display = hour + ':' + m + ' ' + ampm;
    
    if (output) {
        output.textContent = 'Saved time: ' + display;
    }

    // Get vital signs values
    const bpInput = document.querySelector(`.vital-input[data-patient="${patientId}"][data-field="bp"]`);
    const hrInput = document.querySelector(`.vital-input[data-patient="${patientId}"][data-field="hr"]`);
    const tempInput = document.querySelector(`.vital-input[data-patient="${patientId}"][data-field="temp"]`);
    const o2Input = document.querySelector(`.vital-input[data-patient="${patientId}"][data-field="o2"]`);
    const notesTextarea = document.querySelector(`.treatment-textarea[data-patient="${patientId}"]`);
    
    // Prepare data to save
    const data = {
        patient_id: patientId,
        time: display,
        blood_pressure: bpInput ? bpInput.value.trim() : '',
        heart_rate: hrInput ? hrInput.value.trim() : '',
        temperature: tempInput ? tempInput.value.trim() : '',
        oxygen_saturation: o2Input ? o2Input.value.trim() : '',
        nurse_name: nurseName,
        notes: notesTextarea ? notesTextarea.value.trim() : ''
    };
    
    console.log('Saving vital signs via Save Time:', data);
    
    // Show loading on button
    const saveTimeBtn = document.querySelector(`button[onclick="saveVitalTime(${patientId})"]`);
    const originalBtnText = saveTimeBtn ? saveTimeBtn.textContent : '';
    if (saveTimeBtn) {
        saveTimeBtn.disabled = true;
        saveTimeBtn.textContent = 'Saving...';
    }
    
    // Save to database
    fetch('<?= site_url('nurse/saveVitalSigns') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('Save result:', result);
        
        // Re-enable button
        if (saveTimeBtn) {
            saveTimeBtn.disabled = false;
            saveTimeBtn.textContent = originalBtnText;
        }
        
        if (result && result.success) {
            // Show success message
            if (output) {
                output.textContent = '✅ Saved: ' + display;
                output.style.color = '#10b981';
            }
            
            // Display in history
            const history = document.getElementById('vital-time-history-' + patientId);
            if (history) {
                const nowDate = new Date();
                const mm = String(nowDate.getMonth() + 1).padStart(2, '0');
                const dd = String(nowDate.getDate()).padStart(2, '0');
                const yyyy = nowDate.getFullYear();
                const hh = String(nowDate.getHours()).padStart(2, '0');
                const min = String(nowDate.getMinutes()).padStart(2, '0');
                const ampm = parseInt(hh, 10) >= 12 ? 'PM' : 'AM';
                let hour12 = parseInt(hh, 10) % 12; if (hour12 === 0) hour12 = 12;
                const timestamp = `${mm}/${dd}/${yyyy} ${hour12}:${min} ${ampm}`;
                const row = document.createElement('div');
                row.className = 'time-row';
                row.textContent = `Recorded: ${display} by ${nurseName} on ${timestamp}`;
                history.prepend(row);
            }

            // Add to vital history grid
            const list = document.getElementById('vital-history-list-' + patientId);
            if (list) {
                const gridRow = document.createElement('div');
                gridRow.className = 'vh-row';
                const displayNurseName = nurseName && nurseName.trim() !== '' ? nurseName : 'Unknown';
                
                gridRow.innerHTML = `
                    <div>${display}</div>
                    <div>${data.blood_pressure || '—'}</div>
                    <div>${data.heart_rate || '—'}</div>
                    <div>${data.temperature || '—'}</div>
                    <div>${data.oxygen_saturation || '—'}</div>
                    <div><strong>${displayNurseName}</strong></div>
                `;
                list.prepend(gridRow);
            }
            
            // Clear form after successful save
            clearForm(patientId);
            
            // Reload page after 1 second to show data from database
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('❌ Error: ' + (result?.message || 'Failed to save vital signs'));
            if (output) {
                output.textContent = 'Error saving';
                output.style.color = '#ef4444';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (saveTimeBtn) {
            saveTimeBtn.disabled = false;
            saveTimeBtn.textContent = originalBtnText;
        }
        alert('Error saving vital signs: ' + error.message);
        if (output) {
            output.textContent = 'Error saving';
            output.style.color = '#ef4444';
        }
    });
}

// Live/current time handling for each patient card
function setCurrentTimeToInput(inputEl) {
    if (!inputEl) return;
    const now = new Date();
    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    // type="time" expects HH:MM
    const current = `${hh}:${mm}`;
    if (inputEl.value !== current) inputEl.value = current;
}

function initLiveTime() {
    const inputs = document.querySelectorAll('[id^="vital-time-input-"]');
    inputs.forEach(inp => setCurrentTimeToInput(inp));
    // Update every 30 seconds to keep current; more frequent not necessary for minutes
    setInterval(() => {
        inputs.forEach(inp => setCurrentTimeToInput(inp));
    }, 30000);
}

// Initialize live time on page load
document.addEventListener('DOMContentLoaded', function() {
    initLiveTime();
    // Debug: Check if nurse name is set
    if (!nurseName || nurseName.trim() === '' || nurseName === 'Unknown') {
        console.warn('Nurse name not found. Check session or user_name variable.');
    } else {
        console.log('Nurse name loaded:', nurseName);
    }
});

// Function to add entry to vital history
function addToVitalHistory(patientId, time, vitals, nurse) {
    const list = document.getElementById('vital-history-list-' + patientId);
    if (!list) return;
    
    // Ensure nurse name is available
    const displayNurseName = (nurse && nurse.trim() !== '') ? nurse : (nurseName && nurseName.trim() !== '' ? nurseName : 'Unknown');
    
    const gridRow = document.createElement('div');
    gridRow.className = 'vh-row';
    gridRow.innerHTML = `
        <div>${time || '—'}</div>
        <div>${vitals.blood_pressure || '—'}</div>
        <div>${vitals.heart_rate || '—'}</div>
        <div>${vitals.temperature || '—'}</div>
        <div>${vitals.oxygen_saturation || '—'}</div>
        <div><strong>${displayNurseName}</strong></div>
    `;
    list.prepend(gridRow);
}

function markAsGiven(prescriptionId) {
    if (!confirm('Mark this prescription as given to the patient?')) {
        return;
    }

    const btn = event.target.closest('.btn-mark-given');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> Processing...';

    fetch('<?= site_url('nurse/markPrescriptionAsGiven') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ prescription_id: prescriptionId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Prescription marked as given successfully!');
            // Reload page to update both tables
            location.reload();
        } else {
            alert('❌ Error: ' + (data.message || 'Failed to update prescription'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>

<?= $this->endSection() ?>

