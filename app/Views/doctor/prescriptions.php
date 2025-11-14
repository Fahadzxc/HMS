<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<!-- Header Section -->
<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🩺</span>
                    Doctor Dashboard – Prescription Management
                </h2>
                <p class="page-subtitle">
                    Welcome, <?= esc($user_name ?? 'Dr. ' . session()->get('name') ?? 'Doctor') ?>, M.D.
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- New Prescription Form Section -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>📋 New Prescription Form</h2>
    </header>
    
    <div class="stack">
        <div class="card prescription-form-card">
            <!-- Patient Information -->
            <div class="form-section">
                <h3 class="section-title">Patient Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Select Patient <span class="req">*</span></label>
                        <select id="rx_patient" class="form-input" required>
                            <option value="">Select patient...</option>
                            <?php foreach (($patients ?? []) as $pt): ?>
                                <option value="<?= (int) $pt['id'] ?>" 
                                        data-age="<?= esc($pt['age'] ?? '') ?>"
                                        data-gender="<?= esc($pt['gender'] ?? '') ?>"
                                        data-name="<?= esc($pt['full_name']) ?>">
                                    <?= esc($pt['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="text" id="patient_age" class="form-input" readonly placeholder="—">
                    </div>
                    <div class="form-group">
                        <label>Sex</label>
                        <input type="text" id="patient_gender" class="form-input" readonly placeholder="—">
                    </div>
                </div>
            </div>

            <!-- Diagnosis / Notes -->
            <div class="form-section">
                <label>Diagnosis / Notes <span class="req">*</span></label>
                <textarea id="rx_notes" class="form-textarea" rows="3" placeholder="Enter diagnosis, symptoms, or general instructions..." required></textarea>
            </div>

            <!-- Medication Table -->
            <div class="form-section">
                <h3 class="section-title">Medication Table</h3>
                <div class="medication-table-wrapper">
                    <table class="medication-table">
                        <thead>
                            <tr>
                                <th>Medication</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Meal Instruction</th>
                                <th>Duration</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="rx_items_container">
                            <!-- Dynamic rows will be added here -->
                        </tbody>
                    </table>
                    <button type="button" class="btn-add-medication" onclick="addRxItem()">
                        <span>➕</span> Add New Medication
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="savePrescription()">
                    <span>💾</span> Save Prescription
                </button>
                <button type="button" class="btn btn-clear" onclick="clearForm()">
                    <span>❌</span> Clear Form
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Prescription Preview Card (shown after saving) -->
<div id="prescriptionPreview" class="prescription-preview-card" style="display: none;">
    <div class="preview-header">
        <h3>📄 Prescription Preview</h3>
        <button type="button" class="btn-close-preview" onclick="closePreview()">&times;</button>
    </div>
    <div id="previewContent" class="preview-content">
        <!-- Preview content will be generated here -->
    </div>
</div>

<!-- Recent Prescriptions Section -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>🕐 Recent Prescriptions</h2>
    </header>
    
    <div class="stack">
        <?php if (!empty($prescriptions)): ?>
            <div class="prescriptions-table-wrapper">
                <table class="prescriptions-table">
                    <thead>
                        <tr>
                            <th>RX#</th>
                            <th>Patient</th>
                            <th>Medication</th>
                            <th>Frequency</th>
                            <th>Meal</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptions as $rx): ?>
                            <?php 
                            $items = json_decode($rx['items_json'] ?? '[]', true) ?: [];
                            $firstItem = $items[0] ?? [];
                            ?>
                            <tr>
                                <td><strong>RX#<?= str_pad((string)$rx['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= esc($rx['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($firstItem['name'] ?? 'N/A') ?></td>
                                <td><?= esc($firstItem['frequency'] ?? '—') ?></td>
                                <td><?= esc($firstItem['meal_instruction'] ?? '—') ?></td>
                                <td><?= esc($firstItem['duration'] ?? '—') ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($rx['status'] ?? 'pending') ?>">
                                        <span class="status-dot"></span>
                                        <?= ucfirst($rx['status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($rx['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card">
                <p class="text-muted text-center-empty">No prescriptions yet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Patient selection handler
document.getElementById('rx_patient').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        document.getElementById('patient_age').value = selectedOption.dataset.age || '—';
        document.getElementById('patient_gender').value = selectedOption.dataset.gender || '—';
    } else {
        document.getElementById('patient_age').value = '';
        document.getElementById('patient_gender').value = '';
    }
});

