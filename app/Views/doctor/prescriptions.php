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
                                <?php $pType = strtolower($pt['patient_type'] ?? 'outpatient'); ?>
                                <option value="<?= (int) $pt['id'] ?>" 
                                        data-age="<?= esc($pt['age'] ?? '') ?>"
                                        data-gender="<?= esc($pt['gender'] ?? '') ?>"
                                        data-name="<?= esc($pt['full_name']) ?>"
                                        data-type="<?= esc($pType) ?>">
                                    <?= esc($pt['full_name']) ?> (<?= ucfirst($pType) ?>)
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
                                <th style="min-width: 200px;">Medication</th>
                                <th style="min-width: 120px;">Dosage</th>
                                <th style="min-width: 130px;">Frequency</th>
                                <th style="min-width: 140px;">Meal Instruction</th>
                                <th style="min-width: 100px;">Duration</th>
                                <th style="min-width: 100px;">Quantity</th>
                                <th style="min-width: 150px;">Notes</th>
                                <th style="min-width: 100px;">Follow-up</th>
                                <th class="buy-from-hospital-header" style="min-width: 120px; display: none;">Buy from Hospital</th>
                                <th style="width: 60px;">Actions</th>
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
        <div class="preview-actions">
            <button type="button" class="btn btn-primary btn-sm" onclick="printPrescription()" id="printBtn" style="display: none;">
                🖨️ Print
            </button>
            <button type="button" class="btn btn-success btn-sm" onclick="location.reload()" id="doneBtn" style="display: none;">
                ✅ Done
            </button>
            <button type="button" class="btn-close-preview" onclick="closePreview()">&times;</button>
        </div>
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
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptions as $rx): ?>
                            <?php 
                            $items = json_decode($rx['items_json'] ?? '[]', true) ?: [];
                            $firstItem = $items[0] ?? [];
                            $rxData = [
                                'id' => $rx['id'],
                                'patient_name' => $rx['patient_name'] ?? 'N/A',
                                'items' => $items,
                                'notes' => $rx['notes'] ?? '',
                                'created_at' => $rx['created_at'] ?? date('Y-m-d H:i:s')
                            ];
                            // Ensure status is set - if empty or null, default to 'completed' for outpatients
                            $status = $rx['status'] ?? '';
                            if (empty($status)) {
                                // Check if patient is outpatient - if so, set to completed
                                $patientModel = new \App\Models\PatientModel();
                                $patient = $patientModel->find($rx['patient_id'] ?? 0);
                                if ($patient && strtolower($patient['patient_type'] ?? '') === 'outpatient') {
                                    $status = 'completed';
                                } else {
                                    $status = 'pending';
                                }
                            }
                            ?>
                            <tr>
                                <td><strong>RX#<?= str_pad((string)$rx['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= esc($rx['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($firstItem['name'] ?? 'N/A') ?></td>
                                <td><?= esc($firstItem['frequency'] ?? '—') ?></td>
                                <td><?= esc($firstItem['meal_instruction'] ?? '—') ?></td>
                                <td><?= esc($firstItem['duration'] ?? '—') ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($status) ?>">
                                        <span class="status-dot"></span>
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($rx['created_at'])) ?></td>
                                <td>
                                    <button type="button" 
                                            class="btn-print-prescription" 
                                            onclick="printExistingPrescription(<?= htmlspecialchars(json_encode($rxData), ENT_QUOTES, 'UTF-8') ?>)"
                                            title="Print Prescription"
                                            style="background: #3B82F6; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; width: 100%; justify-content: center;">
                                        🖨️ Print
                                    </button>
                                </td>
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
        
        // Show/hide "Buy from Hospital" column based on patient type
        const patientType = selectedOption.dataset.type || 'outpatient';
        const isOutpatient = patientType === 'outpatient';
        
        // Show/hide header
        const headerCells = document.querySelectorAll('.buy-from-hospital-header');
        headerCells.forEach(cell => {
            cell.style.display = isOutpatient ? 'table-cell' : 'none';
        });
        
        // Show/hide cells in existing rows
        const rowCells = document.querySelectorAll('.buy-from-hospital-cell');
        rowCells.forEach(cell => {
            cell.style.display = isOutpatient ? 'table-cell' : 'none';
        });
    } else {
        document.getElementById('patient_age').value = '';
        document.getElementById('patient_gender').value = '';
        
        // Hide "Buy from Hospital" column when no patient selected
        const headerCells = document.querySelectorAll('.buy-from-hospital-header');
        headerCells.forEach(cell => {
            cell.style.display = 'none';
        });
        const rowCells = document.querySelectorAll('.buy-from-hospital-cell');
        rowCells.forEach(cell => {
            cell.style.display = 'none';
        });
    }
});

