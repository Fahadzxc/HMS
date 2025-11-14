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

<!-- Filters -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>Filters</h3>
    </header>
    <div class="stack">
        <form method="GET" action="/accounts/billing" class="filter-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-input">
                        <option value="">All Status</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="partial" <?= ($filters['status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                        <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Patient</label>
                    <select name="patient_id" class="form-input">
                        <option value="">All Patients</option>
                        <?php foreach ($patients ?? [] as $patient): ?>
                            <option value="<?= $patient['id'] ?>" <?= ($filters['patient_id'] ?? '') == $patient['id'] ? 'selected' : '' ?>>
                                <?= esc($patient['full_name']) ?> (<?= esc($patient['patient_id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-primary">Filter</button>
                    <a href="/accounts/billing" class="btn-secondary">Clear</a>
                </div>
            </div>
        </form>
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
                        <th>Tax</th>
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
                                <td>₱<?= number_format($bill['tax'] ?? 0, 2) ?></td>
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
                    <label>Discount</label>
                    <input type="number" name="discount" id="bill_discount" class="form-input" value="0" step="0.01" onchange="calculateBillTotal()">
                </div>
                <div class="form-group">
                    <label>Tax (12%)</label>
                    <input type="number" id="bill_tax" class="form-input" value="0" step="0.01" readonly>
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
    document.getElementById('bill_tax').value = '0.00';
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
        billItemCount = 0;
        document.getElementById('bill_subtotal').value = '0.00';
        document.getElementById('bill_tax').value = '0.00';
        document.getElementById('bill_total').value = '0.00';
        document.getElementById('bill_amount_words').value = '';
        document.getElementById('categoryTotals').innerHTML = '';
        document.getElementById('addItemBtn').style.display = 'none';
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
                        <select class="form-input bill-category" onchange="updateItemCategory(this)" required>
                            <option value="room" ${item.category === 'room' ? 'selected' : ''}>Room/Bed Charges</option>
                            <option value="nursing" ${item.category === 'nursing' ? 'selected' : ''}>Nursing Charges</option>
                            <option value="ot" ${item.category === 'ot' ? 'selected' : ''}>OT Charges</option>
                            <option value="professional" ${item.category === 'professional' ? 'selected' : ''}>Professional Fees</option>
                            <option value="medication" ${item.category === 'medication' ? 'selected' : ''}>Medication</option>
                            <option value="lab" ${item.category === 'lab' ? 'selected' : ''}>Lab Test</option>
                            <option value="other" ${item.category === 'other' ? 'selected' : ''}>Other</option>
                        </select>
                        <input type="text" class="form-input bill-code" placeholder="Code" name="items[${billItemCount}][code]" value="${code}" readonly>
                        <input type="datetime-local" class="form-input bill-datetime" placeholder="Date & Time" name="items[${billItemCount}][date_time]" value="${currentDateTime}" readonly>
                        <input type="text" class="form-input bill-particulars" placeholder="Particulars *" name="items[${billItemCount}][item_name]" value="${particulars}" required readonly>
                        <input type="number" class="form-input bill-rate" placeholder="Rate *" name="items[${billItemCount}][unit_price]" value="${rate}" step="0.01" required readonly>
                        <input type="number" class="form-input bill-units" placeholder="Units *" name="items[${billItemCount}][quantity]" value="${units}" step="0.01" required readonly>
                        <input type="number" class="form-input bill-amount" placeholder="Amount" name="items[${billItemCount}][amount]" value="${amount}" readonly>
                        <span class="text-muted" style="padding: 0.5rem; display: flex; align-items: center; font-size: 0.85rem;">Auto-loaded</span>
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
            const taxEl = document.getElementById('bill_tax');
            const totalEl = document.getElementById('bill_total');
            const wordsEl = document.getElementById('bill_amount_words');
            if (subtotalEl) subtotalEl.value = '0.00';
            if (taxEl) taxEl.value = '0.00';
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

function calculateBillTotal() {
    let subtotal = 0;
    document.querySelectorAll('.bill-amount').forEach(input => {
        if (input.value) {
            subtotal += parseFloat(input.value) || 0;
        }
    });
    
    const discount = parseFloat(document.getElementById('bill_discount').value) || 0;
    const tax = (subtotal - discount) * 0.12;
    const total = subtotal - discount + tax;
    
    document.getElementById('bill_subtotal').value = subtotal.toFixed(2);
    document.getElementById('bill_tax').value = tax.toFixed(2);
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

function viewBill(billId) {
    window.location.href = '/accounts/billing?bill_id=' + billId;
}

// Define handleBillFormSubmit first before using it
async function handleBillFormSubmit(e) {
    if (e) {
        e.preventDefault();
    }
    
    console.log('Form submit triggered');
    
    // Get the form element
    const form = document.getElementById('createBillForm');
    if (!form) {
        alert('Form not found!');
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
        bill_type: document.getElementById('bill_type').value,
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
    const taxEl = document.getElementById('bill_tax');
    const totalEl = document.getElementById('bill_total');
    
    data.subtotal = subtotalEl ? (parseFloat(subtotalEl.value) || 0) : 0;
    data.tax = taxEl ? (parseFloat(taxEl.value) || 0) : 0;
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
    }
}

function submitBillForm() {
    console.log('Create Bill button clicked');
    // Call handleBillFormSubmit directly without event
    handleBillFormSubmit(null);
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
    grid-template-columns: 2fr 2fr 1fr 1fr auto;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    align-items: end;
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

