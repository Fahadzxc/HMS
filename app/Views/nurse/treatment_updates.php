<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Treatment Updates</h2>
        <p>Monitor and update patient treatment progress</p>
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
                                </div>
                                <div id="vital-history-list-<?= $p['id'] ?>" class="vh-grid-list"></div>
                            </div>
                        </div>
                        
                        <div class="treatment-section">
                            <h4>Treatment Notes</h4>
                            <textarea class="treatment-textarea" placeholder="Enter treatment updates, observations, or care notes..." data-patient="<?= $p['id'] ?>"></textarea>
                        </div>

                        <?php $rxList = $prescriptionsByPatient[$p['id']] ?? []; ?>
                        <?php if (!empty($rxList)): ?>
                        <div class="treatment-section">
                            <h4>Latest Prescriptions</h4>
                            <div class="rx-list">
                                <?php foreach ($rxList as $rx): ?>
                                    <div class="rx-item">
                                        <div class="rx-head"><strong>RX#<?= (int) $rx['id'] ?></strong> <span class="text-muted">• <?= date('M j, Y g:i A', strtotime($rx['created_at'])) ?></span> <span class="badge badge-green" style="margin-left:.5rem;"><?= ucfirst($rx['status']) ?></span></div>
                                        <?php foreach (($rx['items'] ?? []) as $it): ?>
                                            <div class="text-muted">- <?= esc($it['name'] ?? '') ?> <?= !empty($it['dosage']) ? '• ' . esc($it['dosage']) : '' ?><?= !empty($it['instructions']) ? ' • ' . esc($it['instructions']) : '' ?></div>
                                        <?php endforeach; ?>
                                        <?php if (!empty($rx['notes'])): ?>
                                            <div class="text-muted">Notes: <?= esc($rx['notes']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
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

<style>
/* Treatment Cards */
.treatment-card {
    margin-bottom: 1.5rem;
    border-left: 4px solid #3B82F6;
}

.treatment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    gap: 2rem;
}

.patient-summary {
    display: flex;
    gap: 1rem;
    flex: 1;
}

.patient-avatar-large {
    flex-shrink: 0;
}

.patient-main-info h3 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
    font-size: 1.25rem;
}

.patient-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    color: #64748b;
    font-size: 0.875rem;
}

.meta-item strong {
    color: #475569;
}

.patient-status-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 200px;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.info-row strong {
    color: #475569;
    min-width: 60px;
}

.room-badge {
    background: #dbeafe;
    color: #1e293b;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.treatment-body {
    padding: 1.5rem;
}

.treatment-section {
    margin-bottom: 1.5rem;
}

.rx-list .rx-item { padding:.5rem .75rem; border:1px solid #e2e8f0; border-radius:.375rem; margin-bottom:.5rem; }
.rx-head { margin-bottom:.25rem; }

.treatment-section h4 {
    margin: 0 0 1rem 0;
    color: #1e293b;
    font-size: 1rem;
    font-weight: 600;
}

.vital-signs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.vital-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.vital-item label {
    color: #475569;
    font-size: 0.875rem;
    font-weight: 500;
}

.vital-input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.vital-input:focus {
    outline: none;
    border-color: #3B82F6;
}

.btn-small { padding: .45rem .8rem; }
.time-output { margin-top: .25rem; color:#475569; font-size:.85rem; }
.time-history { margin-top:.25rem; }
.time-history .time-row { color:#475569; font-size:.85rem; }
.vital-actions-right { display:flex; align-items:flex-end; }

/* Vital history grid */
.vh-grid { display:grid; grid-template-columns: 200px 1fr 1fr 1fr 1fr; gap:.5rem; align-items:center; }
.vh-header { font-weight:600; color:#475569; }
.vh-grid-list { margin-top:.5rem; }
.vh-grid-list .vh-row { display:grid; grid-template-columns: 200px 1fr 1fr 1fr 1fr; gap:.5rem; padding:.35rem .5rem; border:1px solid #e2e8f0; border-radius:.375rem; margin-bottom:.35rem; }

.treatment-textarea {
    width: 100%;
    min-height: 100px;
    padding: 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-family: inherit;
    resize: vertical;
    transition: border-color 0.2s;
}

.treatment-textarea:focus {
    outline: none;
    border-color: #3B82F6;
}

.treatment-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 1rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #3B82F6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #e2e8f0;
    color: #475569;
}

.btn-secondary:hover {
    background: #cbd5e1;
}

.text-center {
    text-align: center;
}

.text-muted {
    color: #64748b;
}

/* Responsive */
@media (max-width: 768px) {
    .treatment-header {
        flex-direction: column;
    }
    
    .patient-status-info {
        width: 100%;
    }
    
    .vital-signs-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function saveTreatmentUpdate(patientId) {
    // Collect vital signs
    const vitalInputs = document.querySelectorAll(`.vital-input[data-patient="${patientId}"]`);
    const vitals = {};
    vitalInputs.forEach((input, index) => {
        const labels = ['blood_pressure', 'heart_rate', 'temperature', 'oxygen_saturation'];
        vitals[labels[index]] = input.value;
    });
    
    // Get treatment notes
    const notesTextarea = document.querySelector(`.treatment-textarea[data-patient="${patientId}"]`);
    const notes = notesTextarea ? notesTextarea.value : '';
    
    // Validate
    if (!notes && !Object.values(vitals).some(v => v)) {
        alert('Please enter at least vital signs or treatment notes.');
        return;
    }
    
    // Prepare data
    const data = {
        patient_id: patientId,
        vitals: vitals,
        notes: notes,
        timestamp: new Date().toISOString()
    };
    
    // Send to server
    fetch('<?= site_url('nurse/updateTreatment') ?>', {
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
            alert('Treatment update saved successfully!');
            clearForm(patientId);
        } else {
            alert('Error saving treatment update: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving treatment update. Please try again.');
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

        // Also push a vitals record below, aligned with placeholders
        const list = document.getElementById('vital-history-list-' + patientId);
        if (list) {
            const gridRow = document.createElement('div');
            gridRow.className = 'vh-row';
            // Get vital values for this patient
            const container = input.closest('.treatment-section');
            const getVal = (selector) => {
                const el = container.parentElement.querySelector(selector + `[data-patient="${patientId}"]`);
                return el ? (el.value || el.placeholder) : '';
            };
            const bp = getVal('input[data-field="bp"]');
            const hr = getVal('input[data-field="hr"]');
            const temp = getVal('input[data-field="temp"]');
            const o2 = getVal('input[data-field="o2"]');

            gridRow.innerHTML = `
                <div>${display}</div>
                <div>${bp}</div>
                <div>${hr}</div>
                <div>${temp}</div>
                <div>${o2}</div>
            `;
            list.prepend(gridRow);
        }
    }
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
document.addEventListener('DOMContentLoaded', initLiveTime);

const nurseName = '<?= esc($user_name ?? session()->get('name')) ?>';
</script>

<?= $this->endSection() ?>