// Add medication row
function addRxItem() {
    const container = document.getElementById('rx_items_container');
    const row = document.createElement('tr');
    row.className = 'medication-row';
    
    // Check if current patient is outpatient to show/hide "Buy from Hospital" column
    const patientSelect = document.getElementById('rx_patient');
    const selectedOption = patientSelect ? patientSelect.options[patientSelect.selectedIndex] : null;
    const patientType = selectedOption ? selectedOption.dataset.type : 'outpatient';
    const isOutpatient = patientType === 'outpatient';
    const buyFromHospitalDisplay = isOutpatient ? 'table-cell' : 'none';
    
    row.innerHTML = `
        <td style="position: relative;">
            <select class="form-input form-input-sm" data-field="med_id" onchange="onMedChange(this)" required style="width: 100%;">
                <option value="">Select medication...</option>
                <?php foreach (($medications ?? []) as $m): ?>
                    <?php 
                    $stockQty = (int)($m['stock_quantity'] ?? 0);
                    $stockStatus = $m['stock_status'] ?? 'ok';
                    $stockLabel = '';
                    $stockClass = '';
                    
                    if ($stockStatus === 'out_of_stock') {
                        $stockLabel = ' (OUT OF STOCK)';
                        $stockClass = 'stock-out';
                    } elseif ($stockStatus === 'low_stock') {
                        $stockLabel = ' (Low Stock: ' . $stockQty . ')';
                        $stockClass = 'stock-low';
                    } else {
                        $stockLabel = ' (Stock: ' . $stockQty . ')';
                        $stockClass = 'stock-ok';
                    }
                    ?>
                    <option value="<?= (int) $m['id'] ?>" 
                            data-name="<?= esc(($m['name'] ?? '') . (!empty($m['strength']) ? ' ' . $m['strength'] : '')) ?>" 
                            data-dosage="<?= esc($m['default_dosage'] ?? '') ?>"
                            data-stock="<?= $stockQty ?>"
                            data-stock-status="<?= $stockStatus ?>"
                            class="<?= $stockClass ?>">
                        <?= esc($m['name'] ?? '') ?> <?= esc($m['strength'] ?? '') ?><?= !empty($m['form']) ? ' (' . esc($m['form']) . ')' : '' ?><?= $stockLabel ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" data-field="name">
            <div class="stock-indicator" style="display: none; margin-top: 6px;">
                <span class="stock-badge"></span>
            </div>
        </td>
        <td>
            <input type="text" class="form-input form-input-sm" placeholder="e.g., 1 capsule" data-field="dosage" required>
        </td>
        <td>
            <select class="form-input form-input-sm" data-field="frequency" required onchange="calculateQuantity(this)">
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
            <input type="text" class="form-input form-input-sm" placeholder="e.g., 7 days" data-field="duration" required oninput="calculateQuantity(this)">
        </td>
        <td>
            <div style="display: flex; align-items: center; gap: 4px;">
                <input type="number" class="form-input form-input-sm" placeholder="Qty" data-field="quantity" min="1" value="1" style="width: 80px; text-align: center;" required readonly title="Auto-calculated: Duration × Frequency">
                <span class="quantity-info" style="font-size: 10px; color: #666; white-space: nowrap;" title="Auto-calculated based on Duration and Frequency">🔢</span>
            </div>
        </td>
        <td>
            <input type="text" class="form-input form-input-sm" placeholder="Additional notes..." data-field="notes">
        </td>
        <td style="text-align: center;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <input type="checkbox" data-field="requires_followup" title="Check if patient needs follow-up appointment" onchange="toggleFollowupDateTime(this)">
                <div class="followup-datetime-container" style="display: none; width: 100%; margin-top: 8px;">
                    <input type="date" class="form-input form-input-sm" data-field="followup_date" style="width: 100%; margin-bottom: 4px;" placeholder="Follow-up Date" title="Follow-up Date">
                    <input type="time" class="form-input form-input-sm" data-field="followup_time" style="width: 100%;" placeholder="Follow-up Time" title="Follow-up Time">
                </div>
            </div>
        </td>
        <td class="buy-from-hospital-cell" style="text-align: center; display: ${buyFromHospitalDisplay};">
            <label style="display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; font-size: 12px;">
                <input type="checkbox" data-field="buy_from_hospital" checked style="cursor: pointer;" title="Check if patient will buy this medication from hospital pharmacy">
                <span>Yes</span>
            </label>
        </td>
        <td>
            <button type="button" class="btn-remove" onclick="this.closest('tr').remove()" title="Remove">
                🗑️
            </button>
        </td>
    `;
    container.appendChild(row);
}

