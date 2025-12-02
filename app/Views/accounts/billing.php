<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>💰</span>
                    Billing & Payments Management
                </h2>
                <p class="page-subtitle">
                    Welcome, <?= esc($user_name ?? 'Accountant') ?>
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
            <button class="btn-primary" onclick="openCreateBillModal()">
                <span>➕</span> Create New Bill
            </button>
        </div>
    </header>
</section>

<!-- KPI Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-value">₱<?= number_format($total_revenue ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">All time</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Amount</div>
                <div class="kpi-value">₱<?= number_format($pending_amount ?? 0, 2) ?></div>
                <div class="kpi-change kpi-warning">Awaiting payment</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Overdue Amount</div>
                <div class="kpi-value">₱<?= number_format($overdue_amount ?? 0, 2) ?></div>
                <div class="kpi-change kpi-negative">Requires attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Bills</div>
                <div class="kpi-value"><?= count($bills ?? []) ?></div>
                <div class="kpi-change">All bills</div>
            </div>
        </div>
    </div>
</section>

<!-- Bills Table -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>All Bills</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bill #</th>
                        <th>Patient</th>
                        <th>Type</th>
                        <th>Subtotal</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><strong><?= esc($bill['bill_number'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <strong><?= esc($bill['patient_name'] ?? 'N/A') ?></strong>
                                    <br><small class="text-muted"><?= esc($bill['patient_code'] ?? '') ?></small>
                                </td>
                                <td><?= ucfirst(esc($bill['bill_type'] ?? 'N/A')) ?></td>
                                <td>₱<?= number_format($bill['subtotal'] ?? 0, 2) ?></td>
                                <td><strong>₱<?= number_format($bill['total_amount'] ?? 0, 2) ?></strong></td>
                                <td>₱<?= number_format($bill['paid_amount'] ?? 0, 2) ?></td>
                                <td><strong>₱<?= number_format($bill['balance'] ?? 0, 2) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $bill['status'] === 'paid' ? 'success' : 
                                        ($bill['status'] === 'overdue' ? 'danger' : 
                                        ($bill['status'] === 'partial' ? 'warning' : 'warning'))
                                    ?>">
                                        <?= ucfirst(esc($bill['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= $bill['due_date'] ? date('M j, Y', strtotime($bill['due_date'])) : '—' ?></td>
                                <td>
                                    <button class="btn-xs btn-primary" onclick="viewBill(<?= $bill['id'] ?>)">View</button>
                                    <?php if ($bill['status'] !== 'paid'): ?>
                                        <button class="btn-xs btn-success" onclick="recordPayment(<?= $bill['id'] ?>)">Pay</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center-empty">No bills found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Create Bill Modal -->
<div id="createBillModal" class="modal" style="display: none;" onclick="if(event.target === this) closeCreateBillModal()">
    <div class="modal-dialog" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Create New Bill</h3>
            <button class="modal-close" onclick="closeCreateBillModal()" type="button">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createBillForm" novalidate>
                <div class="form-group">
                    <label>Patient *</label>
                    <select name="patient_id" id="bill_patient_id" class="form-input" required onchange="loadPatientBillableItems()">
                        <option value="">Select Patient</option>
                        <?php foreach ($patients ?? [] as $patient): ?>
                            <option value="<?= $patient['id'] ?>">
                                <?= esc($patient['full_name']) ?> (<?= esc($patient['patient_id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" name="due_date" id="bill_due_date" class="form-input" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                </div>
                <div class="form-group">
                    <label>Bill Items</label>
                    <div id="billItemsContainer">
                        <!-- Header row for bill items -->
                        <div class="bill-items-header" style="display: none; grid-template-columns: 120px 100px 150px 2fr 100px 80px 100px auto; gap: 0.5rem; padding: 0.75rem; background: #f8f9fa; border-radius: 6px 6px 0 0; font-weight: 600; font-size: 0.875rem; color: #495057; margin-bottom: 0;">
                            <div>Category</div>
                            <div>Code</div>
                            <div>Date & Time</div>
                            <div>Particulars</div>
                            <div>Rate (₱)</div>
                            <div>Units</div>
                            <div>Amount (₱)</div>
                            <div></div>
                        </div>
                        <!-- Items will be auto-populated when patient is selected -->
                        <div id="billItems">
                            <div class="text-center-empty" style="padding: 2rem; color: #666;">
                                <p>Please select a patient to automatically load billable items</p>
                            </div>
                        </div>
                        <button type="button" class="btn-secondary" id="addItemBtn" onclick="addBillItem()" style="display: none;">Add Item</button>
                    </div>
                    <div id="categoryTotals" style="margin-top: 1rem;">
                        <!-- Category subtotals will be displayed here -->
                    </div>
                </div>
                <div class="form-group">
                    <label>Subtotal</label>
                    <input type="number" id="bill_subtotal" class="form-input" value="0" step="0.01" readonly>
                </div>
                <div class="form-group">
                    <label>Discount (Insurance)</label>
                    <input type="number" name="discount" id="bill_discount" class="form-input" value="0" step="0.01" readonly style="background-color: #f5f5f5;" title="Auto-calculated based on patient's insurance provider">
                    <small id="discount_info" style="color: #666; font-size: 0.875rem; display: none;"></small>
                </div>
                <div class="form-group">
                    <label>Total Amount</label>
                    <input type="number" id="bill_total" class="form-input" value="0" step="0.01" readonly style="font-weight: bold; font-size: 1.1rem;">
                </div>
                <div class="form-group">
                    <label>Paid Amount in Words</label>
                    <input type="text" id="bill_amount_words" class="form-input" placeholder="Zero" readonly>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" id="bill_notes" class="form-textarea" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" id="createBillBtn" class="btn-primary" onclick="submitBillForm()">Create Bill</button>
                    <button type="button" class="btn-secondary" onclick="closeCreateBillModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal" style="display: none;" onclick="if(event.target === this) closePaymentModal()">
    <div class="modal-dialog" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Record Payment</h3>
            <button class="modal-close" onclick="closePaymentModal()" type="button">&times;</button>
        </div>
        <div class="modal-body">
            <form id="paymentForm">
                <input type="hidden" name="bill_id" id="payment_bill_id">
                <div class="form-group">
                    <label>Amount *</label>
                    <input type="number" name="amount" id="payment_amount" class="form-input" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Payment Method *</label>
                    <select name="payment_method" id="payment_method" class="form-input" required>
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="insurance">Insurance</option>
                        <option value="check">Check</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" class="form-input" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Transaction ID</label>
                    <input type="text" name="transaction_id" id="payment_transaction_id" class="form-input">
                </div>
                <div class="form-group">
                    <label>Reference Number</label>
                    <input type="text" name="reference_number" id="payment_reference" class="form-input">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" id="payment_notes" class="form-textarea" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Record Payment</button>
                    <button type="button" class="btn-secondary" onclick="closePaymentModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Bill Modal -->
<div id="viewBillModal" class="modal" style="display: none;" onclick="if(event.target === this) closeViewBillModal()">
    <div class="modal-dialog view-bill-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Bill Details</h3>
            <button class="modal-close" onclick="closeViewBillModal()" type="button">&times;</button>
        </div>
        <div class="modal-body">
            <div id="viewBillContent">
                <div class="text-center-empty" style="padding: 2rem;">
                    <p>Loading bill details...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeViewBillModal()">Close</button>
        </div>
    </div>
</div>

<script>
let billItemCount = 1;

// Make function globally accessible
window.openCreateBillModal = function() {
    try {
        console.log('Opening create bill modal...');
        const modal = document.getElementById('createBillModal');
        if (!modal) {
            console.error('Modal not found!');
            alert('Error: Modal not found. Please refresh the page.');
            return;
        }
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        console.log('Modal opened successfully');
    } catch (error) {
        console.error('Error opening modal:', error);
        alert('Error opening form: ' + error.message);
    }
};

// Also define as regular function for compatibility
function openCreateBillModal() {
    window.openCreateBillModal();
}

function closeCreateBillModal() {
    document.getElementById('createBillModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('createBillForm').reset();
    document.getElementById('prescription_select_group').style.display = 'none';
    document.getElementById('prescription_select').value = '';
    billItemCount = 1;
    document.getElementById('billItems').innerHTML = `
        <div class="bill-item-row" data-category="room">
            <select class="form-input bill-category" onchange="updateItemCategory(this)" required>
                <option value="room">Room/Bed Charges</option>
                <option value="nursing">Nursing Charges</option>
                <option value="ot">OT Charges</option>
                <option value="professional">Professional Fees</option>
                <option value="medication">Medication</option>
                <option value="lab">Lab Test</option>
                <option value="other">Other</option>
            </select>
            <input type="text" class="form-input bill-code" placeholder="Code" name="items[0][code]">
            <input type="datetime-local" class="form-input bill-datetime" placeholder="Date & Time" name="items[0][date_time]" value="<?= date('Y-m-d\TH:i') ?>">
            <input type="text" class="form-input bill-particulars" placeholder="Particulars *" name="items[0][item_name]" required>
            <input type="number" class="form-input bill-rate" placeholder="Rate *" name="items[0][unit_price]" step="0.01" required onchange="calculateItemAmount(this)" oninput="calculateItemAmount(this)">
            <input type="number" class="form-input bill-units" placeholder="Units *" name="items[0][quantity]" value="1" step="0.01" required onchange="calculateItemAmount(this)" oninput="calculateItemAmount(this)">
            <input type="number" class="form-input bill-amount" placeholder="Amount" name="items[0][amount]" readonly>
            <button type="button" class="btn-danger" onclick="removeBillItem(this)">Remove</button>
        </div>
    `;
    document.getElementById('bill_subtotal').value = '0.00';
    document.getElementById('bill_discount').value = '0.00';
    document.getElementById('bill_total').value = '0.00';
    document.getElementById('bill_amount_words').value = '';
    document.getElementById('categoryTotals').innerHTML = '';
    document.getElementById('billItems').innerHTML = '<div class="text-center-empty" style="padding: 2rem; color: #666;"><p>Please select a patient to automatically load billable items</p></div>';
    document.getElementById('addItemBtn').style.display = 'none';
}

async function loadPatientBillableItems() {
    const patientId = document.getElementById('bill_patient_id').value;
    
    if (!patientId) {
        // Clear items if no patient selected
        const itemsDiv = document.getElementById('billItems');
        itemsDiv.innerHTML = '<div class="text-center-empty" style="padding: 2rem; color: #666;"><p>Please select a patient to automatically load billable items</p></div>';
        const headerRow = document.querySelector('.bill-items-header');
        if (headerRow) {
            headerRow.style.display = 'none';
        }
        billItemCount = 0;
        document.getElementById('bill_subtotal').value = '0.00';
        document.getElementById('bill_discount').value = '0.00';
        document.getElementById('bill_total').value = '0.00';
        document.getElementById('bill_amount_words').value = '';
        document.getElementById('categoryTotals').innerHTML = '';
        document.getElementById('addItemBtn').style.display = 'none';
        const discountInfoEl = document.getElementById('discount_info');
        if (discountInfoEl) {
            discountInfoEl.style.display = 'none';
        }
        return;
    }
    
    // Show loading state
    const itemsDiv = document.getElementById('billItems');
    itemsDiv.innerHTML = '<div class="text-center-empty" style="padding: 2rem; color: #666;"><p>Loading billable items...</p></div>';
    
    try {
        const response = await fetch(`<?= base_url('accounts/getPatientBillableItems') ?>/${patientId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 200));
            throw new Error('Server returned non-JSON response.');
        }
        
        const result = await response.json();
        
        if (result.success && result.items) {
            itemsDiv.innerHTML = '';
            billItemCount = 0;
            
            // Add all billable items automatically
            if (result.items.length > 0) {
                // Show header row
                const headerRow = document.querySelector('.bill-items-header');
                if (headerRow) {
                    headerRow.style.display = 'grid';
                }
                
                result.items.forEach((item) => {
                    const itemRow = document.createElement('div');
                    itemRow.className = 'bill-item-row';
                    itemRow.setAttribute('data-category', item.category || 'other');
                    
                    const currentDateTime = item.date_time || new Date().toISOString().slice(0, 16);
                    const code = (item.code || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const particulars = (item.item_name || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const rate = parseFloat(item.unit_price) || 0;
                    const units = parseFloat(item.quantity) || 1;
                    const amount = (rate * units).toFixed(2);
                    
                    itemRow.innerHTML = `
                        <select class="form-input bill-category" onchange="updateItemCategory(this)" required style="font-size: 0.875rem;">
                            <option value="room" ${item.category === 'room' ? 'selected' : ''}>Room/Bed</option>
                            <option value="nursing" ${item.category === 'nursing' ? 'selected' : ''}>Nursing</option>
                            <option value="ot" ${item.category === 'ot' ? 'selected' : ''}>OT</option>
                            <option value="professional" ${item.category === 'professional' ? 'selected' : ''}>Professional</option>
                            <option value="medication" ${item.category === 'medication' ? 'selected' : ''}>Medication</option>
                            <option value="lab" ${item.category === 'lab' ? 'selected' : ''}>Lab Test</option>
                            <option value="other" ${item.category === 'other' ? 'selected' : ''}>Other</option>
                        </select>
                        <input type="text" class="form-input bill-code" placeholder="Code" name="items[${billItemCount}][code]" value="${code}" readonly style="font-size: 0.875rem;">
                        <input type="datetime-local" class="form-input bill-datetime" placeholder="Date & Time" name="items[${billItemCount}][date_time]" value="${currentDateTime}" readonly style="font-size: 0.875rem;">
                        <input type="text" class="form-input bill-particulars" placeholder="Particulars *" name="items[${billItemCount}][item_name]" value="${particulars}" required readonly style="font-size: 0.875rem; min-width: 200px;" title="${particulars}">
                        <input type="number" class="form-input bill-rate" placeholder="Rate" name="items[${billItemCount}][unit_price]" value="${rate}" step="0.01" required readonly style="font-size: 0.875rem; text-align: right;">
                        <input type="number" class="form-input bill-units" placeholder="Units" name="items[${billItemCount}][quantity]" value="${units}" step="0.01" required readonly style="font-size: 0.875rem; text-align: center;">
                        <input type="number" class="form-input bill-amount" placeholder="Amount" name="items[${billItemCount}][amount]" value="${amount}" readonly style="font-size: 0.875rem; text-align: right; font-weight: 600;">
                        <span class="text-muted" style="padding: 0.5rem; display: flex; align-items: center; font-size: 0.75rem; color: #28a745;">✓ Auto</span>
                    `;
                    // Store reference info in data attributes
                    if (item.reference_id) {
                        itemRow.setAttribute('data-reference-id', item.reference_id);
                    }
                    if (item.reference_type) {
                        itemRow.setAttribute('data-reference-type', item.reference_type);
                    }
                    itemsDiv.appendChild(itemRow);
                    attachItemListeners(itemRow);
                    billItemCount++;
                });
                
                // Hide "Add Item" button - everything is auto-loaded
                document.getElementById('addItemBtn').style.display = 'none';
            } else {
                // Show message if no billable items
                itemsDiv.innerHTML = '<div class="text-center-empty" style="padding: 2rem; color: #666;"><p>No billable items found for this patient.</p></div>';
                document.getElementById('addItemBtn').style.display = 'none';
            }
            
            // Auto-calculate totals
            calculateBillTotal();
        } else {
            // Error response
            const errorMsg = result.message || 'Failed to load billable items';
            itemsDiv.innerHTML = '<div class="text-center-empty" style="padding: 2rem; color: #d32f2f;"><p>Error: ' + errorMsg + '</p></div>';
            document.getElementById('addItemBtn').style.display = 'none';
            // Reset totals
            const subtotalEl = document.getElementById('bill_subtotal');
            const totalEl = document.getElementById('bill_total');
            const wordsEl = document.getElementById('bill_amount_words');
            if (subtotalEl) subtotalEl.value = '0.00';
            if (totalEl) totalEl.value = '0.00';
            if (wordsEl) wordsEl.value = '';
        }
    } catch (error) {
        console.error('Error loading billable items:', error);
        itemsDiv.innerHTML = '<div class="text-center-empty" style="padding: 2rem; color: #d32f2f;"><p>Error loading billable items: ' + error.message + '</p></div>';
        document.getElementById('addItemBtn').style.display = 'none';
    }
}

function handleBillTypeChange() {
    const billType = document.getElementById('bill_type').value;
    const prescriptionGroup = document.getElementById('prescription_select_group');
    const addItemBtn = document.getElementById('addItemBtn');
    
    if (billType === 'prescription') {
        prescriptionGroup.style.display = 'block';
        // Hide "Add Item" button for prescription bills
        addItemBtn.style.display = 'none';
    } else {
        prescriptionGroup.style.display = 'none';
        document.getElementById('prescription_select').value = '';
        // Show "Add Item" button for other bill types
        addItemBtn.style.display = 'inline-block';
        // Reset items to default
        billItemCount = 1;
        document.getElementById('billItems').innerHTML = `
            <div class="bill-item-row">
                <input type="text" name="items[0][item_name]" placeholder="Item Name" class="form-input" required>
                <input type="text" name="items[0][description]" placeholder="Description" class="form-input">
                <input type="number" name="items[0][quantity]" placeholder="Qty" class="form-input" value="1" step="0.01" required>
                <input type="number" name="items[0][unit_price]" placeholder="Unit Price" class="form-input" step="0.01" required>
                <button type="button" class="btn-danger" onclick="removeBillItem(this)">Remove</button>
            </div>
        `;
    }
}

async function loadPrescriptionItems() {
    const prescriptionSelect = document.getElementById('prescription_select');
    const prescriptionId = prescriptionSelect.value;
    
    if (!prescriptionId) {
        return;
    }
    
    try {
        const response = await fetch(`<?= base_url('accounts/getPrescriptionDetails') ?>/${prescriptionId}`);
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 200));
            throw new Error('Server returned non-JSON response. Please check the server logs.');
        }
        
        const result = await response.json();
        
        if (result.success && result.prescription) {
            const prescription = result.prescription;
            
            // Set patient
            document.getElementById('bill_patient_id').value = prescription.patient_id;
            
            // Clear existing items
            const itemsDiv = document.getElementById('billItems');
            itemsDiv.innerHTML = '';
            billItemCount = 0;
            
            // Add prescription items
            if (prescription.items && prescription.items.length > 0) {
                prescription.items.forEach((item, index) => {
                    const itemRow = document.createElement('div');
                    itemRow.className = 'bill-item-row';
                    itemRow.setAttribute('data-category', 'medication');
                    // Escape HTML to prevent XSS
                    const itemName = (item.item_name || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const description = (item.description || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const quantity = item.quantity || 1;
                    const unitPrice = item.unit_price || 0;
                    const amount = (quantity * unitPrice).toFixed(2);
                    const currentDateTime = new Date().toISOString().slice(0, 16);
                    
                    itemRow.innerHTML = `
                        <select class="form-input bill-category" onchange="updateItemCategory(this)" required>
                            <option value="medication" selected>Medication</option>
                            <option value="room">Room/Bed Charges</option>
                            <option value="nursing">Nursing Charges</option>
                            <option value="ot">OT Charges</option>
                            <option value="professional">Professional Fees</option>
                            <option value="lab">Lab Test</option>
                            <option value="other">Other</option>
                        </select>
                        <input type="text" class="form-input bill-code" placeholder="Code" name="items[${billItemCount}][code]" value="MED${billItemCount.toString().padStart(4, '0')}">
                        <input type="datetime-local" class="form-input bill-datetime" placeholder="Date & Time" name="items[${billItemCount}][date_time]" value="${currentDateTime}">
                        <input type="text" class="form-input bill-particulars" placeholder="Particulars *" name="items[${billItemCount}][item_name]" value="${itemName}" required>
                        <input type="number" class="form-input bill-rate" placeholder="Rate *" name="items[${billItemCount}][unit_price]" value="${unitPrice}" step="0.01" required onchange="calculateItemAmount(this)" oninput="calculateItemAmount(this)">
                        <input type="number" class="form-input bill-units" placeholder="Units *" name="items[${billItemCount}][quantity]" value="${quantity}" step="0.01" required onchange="calculateItemAmount(this)" oninput="calculateItemAmount(this)">
                        <input type="number" class="form-input bill-amount" placeholder="Amount" name="items[${billItemCount}][amount]" value="${amount}" readonly>
                        <span class="text-muted" style="padding: 0.5rem; display: flex; align-items: center; font-size: 0.85rem;">From Prescription</span>
                    `;
                    itemsDiv.appendChild(itemRow);
                    attachItemListeners(itemRow);
                    billItemCount++;
                });
                // Hide "Add Item" button since items come from prescription
                document.getElementById('addItemBtn').style.display = 'none';
                calculateBillTotal();
            } else {
                // If no items, add one empty row
                const itemRow = document.createElement('div');
                itemRow.className = 'bill-item-row';
                itemRow.setAttribute('data-category', 'other');
                itemRow.innerHTML = `
                    <select class="form-input bill-category" onchange="updateItemCategory(this)" required>
                        <option value="room">Room/Bed Charges</option>
                        <option value="nursing">Nursing Charges</option>
                        <option value="ot">OT Charges</option>
                        <option value="professional">Professional Fees</option>
                        <option value="medication">Medication</option>
                        <option value="lab">Lab Test</option>
                        <option value="other" selected>Other</option>
                    </select>
                    <input type="text" class="form-input bill-code" placeholder="Code" name="items[0][code]">
                    <input type="datetime-local" class="form-input bill-datetime" placeholder="Date & Time" name="items[0][date_time]" value="<?= date('Y-m-d\TH:i') ?>">
                    <input type="text" class="form-input bill-particulars" placeholder="Particulars *" name="items[0][item_name]" required>
                    <input type="number" class="form-input bill-rate" placeholder="Rate *" name="items[0][unit_price]" step="0.01" required onchange="calculateItemAmount(this)" oninput="calculateItemAmount(this)">
                    <input type="number" class="form-input bill-units" placeholder="Units *" name="items[0][quantity]" value="1" step="0.01" required onchange="calculateItemAmount(this)" oninput="calculateItemAmount(this)">
                    <input type="number" class="form-input bill-amount" placeholder="Amount" name="items[0][amount]" readonly>
                    <button type="button" class="btn-danger" onclick="removeBillItem(this)">Remove</button>
                `;
                itemsDiv.appendChild(itemRow);
                attachItemListeners(itemRow);
                billItemCount = 1;
            }
        } else {
            alert('Failed to load prescription details: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error loading prescription:', error);
        // Check if response is HTML (error page)
        if (error.message && error.message.includes('JSON')) {
            alert('Server error: Please check if the prescription exists and try again.');
        } else {
            alert('Error loading prescription: ' + error.message);
        }
    }
}

function addBillItem() {
    const itemsDiv = document.getElementById('billItems');
    const newItem = document.createElement('div');
    newItem.className = 'bill-item-row';
    newItem.setAttribute('data-category', 'other');
    const currentDateTime = new Date().toISOString().slice(0, 16);
    newItem.innerHTML = `
        <select class="form-input bill-category" onchange="updateItemCategory(this)" required>
            <option value="room">Room/Bed Charges</option>
            <option value="nursing">Nursing Charges</option>
            <option value="ot">OT Charges</option>
            <option value="professional">Professional Fees</option>
            <option value="medication">Medication</option>
            <option value="lab">Lab Test</option>
            <option value="other" selected>Other</option>
        </select>
        <input type="text" class="form-input bill-code" placeholder="Code" name="items[${billItemCount}][code]">
        <input type="datetime-local" class="form-input bill-datetime" placeholder="Date & Time" name="items[${billItemCount}][date_time]" value="${currentDateTime}">
        <input type="text" class="form-input bill-particulars" placeholder="Particulars *" name="items[${billItemCount}][item_name]" required>
        <input type="number" class="form-input bill-rate" placeholder="Rate *" name="items[${billItemCount}][unit_price]" step="0.01" required onchange="calculateItemAmount(this)" oninput="calculateItemAmount(this)">
        <input type="number" class="form-input bill-units" placeholder="Units *" name="items[${billItemCount}][quantity]" value="1" step="0.01" required onchange="calculateItemAmount(this)" oninput="calculateItemAmount(this)">
        <input type="number" class="form-input bill-amount" placeholder="Amount" name="items[${billItemCount}][amount]" readonly>
        <button type="button" class="btn-danger" onclick="removeBillItem(this)">Remove</button>
    `;
    itemsDiv.appendChild(newItem);
    attachItemListeners(newItem);
    billItemCount++;
}

function removeBillItem(btn) {
    btn.parentElement.remove();
    calculateBillTotal();
}

function updateItemCategory(select) {
    const row = select.closest('.bill-item-row');
    if (row) {
        row.setAttribute('data-category', select.value);
        calculateCategoryTotals();
    }
}

function calculateItemAmount(input) {
    const row = input.closest('.bill-item-row');
    const rateInput = row.querySelector('.bill-rate');
    const unitsInput = row.querySelector('.bill-units');
    const amountInput = row.querySelector('.bill-amount');
    
    const rate = parseFloat(rateInput.value) || 0;
    const units = parseFloat(unitsInput.value) || 0;
    const amount = rate * units;
    
    amountInput.value = amount.toFixed(2);
    calculateCategoryTotals();
    calculateBillTotal();
}

function calculateCategoryTotals() {
    const categories = ['room', 'nursing', 'ot', 'professional', 'medication', 'lab', 'other'];
    const categoryNames = {
        'room': 'Room/Bed Charges',
        'nursing': 'Nursing Charges',
        'ot': 'OT Charges',
        'professional': 'Professional Fees',
        'medication': 'Medication',
        'lab': 'Lab Test',
        'other': 'Other'
    };
    
    const totalsDiv = document.getElementById('categoryTotals');
    totalsDiv.innerHTML = '';
    
    categories.forEach(category => {
        const items = document.querySelectorAll(`.bill-item-row[data-category="${category}"]`);
        let subtotal = 0;
        
        items.forEach(item => {
            const amountInput = item.querySelector('.bill-amount');
            if (amountInput && amountInput.value) {
                subtotal += parseFloat(amountInput.value) || 0;
            }
        });
        
        if (subtotal > 0) {
            const categoryDiv = document.createElement('div');
            categoryDiv.style.cssText = 'display: flex; justify-content: space-between; padding: 0.5rem; background: #f8f9fa; border-radius: 4px; margin-bottom: 0.25rem;';
            categoryDiv.innerHTML = `
                <span><strong>${categoryNames[category]}:</strong></span>
                <span><strong>₱${subtotal.toFixed(2)}</strong></span>
            `;
            totalsDiv.appendChild(categoryDiv);
        }
    });
}

async function calculateBillTotal() {
    let subtotal = 0;
    document.querySelectorAll('.bill-amount').forEach(input => {
        if (input.value) {
            subtotal += parseFloat(input.value) || 0;
        }
    });
    
    // Check for insurance discount
    const patientId = document.getElementById('bill_patient_id').value;
    let discount = 0;
    let discountInfo = '';
    
    if (patientId && subtotal > 0) {
        try {
            const response = await fetch(`<?= base_url('accounts/getPatientInsuranceDiscount') ?>/${patientId}`);
            const result = await response.json();
            
            console.log('Insurance discount response:', result);
            
            if (result.success && result.has_insurance) {
                if (result.coverage) {
                    // Category-based discount calculation
                    let categoryDiscount = 0;
                    let coverageDetails = [];
                    
                    document.querySelectorAll('.bill-item-row').forEach(row => {
                        const category = row.querySelector('[name="item_category[]"]')?.value || 'other';
                        const amount = parseFloat(row.querySelector('.bill-amount')?.value || 0);
                        
                        if (amount > 0) {
                            // Map category to coverage key
                            let coverageKey = 'professional';
                            if (category === 'room' || category === 'room/bed') {
                                coverageKey = 'room';
                            } else if (category === 'lab' || category === 'laboratory') {
                                coverageKey = 'laboratory';
                            } else if (category === 'medication' || category === 'meds') {
                                coverageKey = 'medication';
                            } else if (category === 'professional' || category === 'pf') {
                                coverageKey = 'professional';
                            } else if (category === 'procedure' || category === 'ot') {
                                coverageKey = 'procedure';
                            }
                            
                            const coveragePercent = result.coverage[coverageKey] || 0;
                            const itemDiscount = amount * (coveragePercent / 100);
                            categoryDiscount += itemDiscount;
                            
                            if (coveragePercent > 0) {
                                coverageDetails.push(`${getCategoryLabel(category)}: ${coveragePercent}%`);
                            }
                        }
                    });
                    
                    discount = categoryDiscount;
                    
                    if (coverageDetails.length > 0) {
                        discountInfo = `${result.provider} Coverage:\n${coverageDetails.join('\n')}`;
                    } else {
                        discountInfo = `${result.provider} - No coverage available`;
                    }
                } else if (result.discount_percentage > 0) {
                    // Fallback to percentage-based discount
                    discount = subtotal * (result.discount_percentage / 100);
                    discountInfo = `${result.provider} discount (${result.discount_percentage}%): Patient pays ${result.copay_percentage}%`;
                } else {
                    discount = 0;
                    discountInfo = `${result.provider} - No discount available`;
                }
                console.log(`Discount calculated: ${discount.toFixed(2)} for provider ${result.provider}`);
            } else {
                discount = 0;
                discountInfo = '';
                console.log('No discount - has_insurance:', result.has_insurance, 'discount_percentage:', result.discount_percentage);
            }
            
            function getCategoryLabel(category) {
                const labels = {
                    'room': 'Room',
                    'room/bed': 'Room',
                    'lab': 'Lab Tests',
                    'laboratory': 'Lab Tests',
                    'medication': 'Medicines',
                    'meds': 'Medicines',
                    'professional': 'Doctor PF',
                    'pf': 'Doctor PF',
                    'procedure': 'Procedures',
                    'ot': 'Procedures',
                    'other': 'Other'
                };
                return labels[category] || 'Other';
            }
        } catch (error) {
            console.error('Error checking insurance:', error);
            discount = 0;
        }
    }
    
    document.getElementById('bill_discount').value = discount.toFixed(2);
    const discountInfoEl = document.getElementById('discount_info');
    if (discountInfoEl) {
        if (discountInfo) {
            // Format discount info with line breaks
            discountInfoEl.innerHTML = discountInfo.replace(/\n/g, '<br>');
            discountInfoEl.style.display = 'block';
        } else {
            discountInfoEl.style.display = 'none';
        }
    }
    
    // No tax - total is subtotal minus discount
    const total = subtotal - discount;
    
    document.getElementById('bill_subtotal').value = subtotal.toFixed(2);
    document.getElementById('bill_total').value = total.toFixed(2);
    
    // Convert to words
    document.getElementById('bill_amount_words').value = numberToWords(total);
    calculateCategoryTotals();
}

function numberToWords(num) {
    if (num === 0) return 'Zero';
    
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    
    function convertHundreds(n) {
        let result = '';
        if (n >= 100) {
            result += ones[Math.floor(n / 100)] + ' Hundred ';
            n %= 100;
        }
        if (n >= 20) {
            result += tens[Math.floor(n / 10)] + ' ';
            n %= 10;
        }
        if (n > 0) {
            result += ones[n] + ' ';
        }
        return result.trim();
    }
    
    if (num === 0) return 'Zero';
    
    let words = '';
    let wholePart = Math.floor(num);
    const decimalPart = Math.round((num - wholePart) * 100);
    
    if (wholePart >= 1000000) {
        words += convertHundreds(Math.floor(wholePart / 1000000)) + ' Million ';
        wholePart %= 1000000;
    }
    if (wholePart >= 1000) {
        words += convertHundreds(Math.floor(wholePart / 1000)) + ' Thousand ';
        wholePart %= 1000;
    }
    if (wholePart > 0) {
        words += convertHundreds(wholePart);
    }
    
    if (decimalPart > 0) {
        words += ' and ' + decimalPart + '/100';
    }
    
    return words.trim() + ' Pesos Only';
}

function attachItemListeners(row) {
    const rateInput = row.querySelector('.bill-rate');
    const unitsInput = row.querySelector('.bill-units');
    
    if (rateInput) {
        rateInput.addEventListener('input', function() { calculateItemAmount(this); });
        rateInput.addEventListener('change', function() { calculateItemAmount(this); });
    }
    if (unitsInput) {
        unitsInput.addEventListener('input', function() { calculateItemAmount(this); });
        unitsInput.addEventListener('change', function() { calculateItemAmount(this); });
    }
}

function recordPayment(billId) {
    document.getElementById('payment_bill_id').value = billId;
    document.getElementById('paymentModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('paymentForm').reset();
}

async function viewBill(billId) {
    try {
        const modal = document.getElementById('viewBillModal');
        const content = document.getElementById('viewBillContent');
        
        // Show modal with loading state
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        content.innerHTML = '<div class="text-center-empty" style="padding: 2rem;"><p>Loading bill details...</p></div>';
        
        // Fetch bill details
        const response = await fetch(`<?= base_url('accounts/getBillDetails') ?>/${billId}`);
        const result = await response.json();
        
        if (result.success && result.bill) {
            const bill = result.bill;
            const patient = bill.patient || {};
            const items = bill.items || [];
            const payments = bill.payments || [];
            
            // Fetch insurance information first
            let insuranceHtml = '';
            if (bill.patient_id) {
                try {
                    const insuranceResponse = await fetch(`<?= base_url('accounts/getPatientInsuranceDiscount') ?>/${bill.patient_id}`);
                    const insuranceResult = await insuranceResponse.json();
                    
                    if (insuranceResult.success && insuranceResult.has_insurance) {
                        insuranceHtml = `
                            <div class="view-bill-info">
                                <span class="view-bill-label">Provider:</span>
                                <span class="view-bill-value"><strong>${insuranceResult.provider || 'N/A'}</strong></span>
                            </div>
                        `;
                        
                        if (insuranceResult.coverage) {
                            const coverage = insuranceResult.coverage;
                            
                            // Calculate discount per category from bill items
                            const categoryTotals = {};
                            const categoryDiscounts = {};
                            let totalItemDiscounts = 0; // Track total discount from items
                            
                            items.forEach(item => {
                                // Use saved category from database, fallback to item_type
                                const category = (item.category || item.item_type || '').toLowerCase();
                                const amount = parseFloat(item.total_price || item.unit_price * item.quantity || 0);
                                
                                // Use saved insurance discount if available, otherwise calculate
                                const savedDiscount = parseFloat(item.insurance_discount_amount || 0);
                                const savedCoveragePercent = parseFloat(item.insurance_coverage_percent || 0);
                                
                                // Map category to coverage key
                                let coverageKey = 'professional';
                                if (category === 'room' || category === 'room/bed') {
                                    coverageKey = 'room';
                                } else if (category === 'lab' || category === 'laboratory') {
                                    coverageKey = 'laboratory';
                                } else if (category === 'medication' || category === 'meds') {
                                    coverageKey = 'medication';
                                } else if (category === 'professional' || category === 'pf' || category === 'nursing') {
                                    // Nursing fees are covered under professional fees
                                    coverageKey = 'professional';
                                } else if (category === 'procedure' || category === 'ot') {
                                    coverageKey = 'procedure';
                                }
                                
                                if (!categoryTotals[coverageKey]) {
                                    categoryTotals[coverageKey] = 0;
                                    categoryDiscounts[coverageKey] = 0;
                                }
                                
                                categoryTotals[coverageKey] += amount;
                                
                                // Use saved discount if available, otherwise calculate from coverage
                                let itemDiscount = 0;
                                if (savedDiscount > 0) {
                                    itemDiscount = savedDiscount;
                                } else {
                                    const coveragePercent = coverage[coverageKey] || 0;
                                    itemDiscount = amount * (coveragePercent / 100);
                                }
                                
                                categoryDiscounts[coverageKey] += itemDiscount;
                                totalItemDiscounts += itemDiscount;
                            });
                            
                            // Update bill discount to match sum of item discounts if different
                            const savedBillDiscount = parseFloat(bill.discount || 0);
                            if (Math.abs(totalItemDiscounts - savedBillDiscount) > 0.01) {
                                console.warn(`Discount mismatch: Bill discount (${savedBillDiscount}) vs Item discounts (${totalItemDiscounts.toFixed(2)})`);
                                // Use the sum of item discounts for consistency
                                bill.discount = totalItemDiscounts;
                                bill.total_amount = parseFloat(bill.subtotal || 0) - totalItemDiscounts;
                            }
                            
                            // Build coverage display with discount amounts - only show categories that have items
                            const coverageLabels = {
                                'room': 'Room Coverage',
                                'laboratory': 'Lab Tests Coverage',
                                'medication': 'Medicines Coverage',
                                'professional': 'Doctor PF & Nursing Coverage',
                                'procedure': 'Procedures Coverage'
                            };
                            
                            // Only show coverage for categories that have items in the bill
                            Object.keys(coverageLabels).forEach(key => {
                                const categoryTotal = categoryTotals[key] || 0;
                                
                                // Only display if there are items in this category
                                if (categoryTotal > 0) {
                                    const coveragePercent = coverage[key] || 0;
                                    const categoryDiscount = categoryDiscounts[key] || 0;
                                    const patientPays = categoryTotal - categoryDiscount;
                                    
                                    insuranceHtml += `
                                        <div class="view-bill-info">
                                            <span class="view-bill-label">${coverageLabels[key]}:</span>
                                            <span class="view-bill-value">
                                                ${coveragePercent}% 
                                                <small style="color: #666; margin-left: 8px;">
                                                    (Discount: ₱${categoryDiscount.toFixed(2)}, Patient pays: ₱${patientPays.toFixed(2)})
                                                </small>
                                            </span>
                                        </div>
                                    `;
                                }
                            });
                        } else if (insuranceResult.discount_percentage > 0) {
                            insuranceHtml += `
                                <div class="view-bill-info">
                                    <span class="view-bill-label">Discount:</span>
                                    <span class="view-bill-value">${insuranceResult.discount_percentage}%</span>
                                </div>
                            `;
                        }
                        
                        if (insuranceResult.policy_number) {
                            insuranceHtml += `
                                <div class="view-bill-info">
                                    <span class="view-bill-label">Policy Number:</span>
                                    <span class="view-bill-value">${insuranceResult.policy_number}</span>
                                </div>
                            `;
                        }
                        
                        if (insuranceResult.member_id) {
                            insuranceHtml += `
                                <div class="view-bill-info">
                                    <span class="view-bill-label">Member ID:</span>
                                    <span class="view-bill-value">${insuranceResult.member_id}</span>
                                </div>
                            `;
                        }
                    }
                } catch (error) {
                    console.error('Error loading insurance info:', error);
                }
            }
            
            // Format bill details HTML
            let html = `
                <div class="view-bill-section">
                    <h4>Bill Information</h4>
                    <div class="view-bill-grid">
                        <div class="view-bill-info">
                            <span class="view-bill-label">Bill Number:</span>
                            <span class="view-bill-value"><strong>${bill.bill_number || 'N/A'}</strong></span>
                        </div>
                        <div class="view-bill-info">
                            <span class="view-bill-label">Status:</span>
                            <span class="view-bill-value">
                                <span class="badge badge-${bill.status === 'paid' ? 'success' : (bill.status === 'overdue' ? 'danger' : 'warning')}">
                                    ${(bill.status || 'pending').toUpperCase()}
                                </span>
                            </span>
                        </div>
                        <div class="view-bill-info">
                            <span class="view-bill-label">Bill Date:</span>
                            <span class="view-bill-value">${bill.created_at ? new Date(bill.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'}</span>
                        </div>
                        <div class="view-bill-info">
                            <span class="view-bill-label">Due Date:</span>
                            <span class="view-bill-value">${bill.due_date ? new Date(bill.due_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'}</span>
                        </div>
                    </div>
                </div>
                
                <div class="view-bill-section">
                    <h4>Patient Information</h4>
                    <div class="view-bill-grid">
                        <div class="view-bill-info">
                            <span class="view-bill-label">Patient Name:</span>
                            <span class="view-bill-value"><strong>${patient.full_name || patient.name || 'N/A'}</strong></span>
                        </div>
                        <div class="view-bill-info">
                            <span class="view-bill-label">Patient ID:</span>
                            <span class="view-bill-value">${patient.patient_id || 'N/A'}</span>
                        </div>
                        ${patient.phone ? `
                        <div class="view-bill-info">
                            <span class="view-bill-label">Contact:</span>
                            <span class="view-bill-value">${patient.phone}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="view-bill-section">
                    <h4>Bill Items</h4>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Code</th>
                                    <th>Particulars</th>
                                    <th>Rate (₱)</th>
                                    <th>Units</th>
                                    <th>Amount (₱)</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (items.length > 0) {
                items.forEach(item => {
                    // Extract code from description if available, or generate based on item type
                    let itemCode = 'N/A';
                    if (item.description && item.description.includes('Code:')) {
                        itemCode = item.description.split('Code:')[1]?.trim() || 'N/A';
                    } else if (item.reference_id) {
                        // Generate code based on item type
                        const typePrefix = item.item_type === 'professional' ? 'PROF' : 
                                         item.item_type === 'medication' ? 'MED' : 
                                         item.item_type === 'laboratory' ? 'LAB' : 'SRV';
                        itemCode = `${typePrefix}-${String(item.reference_id).padStart(6, '0')}`;
                    }
                    
                    // Use item_type as category, capitalize it
                    const category = item.item_type ? item.item_type.charAt(0).toUpperCase() + item.item_type.slice(1) : 'N/A';
                    
                    html += `
                        <tr>
                            <td>${category}</td>
                            <td>${itemCode}</td>
                            <td>${item.item_name || 'N/A'}</td>
                            <td>₱${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                            <td>${item.quantity || 0}</td>
                            <td><strong>₱${parseFloat(item.total_price || 0).toFixed(2)}</strong></td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="6" class="text-center-empty">No items found</td></tr>';
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
                ${insuranceHtml ? `
                <div class="view-bill-section">
                    <h4>Insurance Information</h4>
                    <div class="view-bill-grid">
                        ${insuranceHtml}
                    </div>
                </div>
                ` : ''}
                <div class="view-bill-section">
                    <h4>Summary</h4>
                    <div class="view-bill-summary">
                        <div class="view-bill-summary-row">
                            <span class="view-bill-summary-label">Subtotal:</span>
                            <span class="view-bill-summary-value">₱${parseFloat(bill.subtotal || 0).toFixed(2)}</span>
                        </div>
                        <div class="view-bill-summary-row">
                            <span class="view-bill-summary-label">Discount:</span>
                            <span class="view-bill-summary-value">₱${parseFloat(bill.discount || 0).toFixed(2)}</span>
                        </div>
                        <div class="view-bill-summary-row view-bill-total">
                            <span class="view-bill-summary-label"><strong>Total Amount:</strong></span>
                            <span class="view-bill-summary-value"><strong>₱${parseFloat(bill.total_amount || 0).toFixed(2)}</strong></span>
                        </div>
                        <div class="view-bill-summary-row">
                            <span class="view-bill-summary-label">Paid Amount:</span>
                            <span class="view-bill-summary-value">₱${parseFloat(bill.paid_amount || 0).toFixed(2)}</span>
                        </div>
                        <div class="view-bill-summary-row">
                            <span class="view-bill-summary-label">Balance:</span>
                            <span class="view-bill-summary-value"><strong>₱${parseFloat(bill.balance || 0).toFixed(2)}</strong></span>
                        </div>
                    </div>
                </div>
            `;
            
            if (payments.length > 0) {
                html += `
                    <div class="view-bill-section">
                        <h4>Payment History</h4>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Amount (₱)</th>
                                        <th>Method</th>
                                        <th>Transaction ID</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;
                
                payments.forEach(payment => {
                    html += `
                        <tr>
                            <td>${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'}</td>
                            <td><strong>₱${parseFloat(payment.amount || 0).toFixed(2)}</strong></td>
                            <td>${(payment.payment_method || 'N/A').toUpperCase()}</td>
                            <td>${payment.transaction_id || 'N/A'}</td>
                            <td>
                                <span class="badge badge-${payment.status === 'completed' ? 'success' : 'warning'}">
                                    ${(payment.status || 'pending').toUpperCase()}
                                </span>
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }
            
            if (bill.notes) {
                html += `
                    <div class="view-bill-section">
                        <h4>Notes</h4>
                        <p style="color: #64748b; padding: 0.75rem; background: #f8fafc; border-radius: 6px;">${bill.notes}</p>
                    </div>
                `;
            }
            
            content.innerHTML = html;
        } else {
            content.innerHTML = '<div class="text-center-empty" style="padding: 2rem;"><p style="color: #dc2626;">Error: Could not load bill details</p></div>';
        }
    } catch (error) {
        console.error('Error loading bill:', error);
        document.getElementById('viewBillContent').innerHTML = '<div class="text-center-empty" style="padding: 2rem;"><p style="color: #dc2626;">Error loading bill details. Please try again.</p></div>';
    }
}

function closeViewBillModal() {
    document.getElementById('viewBillModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('viewBillContent').innerHTML = '<div class="text-center-empty" style="padding: 2rem;"><p>Loading bill details...</p></div>';
}

// Define handleBillFormSubmit first before using it
async function handleBillFormSubmit(e) {
    if (e) {
        e.preventDefault();
    }
    
    console.log('Form submit triggered');
    
    // Disable button to prevent double-click
    const createBtn = document.getElementById('createBillBtn');
    if (createBtn) {
        createBtn.disabled = true;
        createBtn.textContent = 'Creating...';
    }
    
    // Get the form element
    const form = document.getElementById('createBillForm');
    if (!form) {
        alert('Form not found!');
        // Re-enable button
        if (createBtn) {
            createBtn.disabled = false;
            createBtn.textContent = 'Create Bill';
        }
        return;
    }
    
    // Basic validation
    const patientId = document.getElementById('bill_patient_id').value;
    if (!patientId) {
        alert('Please select a patient.');
        return;
    }
    
    // Collect data directly from form inputs (don't use FormData to avoid issues)
    const data = {
        patient_id: document.getElementById('bill_patient_id').value,
        bill_type: 'other', // Overall bill type - removed from form, using default
        due_date: document.getElementById('bill_due_date').value,
        discount: parseFloat(document.getElementById('bill_discount').value) || 0,
        notes: document.getElementById('bill_notes').value || ''
    };
    
    // Add prescription_id if prescription is selected
    const prescriptionSelect = document.getElementById('prescription_select');
    if (prescriptionSelect && prescriptionSelect.value) {
        data.prescription_id = prescriptionSelect.value;
    }
    
    // Collect items directly from form inputs
    const items = [];
    const itemRows = document.querySelectorAll('#billItems .bill-item-row');
    
    itemRows.forEach((row, index) => {
        const category = row.getAttribute('data-category') || 'other';
        const code = row.querySelector('.bill-code')?.value?.trim() || '';
        const dateTime = row.querySelector('.bill-datetime')?.value || '';
        const itemName = row.querySelector('.bill-particulars')?.value?.trim();
        const quantity = parseFloat(row.querySelector('.bill-units')?.value) || 0;
        const unitPrice = parseFloat(row.querySelector('.bill-rate')?.value) || 0;
        const amount = parseFloat(row.querySelector('.bill-amount')?.value) || 0;
        
        if (itemName && quantity > 0 && unitPrice > 0) {
            // Get reference info from data attributes
            let referenceType = row.getAttribute('data-reference-type') || null;
            let referenceId = row.getAttribute('data-reference-id') || null;
            
            // If not in data attributes, determine from category
            if (!referenceType) {
                if (category === 'professional') {
                    referenceType = 'appointment';
                } else if (category === 'medication') {
                    referenceType = 'prescription';
                }
            }
            
            items.push({
                item_name: itemName,
                description: code ? `Code: ${code}` : '',
                quantity: quantity,
                unit_price: unitPrice,
                item_type: category,
                category: category,
                code: code,
                date_time: dateTime,
                reference_type: referenceType,
                reference_id: referenceId
            });
        }
    });
    
    // Validate items
    if (!items || items.length === 0) {
        alert('Please add at least one item to the bill.');
        return;
    }
    
    console.log('Items collected:', items);
    
    data.items = items;
    
    // Get totals with null checks
    const subtotalEl = document.getElementById('bill_subtotal');
    const totalEl = document.getElementById('bill_total');
    
    data.subtotal = subtotalEl ? (parseFloat(subtotalEl.value) || 0) : 0;
    data.tax = 0;
    data.total_amount = totalEl ? (parseFloat(totalEl.value) || 0) : 0;
    data.bill_type = 'other'; // Overall bill type
    
    console.log('Sending data:', data);
    
    try {
        const response = await fetch('<?= base_url('accounts/createBill') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        
        console.log('Response status:', response.status);
        
        // Check if response is OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 200));
            throw new Error('Server returned non-JSON response. Please check the server logs.');
        }
        
        const result = await response.json();
        console.log('Result:', result);
        
        if (result.success) {
            alert('Bill created successfully!');
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error creating bill:', error);
        alert('Error creating bill: ' + error.message);
    } finally {
        // Re-enable button
        const createBtn = document.getElementById('createBillBtn');
        if (createBtn) {
            createBtn.disabled = false;
            createBtn.textContent = 'Create Bill';
        }
    }
}

function submitBillForm() {
    console.log('Create Bill button clicked');
    try {
        // Call handleBillFormSubmit directly without event
        handleBillFormSubmit(null);
    } catch (error) {
        console.error('Error in submitBillForm:', error);
        alert('Error creating bill: ' + error.message);
    }
}

// Handle form submissions - wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createBillForm');
    if (form) {
        form.addEventListener('submit', handleBillFormSubmit);
    }
    
    // Initialize calculations for initial item row
    const initialRow = document.querySelector('.bill-item-row');
    if (initialRow) {
        attachItemListeners(initialRow);
    }
});

// Also try to attach immediately if DOM is already loaded
if (document.readyState !== 'loading') {
    const form = document.getElementById('createBillForm');
    if (form) {
        form.addEventListener('submit', handleBillFormSubmit);
    }
    
    // Initialize calculations for initial item row
    const initialRow = document.querySelector('.bill-item-row');
    if (initialRow) {
        attachItemListeners(initialRow);
    }
}

document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('<?= base_url('accounts/recordPayment') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            alert('Payment recorded successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error recording payment: ' + error.message);
    }
});
</script>

<style>
.bill-item-row {
    display: grid;
    grid-template-columns: 120px 100px 150px 2fr 100px 80px 100px auto;
    gap: 0.5rem;
    padding: 0.75rem;
    border-bottom: 1px solid #e9ecef;
    align-items: center;
}

.bill-items-header {
    display: none;
}

.filter-form {
    padding: 1rem 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.form-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}
</style>

<?= $this->endSection() ?>

    