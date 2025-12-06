<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📦</span>
                    Medicine Inventory Management
                </h2>
                <p class="page-subtitle">
                    Manage medicine stock, expiration dates, and inventory levels
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
            <button class="btn-primary" onclick="openAddMedicineModal()" style="padding: 0.6rem 1.2rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">
                <span>➕</span> Add Medicine
            </button>
        </div>
    </header>
</section>

<!-- Statistics Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Medicines</div>
                <div class="kpi-value"><?= number_format(count($medications ?? [])) ?></div>
                <div class="kpi-change">In system</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Low Stock Items</div>
                <div class="kpi-value"><?= number_format(count(array_filter($inventory ?? [], fn($i) => ($i['stock_quantity'] ?? 0) <= ($i['reorder_level'] ?? 0) && ($i['stock_quantity'] ?? 0) > 0))) ?></div>
                <div class="kpi-change kpi-warning">Needs attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Out of Stock</div>
                <div class="kpi-value"><?= number_format(count(array_filter($inventory ?? [], fn($i) => ($i['stock_quantity'] ?? 0) <= 0))) ?></div>
                <div class="kpi-change kpi-negative">Critical</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Expiring Soon</div>
                <div class="kpi-value"><?= number_format(count(array_filter($inventory ?? [], fn($i) => !empty($i['expiration_date']) && strtotime($i['expiration_date']) <= strtotime('+30 days') && strtotime($i['expiration_date']) >= strtotime('today')))) ?></div>
                <div class="kpi-change kpi-warning">Within 30 days</div>
            </div>
        </div>
    </div>
</section>