// Toggle follow-up date and time inputs
function toggleFollowupDateTime(checkbox) {
    const row = checkbox.closest('tr');
    const container = row.querySelector('.followup-datetime-container');
    const dateInput = row.querySelector('[data-field="followup_date"]');
    const timeInput = row.querySelector('[data-field="followup_time"]');
    
    if (checkbox.checked) {
        container.style.display = 'block';
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        if (dateInput) {
            dateInput.setAttribute('min', today);
            dateInput.required = true;
        }
        if (timeInput) {
            timeInput.required = true;
        }
    } else {
        container.style.display = 'none';
        if (dateInput) {
            dateInput.value = '';
            dateInput.required = false;
        }
        if (timeInput) {
            timeInput.value = '';
            timeInput.required = false;
        }
    }
}

// Medication change handler
function onMedChange(sel) {
    const opt = sel.selectedOptions[0];
    if (!opt) {
        // Hide stock indicator if no medication selected
        const row = sel.closest('tr');
        if (row) {
            const stockIndicator = row.querySelector('.stock-indicator');
            if (stockIndicator) stockIndicator.style.display = 'none';
        }
        return;
    }
    
    const row = sel.closest('tr');
    const dosage = row.querySelector('[data-field="dosage"]');
    const nameHidden = row.querySelector('[data-field="name"]');
    const stockIndicator = row.querySelector('.stock-indicator');
    const stockBadge = row.querySelector('.stock-badge');
    
    if (dosage && opt.dataset.dosage) dosage.value = opt.dataset.dosage;
    if (nameHidden) nameHidden.value = opt.dataset.name || opt.textContent;
    
    // Show stock information
    if (stockIndicator && stockBadge && opt.dataset.stock !== undefined) {
        const stockQty = parseInt(opt.dataset.stock) || 0;
        const stockStatus = opt.dataset.stockStatus || 'ok';
        
        stockIndicator.style.display = 'block';
        stockBadge.textContent = '';
        stockBadge.style.backgroundColor = '';
        stockBadge.style.color = '';
        stockBadge.style.border = '';
        
        if (stockStatus === 'out_of_stock') {
            stockBadge.textContent = '⚠️ OUT OF STOCK';
            stockBadge.style.backgroundColor = '#dc3545';
            stockBadge.style.color = '#fff';
            stockBadge.style.border = '1px solid #c82333';
        } else if (stockStatus === 'low_stock') {
            stockBadge.textContent = `⚠️ Low Stock: ${stockQty} remaining`;
            stockBadge.style.backgroundColor = '#ffc107';
            stockBadge.style.color = '#856404';
            stockBadge.style.border = '1px solid #ffb300';
        } else {
            stockBadge.textContent = `✓ In Stock: ${stockQty} available`;
            stockBadge.style.backgroundColor = '#28a745';
            stockBadge.style.color = '#fff';
            stockBadge.style.border = '1px solid #218838';
        }
    } else if (stockIndicator) {
        stockIndicator.style.display = 'none';
    }
}