// Add medication row
function addRxItem() {
    const container = document.getElementById('rx_items_container');
    const row = document.createElement('tr');
    row.className = 'medication-row';
    row.innerHTML = `
        <td>
            <select class="form-input form-input-sm" data-field="med_id" onchange="onMedChange(this)" required>
                <option value="">Select medication...</option>
                <?php foreach (($medications ?? []) as $m): ?>
                    <option value="<?= (int) $m['id'] ?>" 
                            data-name="<?= esc(($m['name'] ?? '') . (!empty($m['strength']) ? ' ' . $m['strength'] : '')) ?>" 
                            data-dosage="<?= esc($m['default_dosage'] ?? '') ?>">
                        <?= esc($m['name'] ?? '') ?> <?= esc($m['strength'] ?? '') ?><?= !empty($m['form']) ? ' (' . esc($m['form']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" data-field="name">
        </td>
        <td>
            <input type="text" class="form-input form-input-sm" placeholder="e.g., 1 capsule" data-field="dosage" required>
        </td>
        <td>
            <select class="form-input form-input-sm" data-field="frequency" required>
                <option value="">Select...</option>
                <option value="Once a day">Once a day</option>
                <option value="2x/day">2x/day</option>
                <option value="3x/day">3x/day</option>
                <option value="Every 6 hours">Every 6 hours</option>
                <option value="Every 8 hours">Every 8 hours</option>
            </select>
        </td>
        <td>
            <select class="form-input form-input-sm" data-field="meal_instruction" required>
                <option value="">Select...</option>
                <option value="After breakfast">After breakfast</option>
                <option value="After lunch">After lunch</option>
                <option value="After dinner">After dinner</option>
                <option value="Before meals">Before meals</option>
                <option value="After meals">After meals</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-input form-input-sm" placeholder="e.g., 7 days" data-field="duration" required>
        </td>
        <td>
            <input type="text" class="form-input form-input-sm" placeholder="Additional notes..." data-field="notes">
        </td>
        <td>
            <button type="button" class="btn-remove" onclick="this.closest('tr').remove()" title="Remove">
                🗑️
            </button>
        </td>
    `;
    container.appendChild(row);
}

// Medication change handler
function onMedChange(sel) {
    const opt = sel.selectedOptions[0];
    if (!opt) return;
    const row = sel.closest('tr');
    const dosage = row.querySelector('[data-field="dosage"]');
    const nameHidden = row.querySelector('[data-field="name"]');
    if (dosage && opt.dataset.dosage) dosage.value = opt.dataset.dosage;
    if (nameHidden) nameHidden.value = opt.dataset.name || opt.textContent;
}

// Collect medication items
function collectItems() {
    const rows = document.querySelectorAll('#rx_items_container .medication-row');
    const items = [];
    rows.forEach(r => {
        const item = {};
        r.querySelectorAll('[data-field]').forEach(inp => {
            item[inp.dataset.field] = inp.value.trim();
        });
        if ((!item.name || item.name.length === 0) && item.med_id) {
            const sel = r.querySelector('select[data-field="med_id"]');
            if (sel && sel.selectedOptions.length) {
                item.name = sel.selectedOptions[0].dataset.name || sel.options[sel.selectedIndex].text;
            }
        }
        if (item.med_id || item.name) items.push(item);
    });
    return items;
}

// Save prescription
function savePrescription() {
    const patientId = document.getElementById('rx_patient').value;
    const notes = document.getElementById('rx_notes').value;
    const items = collectItems();

    if (!patientId) {
        alert('Please select a patient.');
        return;
    }
    if (!notes.trim()) {
        alert('Please enter diagnosis/notes.');
        return;
    }
    if (items.length === 0) {
        alert('Please add at least one medication item.');
        return;
    }

    const saveBtn = document.querySelector('button[onclick="savePrescription()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span>⏳</span> Saving...';

    fetch('<?= site_url('doctor/prescriptions/create') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ patient_id: patientId, items: items, notes: notes })
    }).then(r => r.json()).then(res => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
        
        if (res.success) {
            showPrescriptionPreview(patientId, notes, items);
            alert('✅ Prescription saved successfully!');
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else {
            alert('❌ ' + (res.message || 'Failed to save prescription.'));
        }
    }).catch(err => {
        console.error(err);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
        alert('❌ Network error. Please try again.');
    });
}