<!-- Inventory Table -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Medicine Inventory</h2>
        <p>Complete list of medicines with stock levels and expiration dates</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Strength</th>
                        <th>Form</th>
                        <th>Price (₱)</th>
                        <th>Stock Quantity</th>
                        <th>Reorder Level</th>
                        <th>Category</th>
                        <th>Expiration Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($medications)): ?>
                        <?php foreach ($medications as $med): ?>
                            <?php
                            $invItem = $inventory[$med['id']] ?? null;
                            $stockQty = $invItem['stock_quantity'] ?? 0;
                            $reorderLevel = $invItem['reorder_level'] ?? 10;
                            $expirationDate = $invItem['expiration_date'] ?? null;
                            $category = $invItem['category'] ?? 'General';
                            
                            $status = 'ok';
                            $statusLabel = 'In Stock';
                            $statusClass = 'badge-success';
                            
                            if ($stockQty <= 0) {
                                $status = 'out_of_stock';
                                $statusLabel = 'Out of Stock';
                                $statusClass = 'badge-danger';
                            } elseif ($stockQty <= $reorderLevel) {
                                $status = 'low_stock';
                                $statusLabel = 'Low Stock';
                                $statusClass = 'badge-warning';
                            }
                            
                            if ($expirationDate && strtotime($expirationDate) <= strtotime('+30 days') && strtotime($expirationDate) >= strtotime('today')) {
                                if ($status === 'ok') {
                                    $statusClass = 'badge-warning';
                                    $statusLabel = 'Expiring Soon';
                                }
                            }
                            ?>
                            <tr>
                                <td><strong><?= esc($med['name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($med['strength'] ?? '—') ?></td>
                                <td><?= esc($med['form'] ?? '—') ?></td>
                                <td>
                                    <?php
                                    // Get base price from database or use default
                                    $basePrice = (float)($med['price'] ?? 0);
                                    if ($basePrice <= 0) {
                                        // Use default prices if not in database
                                        $defaultPrices = [
                                            'amoxicillin' => 8.00,
                                            'paracetamol' => 25.00,
                                            'ibuprofen' => 30.00,
                                            'aspirin' => 20.00,
                                            'metformin' => 40.00,
                                            'losartan' => 45.00,
                                            'atorvastatin' => 60.00,
                                            'omeprazole' => 35.00,
                                            'cefuroxime' => 80.00,
                                            'azithromycin' => 75.00,
                                        ];
                                        $medNameLower = strtolower($med['name'] ?? '');
                                        $baseName = preg_replace('/\s*\d+.*?(mg|ml|g|kg|mcg|iu|units?)\s*/i', '', $medNameLower);
                                        $baseName = trim($baseName);
                                        foreach ($defaultPrices as $key => $price) {
                                            if (strpos($medNameLower, $key) !== false || (!empty($baseName) && strpos($baseName, $key) !== false)) {
                                                $basePrice = $price;
                                                break;
                                            }
                                        }
                                    }
                                    // Patient price is doubled
                                    $patientPrice = $basePrice * 2;
                                    ?>
                                    <strong style="color: #059669; font-size: 1rem;">₱<?= number_format($patientPrice, 2) ?></strong>
                                    <br><small style="color: #64748b; font-size: 0.75rem;">Base: ₱<?= number_format($basePrice, 2) ?></small>
                                </td>
                                <td>
                                    <strong style="color: <?= $stockQty <= 0 ? '#ef4444' : ($stockQty <= $reorderLevel ? '#f59e0b' : '#10b981') ?>;">
                                        <?= number_format($stockQty) ?>
                                    </strong>
                                </td>
                                <td><?= number_format($reorderLevel) ?></td>
                                <td><?= esc($category) ?></td>
                                <td>
                                    <?php if ($expirationDate): ?>
                                        <?= date('M j, Y', strtotime($expirationDate)) ?>
                                        <?php if (strtotime($expirationDate) <= strtotime('+30 days') && strtotime($expirationDate) >= strtotime('today')): ?>
                                            <br><small style="color: #f59e0b;">⚠️ Expiring soon</small>
                                        <?php elseif (strtotime($expirationDate) < strtotime('today')): ?>
                                            <br><small style="color: #ef4444;">❌ Expired</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?>" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600;">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="editMedicine(<?= $med['id'] ?>)" style="padding: 0.35rem 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; margin-right: 0.5rem;">Edit</button>
                                    <button onclick="adjustStock(<?= $med['id'] ?>)" style="padding: 0.35rem 0.75rem; background: #10b981; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">Stock</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="padding: 2rem; text-align: center; color: #64748b;">
                                No medicines found. Click "Add Medicine" to get started.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Add/Edit Medicine Modal -->
<div id="medicineModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeMedicineModal()"></div>
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="modalTitle">Add Medicine</h3>
            <button class="modal-close" onclick="closeMedicineModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="medicineForm">
                <input type="hidden" id="medicine_id" name="medicine_id">
                <input type="hidden" id="medication_id" name="medication_id">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Medicine Name *</label>
                    <select id="medicine_name" name="name" required style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                        <option value="">Select medicine</option>
                        <?php foreach ($medications ?? [] as $med): ?>
                            <option value="<?= esc($med['name']) ?>" data-med-id="<?= $med['id'] ?>" data-strength="<?= esc($med['strength'] ?? '') ?>" data-form="<?= esc($med['form'] ?? '') ?>">
                                <?= esc($med['name']) ?><?= !empty($med['strength']) ? ' (' . esc($med['strength']) . ')' : '' ?><?= !empty($med['form']) ? ' - ' . esc($med['form']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Strength</label>
                        <input type="text" id="medicine_strength" name="strength" placeholder="e.g., 500mg" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: #f8fafc;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Form</label>
                        <input type="text" id="medicine_form" name="form" placeholder="Auto-filled" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: #f8fafc;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="0" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: #f8fafc; cursor: not-allowed;">
                        <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Use "Stock" button to adjust stock</small>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Reorder Level</label>
                        <input type="number" id="reorder_level" name="reorder_level" min="0" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Category</label>
                        <select id="category" name="category" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                            <option value="General">General</option>
                            <option value="Antibiotic">Antibiotic</option>
                            <option value="Pain Relief">Pain Relief</option>
                            <option value="Cardiovascular">Cardiovascular</option>
                            <option value="Respiratory">Respiratory</option>
                            <option value="Gastrointestinal">Gastrointestinal</option>
                            <option value="Vitamins">Vitamins</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Expiration Date</label>
                        <input type="date" id="expiration_date" name="expiration_date" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Notes</label>
                    <textarea id="medicine_notes" name="notes" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;"></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="button" onclick="closeMedicineModal()" style="padding: 0.5rem 1.5rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 0.5rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Save Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div id="stockModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeStockModal()"></div>
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Adjust Stock</h3>
            <button class="modal-close" onclick="closeStockModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="stockForm">
                <input type="hidden" id="stock_medication_id" name="medication_id">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Medicine</label>
                    <input type="text" id="stock_medicine_name" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: #f8fafc;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Current Stock</label>
                    <input type="text" id="stock_current" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; background: #f8fafc; font-weight: 600;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Adjustment Type</label>
                    <select id="adjustment_type" name="adjustment_type" onchange="onAdjustmentTypeChange()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                        <option value="add">Add Stock</option>
                        <option value="set">Set Stock</option>
                        <option value="remove_expired">Remove Expired</option>
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Quantity</label>
                    <input type="number" id="stock_quantity_input" name="quantity" min="1" required style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Notes (Optional)</label>
                    <textarea id="stock_notes" name="notes" rows="2" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;"></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="button" onclick="closeStockModal()" style="padding: 0.5rem 1.5rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 0.5rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddMedicineModal() {
    document.getElementById('modalTitle').textContent = 'Add Medicine';
    document.getElementById('medicineForm').reset();
    document.getElementById('medicine_id').value = '';
    document.getElementById('medication_id').value = '';
    document.getElementById('medicine_strength').value = '';
    document.getElementById('medicine_form').value = '';
    
    // Enable stock quantity for new medicine
    const stockQuantityInput = document.getElementById('stock_quantity');
    stockQuantityInput.readOnly = false;
    stockQuantityInput.style.background = '';
    stockQuantityInput.style.cursor = '';
    
    document.getElementById('medicineModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeMedicineModal() {
    document.getElementById('medicineModal').style.display = 'none';
    document.body.style.overflow = '';
}

function editMedicine(medicineId) {
    fetch('<?= base_url('pharmacy/inventory/get/') ?>' + medicineId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'Edit Medicine';
                document.getElementById('medicine_id').value = medicineId;
                document.getElementById('medication_id').value = medicineId;
                
                // Set medicine name dropdown
                const medicineSelect = document.getElementById('medicine_name');
                medicineSelect.value = data.medication.name;
                
                // Auto-fill strength and form
                const selectedOption = medicineSelect.options[medicineSelect.selectedIndex];
                if (selectedOption) {
                    document.getElementById('medicine_strength').value = selectedOption.getAttribute('data-strength') || data.medication.strength || '';
                    document.getElementById('medicine_form').value = selectedOption.getAttribute('data-form') || data.medication.form || '';
                } else {
                    document.getElementById('medicine_strength').value = data.medication.strength || '';
                    document.getElementById('medicine_form').value = data.medication.form || '';
                }
                
                // Set stock quantity as readonly (display only)
                const stockQuantityInput = document.getElementById('stock_quantity');
                stockQuantityInput.value = data.inventory.stock_quantity || 0;
                stockQuantityInput.readOnly = true;
                stockQuantityInput.style.background = '#f8fafc';
                stockQuantityInput.style.cursor = 'not-allowed';
                
                document.getElementById('reorder_level').value = data.inventory.reorder_level || 10;
                document.getElementById('category').value = data.inventory.category || 'General';
                document.getElementById('expiration_date').value = data.inventory.expiration_date || '';
                document.getElementById('medicine_notes').value = '';
                document.getElementById('medicineModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            } else {
                alert('Error loading medicine details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading medicine details');
        });
}

function adjustStock(medicineId) {
    fetch('<?= base_url('pharmacy/inventory/get/') ?>' + medicineId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stock_medication_id').value = medicineId;
                document.getElementById('stock_medicine_name').value = data.medication.name + (data.medication.strength ? ' (' + data.medication.strength + ')' : '');
                document.getElementById('stock_current').value = data.inventory.stock_quantity || 0;
                document.getElementById('stock_quantity_input').value = '';
                document.getElementById('adjustment_type').value = 'add';
                document.getElementById('stock_notes').value = '';
                
                // Store expiration date for expired check
                const expirationDate = data.inventory.expiration_date || null;
                document.getElementById('stock_quantity_input').setAttribute('data-expiration', expirationDate || '');
                
                onAdjustmentTypeChange();
                document.getElementById('stockModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            } else {
                alert('Error loading medicine details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading medicine details');
        });
}

function onAdjustmentTypeChange() {
    const adjustmentType = document.getElementById('adjustment_type').value;
    const quantityInput = document.getElementById('stock_quantity_input');
    const notesTextarea = document.getElementById('stock_notes');
    const expirationDate = quantityInput.getAttribute('data-expiration');
    
    if (adjustmentType === 'remove_expired') {
        // Auto-fill notes with expired removal note
        if (notesTextarea) {
            notesTextarea.value = 'Removed expired medicines from stock';
        }
        
        // Check if medicine is expired
        if (expirationDate && new Date(expirationDate) < new Date()) {
            // Medicine is expired - show warning
            const currentStock = parseInt(document.getElementById('stock_current').value) || 0;
            if (currentStock > 0) {
                // Suggest removing all expired stock
                quantityInput.placeholder = 'Enter quantity to remove (max: ' + currentStock + ')';
                quantityInput.max = currentStock;
            }
        }
    } else {
        quantityInput.placeholder = '';
        quantityInput.removeAttribute('max');
    }
}

function closeStockModal() {
    document.getElementById('stockModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Auto-fill strength and form when medicine is selected
document.getElementById('medicine_name').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        document.getElementById('medication_id').value = selectedOption.getAttribute('data-med-id') || '';
        document.getElementById('medicine_strength').value = selectedOption.getAttribute('data-strength') || '';
        document.getElementById('medicine_form').value = selectedOption.getAttribute('data-form') || '';
    }
});

// Save medicine inventory
document.getElementById('medicineForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('medication_id', document.getElementById('medication_id').value);
    
    fetch('<?= base_url('pharmacy/inventory/save') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Medicine inventory saved successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save inventory'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving inventory');
    });
});

// Adjust stock
document.getElementById('stockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= base_url('pharmacy/inventory/adjustStock') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Stock adjusted successfully! Previous: ' + data.previous_stock + ', New: ' + data.new_stock);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to adjust stock'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adjusting stock');
    });
});
</script>

<?= $this->endSection() ?>