// Calculate quantity based on duration and frequency
function calculateQuantity(element) {
    const row = element.closest('tr');
    if (!row) return;
    
    const durationInput = row.querySelector('[data-field="duration"]');
    const frequencySelect = row.querySelector('[data-field="frequency"]');
    const quantityInput = row.querySelector('[data-field="quantity"]');
    const medicationSelect = row.querySelector('[data-field="med_id"]');
    const stockBadge = row.querySelector('.stock-badge');
    
    if (!durationInput || !frequencySelect || !quantityInput) return;
    
    // Get duration value and extract number of days
    const durationText = durationInput.value.trim();
    let durationDays = 0;
    
    if (durationText) {
        // Extract number from duration (e.g., "7 days" -> 7, "5" -> 5)
        const match = durationText.match(/(\d+)/);
        if (match) {
            durationDays = parseInt(match[1], 10);
        }
    }
    
    // Get frequency value and determine multiplier
    const frequencyValue = frequencySelect.value.trim();
    let frequencyMultiplier = 0;
    
    if (frequencyValue) {
        if (frequencyValue === 'Once a day' || frequencyValue.toLowerCase().includes('once')) {
            frequencyMultiplier = 1;
        } else if (frequencyValue === '2x/day' || frequencyValue.toLowerCase().includes('2x') || frequencyValue.toLowerCase().includes('twice')) {
            frequencyMultiplier = 2;
        } else if (frequencyValue === '3x/day' || frequencyValue.toLowerCase().includes('3x') || frequencyValue.toLowerCase().includes('thrice')) {
            frequencyMultiplier = 3;
        } else if (frequencyValue === 'Every 6 hours') {
            frequencyMultiplier = 4; // 24 hours / 6 hours = 4 times per day
        } else if (frequencyValue === 'Every 8 hours') {
            frequencyMultiplier = 3; // 24 hours / 8 hours = 3 times per day
        }
    }
    
    // Calculate quantity: duration (days) × frequency (times per day)
    let calculatedQuantity = 0;
    if (durationDays > 0 && frequencyMultiplier > 0) {
        calculatedQuantity = durationDays * frequencyMultiplier;
    }
    
    // Update quantity field (only if both duration and frequency are provided)
    if (calculatedQuantity > 0) {
        quantityInput.value = calculatedQuantity;
        
        // Check if quantity exceeds available stock
        if (medicationSelect && medicationSelect.selectedOptions.length > 0) {
            const selectedOption = medicationSelect.selectedOptions[0];
            const availableStock = parseInt(selectedOption.dataset.stock) || 0;
            
            if (availableStock > 0 && calculatedQuantity > availableStock) {
                // Show warning in stock badge
                if (stockBadge) {
                    stockBadge.textContent = `⚠️ Warning: Prescribing ${calculatedQuantity} but only ${availableStock} available!`;
                    stockBadge.style.backgroundColor = '#ff9800';
                    stockBadge.style.color = '#fff';
                    stockBadge.style.border = '1px solid #f57c00';
                }
                // Highlight quantity field
                quantityInput.style.borderColor = '#ff9800';
                quantityInput.style.borderWidth = '2px';
                quantityInput.style.backgroundColor = '#fff3cd';
            } else {
                // Reset quantity field styling
                quantityInput.style.borderColor = '';
                quantityInput.style.borderWidth = '';
                quantityInput.style.backgroundColor = '';
                // Restore stock badge to original status
                if (medicationSelect && stockBadge) {
                    onMedChange(medicationSelect);
                }
            }
        }
    } else {
        // If calculation can't be done, set to 1 as default
        quantityInput.value = 1;
        quantityInput.style.borderColor = '';
        quantityInput.style.backgroundColor = '';
    }
}