// Show prescription preview
function showPrescriptionPreview(patientId, notes, items) {
    const patientSelect = document.getElementById('rx_patient');
    const selectedOption = patientSelect.options[patientSelect.selectedIndex];
    const patientName = selectedOption ? selectedOption.dataset.name || selectedOption.text : 'N/A';
    const patientAge = document.getElementById('patient_age').value || '—';
    const patientGender = document.getElementById('patient_gender').value || '—';
    const doctorName = '<?= esc($user_name ?? session()->get('name') ?? 'Dr. ' . session()->get('name') ?? 'Doctor') ?>';
    const rxNumber = 'RX#' + String(Date.now()).slice(-3);
    const currentDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

    let medicationsHtml = '';
    items.forEach((item, index) => {
        medicationsHtml += `
            <div class="medication-preview-item">
                <strong>${index + 1}. ${item.name || 'Medication'}</strong>
                <div class="medication-details">
                    <div><span class="detail-label">Dosage:</span> ${item.dosage || '—'}</div>
                    <div><span class="detail-label">Frequency:</span> ${item.frequency || '—'}</div>
                    <div><span class="detail-label">Meal:</span> ${item.meal_instruction || '—'}</div>
                    <div><span class="detail-label">Duration:</span> ${item.duration || '—'}</div>
                    ${item.notes ? `<div><span class="detail-label">Notes:</span> ${item.notes}</div>` : ''}
                </div>
            </div>
        `;
    });

    const previewHtml = `
        <div class="preview-section">
            <div class="preview-row">
                <span class="preview-label">Prescription No:</span> <strong>${rxNumber}</strong>
            </div>
            <div class="preview-row">
                <span class="preview-label">Date:</span> ${currentDate}
            </div>
        </div>
        
        <div class="preview-section">
            <div class="preview-row">
                <span class="preview-label">Patient:</span> ${patientName} | Age: ${patientAge} | Sex: ${patientGender.charAt(0).toUpperCase()}
            </div>
            <div class="preview-row">
                <span class="preview-label">Diagnosis:</span> ${notes}
            </div>
        </div>
        
        <div class="preview-section">
            <div class="preview-row">
                <span class="preview-label">Medications:</span>
            </div>
            ${medicationsHtml}
        </div>
        
        <div class="preview-section">
            <div class="preview-row">
                <span class="preview-label">Doctor:</span> ${doctorName}
            </div>
            <div class="preview-row">
                <span class="preview-label">PRC No.:</span> — | <span class="preview-label">PTR No.:</span> —
            </div>
            <div class="preview-row">
                <span class="preview-label">Signature:</span> <span style="border-bottom: 1px solid #000; min-width: 200px; display: inline-block;">&nbsp;</span>
            </div>
        </div>
    `;

    document.getElementById('previewContent').innerHTML = previewHtml;
    document.getElementById('prescriptionPreview').style.display = 'block';
    document.getElementById('prescriptionPreview').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Close preview
function closePreview() {
    document.getElementById('prescriptionPreview').style.display = 'none';
}

// Clear form
function clearForm() {
    if (confirm('Are you sure you want to clear the form? All entered data will be lost.')) {
        document.getElementById('rx_patient').value = '';
        document.getElementById('patient_age').value = '';
        document.getElementById('patient_gender').value = '';
        document.getElementById('rx_notes').value = '';
        document.getElementById('rx_items_container').innerHTML = '';
        document.getElementById('prescriptionPreview').style.display = 'none';
        addRxItem(); // Add one empty row
    }
}

// Initialize with one row
addRxItem();
</script>

<!-- All CSS moved to template.php for centralized styling -->

<?= $this->endSection() ?>