// Collect medication items
function collectItems() {
    const rows = document.querySelectorAll('#rx_items_container .medication-row');
    const items = [];
    rows.forEach(r => {
        const item = {};
        r.querySelectorAll('[data-field]').forEach(inp => {
            if (inp.type === 'checkbox') {
                item[inp.dataset.field] = inp.checked;
            } else {
                item[inp.dataset.field] = inp.value.trim();
            }
        });
        if ((!item.name || item.name.length === 0) && item.med_id) {
            const sel = r.querySelector('select[data-field="med_id"]');
            if (sel && sel.selectedOptions.length) {
                item.name = sel.selectedOptions[0].dataset.name || sel.options[sel.selectedIndex].text;
            }
        }
        // Default buy_from_hospital to true if not set (for inpatients or if checkbox doesn't exist)
        if (item.buy_from_hospital === undefined) {
            item.buy_from_hospital = true;
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

    // Validate follow-up date and time if follow-up is checked
    const rows = document.querySelectorAll('#rx_items_container .medication-row');
    for (let row of rows) {
        const followupCheckbox = row.querySelector('[data-field="requires_followup"]');
        if (followupCheckbox && followupCheckbox.checked) {
            const followupDate = row.querySelector('[data-field="followup_date"]');
            const followupTime = row.querySelector('[data-field="followup_time"]');
            if (!followupDate || !followupDate.value.trim()) {
                alert('Please select a follow-up date for the medication with follow-up checked.');
                followupDate?.focus();
                return;
            }
            if (!followupTime || !followupTime.value.trim()) {
                alert('Please select a follow-up time for the medication with follow-up checked.');
                followupTime?.focus();
                return;
            }
        }
    }

    const saveBtn = document.querySelector('button[onclick="savePrescription()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span>⏳</span> Saving...';

    // Get patient type
    const patientSelect = document.getElementById('rx_patient');
    const selectedOption = patientSelect.options[patientSelect.selectedIndex];
    const patientType = selectedOption ? selectedOption.dataset.type : 'outpatient';
    
    fetch('<?= site_url('doctor/prescriptions/create') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ patient_id: patientId, items: items, notes: notes })
    }).then(r => r.json()).then(res => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
        
        if (res.success) {
            showPrescriptionPreview(patientId, notes, items, res.is_outpatient, res.prescription_id);
            
            if (res.is_outpatient) {
                // For outpatients - show print dialog
                alert('✅ Prescription saved!\n\nThis is an OUTPATIENT prescription.\nClick the Print button to print it.');
                // Don't auto-reload for outpatients - let them print first
            } else {
                // For inpatients - notify about nurse and reload
                alert('✅ Prescription saved and sent to nurse station for administration.');
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
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
function showPrescriptionPreview(patientId, notes, items, isOutpatient = false, prescriptionId = null) {
    const patientSelect = document.getElementById('rx_patient');
    const selectedOption = patientSelect.options[patientSelect.selectedIndex];
    const patientName = selectedOption ? selectedOption.dataset.name || selectedOption.text : 'N/A';
    const patientAge = document.getElementById('patient_age').value || '—';
    const patientGender = document.getElementById('patient_gender').value || '—';
    const patientType = selectedOption ? selectedOption.dataset.type : 'outpatient';
    const doctorName = '<?= esc($user_name ?? session()->get('name') ?? 'Dr. ' . session()->get('name') ?? 'Doctor') ?>';
    const rxNumber = prescriptionId ? 'RX#' + String(prescriptionId).padStart(3, '0') : 'RX#' + String(Date.now()).slice(-3);
    const currentDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    
    // Store for printing
    window.currentPrescription = { patientName, patientAge, patientGender, patientType, doctorName, rxNumber, currentDate, notes, items, isOutpatient };

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
                    <div><span class="detail-label">Quantity:</span> ${item.quantity || '—'}</div>
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
    
    // Show print and done buttons for outpatients (always show for outpatients)
    const printBtn = document.getElementById('printBtn');
    const doneBtn = document.getElementById('doneBtn');
    if (printBtn) {
        printBtn.style.display = isOutpatient ? 'inline-block' : 'none';
        // Ensure print button is always visible for outpatients
        if (isOutpatient) {
            printBtn.style.display = 'inline-block';
        }
    }
    if (doneBtn) {
        doneBtn.style.display = isOutpatient ? 'inline-block' : 'none';
    }
}

// Print prescription for outpatients
function printPrescription() {
    const rx = window.currentPrescription;
    if (!rx) {
        alert('No prescription to print.');
        return;
    }
    
    let medicationsHtml = '';
    rx.items.forEach((item, index) => {
        medicationsHtml += `
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${index + 1}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>${item.name || 'Medication'}</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.dosage || '—'}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.frequency || '—'}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.meal_instruction || '—'}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.duration || '—'}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.quantity || '—'}</td>
            </tr>
        `;
    });
    
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Prescription - ${rx.rxNumber}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #1a365d; font-size: 24px; }
                .header p { margin: 5px 0; color: #666; }
                .rx-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
                .patient-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
                .patient-info h3 { margin: 0 0 10px 0; color: #333; }
                .diagnosis { background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th { background: #1a365d; color: white; padding: 10px; text-align: left; }
                .footer { margin-top: 40px; display: flex; justify-content: space-between; }
                .signature-box { width: 45%; }
                .signature-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; text-align: center; }
                .outpatient-notice { background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; color: #155724; font-weight: bold; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🏥 Hospital Management System</h1>
                <p>Medical Prescription</p>
            </div>
            
            <div class="outpatient-notice">
                📋 OUTPATIENT PRESCRIPTION - For External Pharmacy Use
            </div>
            
            <div class="rx-info">
                <div><strong>Prescription No:</strong> ${rx.rxNumber}</div>
                <div><strong>Date:</strong> ${rx.currentDate}</div>
            </div>
            
            <div class="patient-info">
                <h3>Patient Information</h3>
                <p><strong>Name:</strong> ${rx.patientName}</p>
                <p><strong>Age:</strong> ${rx.patientAge} | <strong>Sex:</strong> ${rx.patientGender}</p>
            </div>
            
            <div class="diagnosis">
                <strong>Diagnosis / Notes:</strong><br>
                ${rx.notes}
            </div>
            
            <h3>Prescribed Medications</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Medication</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Meal</th>
                        <th>Duration</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    ${medicationsHtml}
                </tbody>
            </table>
            
            <div class="footer">
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>${rx.doctorName}, M.D.</strong><br>
                        <small>Attending Physician</small>
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <small>PRC License No: ___________</small><br>
                        <small>PTR No: ___________</small>
                    </div>
                </div>
            </div>
        </body>
        </html>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
    }, 250);
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

// Print existing prescription from Recent Prescriptions table
function printExistingPrescription(rxData) {
    if (!rxData || !rxData.items || rxData.items.length === 0) {
        alert('No prescription data available to print.');
        return;
    }
    
    const rxNumber = 'RX#' + String(rxData.id).padStart(3, '0');
    const currentDate = new Date(rxData.created_at).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    // Get patient info - we'll need to fetch this or use what we have
    const patientName = rxData.patient_name || 'N/A';
    const doctorName = '<?= esc($user_name ?? session()->get('name') ?? 'Dr. ' . session()->get('name') ?? 'Doctor') ?>';
    
    let medicationsHtml = '';
    rxData.items.forEach((item, index) => {
        medicationsHtml += `
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${index + 1}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>${item.name || 'Medication'}</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.dosage || '—'}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.frequency || '—'}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.meal_instruction || '—'}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.duration || '—'}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.quantity || '—'}</td>
            </tr>
        `;
    });
    
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Prescription - ${rxNumber}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #1a365d; font-size: 24px; }
                .header p { margin: 5px 0; color: #666; }
                .rx-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
                .patient-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
                .patient-info h3 { margin: 0 0 10px 0; color: #333; }
                .diagnosis { background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th { background: #1a365d; color: white; padding: 10px; text-align: left; }
                .footer { margin-top: 40px; display: flex; justify-content: space-between; }
                .signature-box { width: 45%; }
                .signature-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; text-align: center; }
                .outpatient-notice { background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; color: #155724; font-weight: bold; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🏥 Hospital Management System</h1>
                <p>Medical Prescription</p>
            </div>
            
            <div class="outpatient-notice">
                📋 OUTPATIENT PRESCRIPTION - For External Pharmacy Use
            </div>
            
            <div class="rx-info">
                <div><strong>Prescription No:</strong> ${rxNumber}</div>
                <div><strong>Date:</strong> ${currentDate}</div>
            </div>
            
            <div class="patient-info">
                <h3>Patient Information</h3>
                <p><strong>Name:</strong> ${patientName}</p>
            </div>
            
            ${rxData.notes ? `
            <div class="diagnosis">
                <strong>Diagnosis / Notes:</strong><br>
                ${rxData.notes}
            </div>
            ` : ''}
            
            <h3>Prescribed Medications</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Medication</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Meal</th>
                        <th>Duration</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    ${medicationsHtml}
                </tbody>
            </table>
            
            <div class="footer">
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>${doctorName}, M.D.</strong><br>
                        <small>Attending Physician</small>
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <small>PRC License No: ___________</small><br>
                        <small>PTR No: ___________</small>
                    </div>
                </div>
            </div>
        </body>
        </html>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
    }, 250);
}

// Initialize with one row
addRxItem();
</script>

<style>
.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.preview-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
}
.btn-success {
    background: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.btn-success:hover {
    background: #059669;
}

/* Stock indicator styles */
.stock-indicator {
    margin-top: 6px;
    font-size: 11px;
    line-height: 1.4;
}

.stock-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 500;
    display: inline-block;
    font-size: 11px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

/* Medication dropdown stock status colors */
select option.stock-out {
    background-color: #ffe6e6;
    color: #dc3545;
    font-weight: bold;
}

select option.stock-low {
    background-color: #fff3cd;
    color: #856404;
}

select option.stock-ok {
    background-color: #d4edda;
    color: #155724;
}

/* Highlight selected option based on stock status */
select:focus option.stock-out:checked {
    background-color: #dc3545;
    color: white;
}

select:focus option.stock-low:checked {
    background-color: #ffc107;
    color: #000;
}

select:focus option.stock-ok:checked {
    background-color: #28a745;
    color: white;
}

/* Medication table improvements */
.medication-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.medication-table td {
    padding: 8px;
    vertical-align: top;
}

.medication-table th {
    padding: 10px 8px;
    text-align: left;
    font-weight: 600;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.medication-row {
    border-bottom: 1px solid #e9ecef;
}

.medication-row:hover {
    background-color: #f8f9fa;
}

.form-input-sm {
    width: 100%;
    padding: 6px 8px;
    font-size: 13px;
}

/* Quantity field styling */
input[data-field="quantity"] {
    font-weight: 600;
    background-color: #f8f9fa !important;
}

input[data-field="quantity"]:focus {
    outline: 2px solid #007bff;
    outline-offset: -2px;
}

.quantity-info {
    cursor: help;
}

/* Follow-up date and time styling */
.followup-datetime-container {
    display: flex;
    flex-direction: column;
    gap: 4px;
    width: 100%;
    padding: 4px;
    background-color: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.followup-datetime-container input[type="date"],
.followup-datetime-container input[type="time"] {
    font-size: 12px;
    padding: 4px 6px;
}

/* Adjust follow-up column width */
.medication-table th:nth-child(8),
.medication-table td:nth-child(8) {
    min-width: 150px;
    max-width: 180px;
}
</style>

<?= $this->endSection() ?>
