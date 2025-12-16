<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📦</span>
                    Medicine Orders
                </h2>
                <p class="page-subtitle">
                    Manage medicine orders and suppliers
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Orders Table -->
<section class="panel panel-spaced">
    <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Orders Management</h2>
            <p>Track and manage medicine orders from suppliers</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <button onclick="processDeliveredOrders()" style="padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                <span>🔄</span> Update Stock from Delivered Orders
            </button>
            <button onclick="openCreateOrderModal()" style="padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                <span>+</span> Create Order
            </button>
        </div>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Medicine</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Price</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Received By</th>
                        <th>Reference</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $status = strtolower($order['status'] ?? 'pending');
                            $badgeClass = '';
                            if ($status === 'delivered') {
                                $badgeClass = 'badge-success';
                            } elseif ($status === 'pending') {
                                $badgeClass = 'badge-warning';
                            } else {
                                $badgeClass = 'badge-secondary';
                            }
                            ?>
                            <tr>
                                <td><strong style="color: #3b82f6; font-family: monospace;"><?= esc($order['order_number'] ?? 'N/A') ?></strong></td>
                                <td><strong><?= esc($order['medicine_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($order['supplier_name'] ?? 'N/A') ?></td>
                                <td><strong><?= number_format($order['quantity_ordered'] ?? 0) ?></strong></td>
                                <td><strong>₱<?= number_format($order['unit_price'] ?? 0, 2) ?></strong></td>
                                <td><strong style="color: #059669;">₱<?= number_format($order['total_price'] ?? 0, 2) ?></strong></td>
                                <td><?= !empty($order['order_date']) ? date('M j, Y', strtotime($order['order_date'])) : '—' ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                        <?= strtoupper($status) ?>
                                    </span>
                                </td>
                                <td><?= esc($order['received_by_name'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($order['reference'])): ?>
                                        <span style="color: #64748b; font-size: 0.875rem;"><?= esc($order['reference']) ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status === 'pending'): ?>
                                        <button onclick="updateOrderStatus(<?= $order['id'] ?>, 'delivered')" style="padding: 0.35rem 0.75rem; background: #10b981; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; margin-right: 0.5rem;">Mark Delivered</button>
                                        <button onclick="updateOrderStatus(<?= $order['id'] ?>, 'cancelled')" style="padding: 0.35rem 0.75rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">Cancel</button>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 0.875rem;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="padding: 2rem; text-align: center; color: #64748b;">
                                No orders found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Create Order Modal -->
<div id="createOrderModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeCreateOrderModal()"></div>
    <div class="modal-dialog" style="max-width: 900px; width: 90%;">
        <div class="modal-header">
            <h3>Create New Order</h3>
            <button class="modal-close" onclick="closeCreateOrderModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createOrderForm" onsubmit="submitOrder(event)">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem;">
                        Supplier Name <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="text" id="supplier_name" name="supplier_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem;" placeholder="Enter supplier name">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem;">
                        Order Date <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="date" id="order_date" name="order_date" required value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem;">
                        Reference (Invoice #) <small style="color: #64748b;">Optional</small>
                    </label>
                    <input type="text" id="reference" name="reference" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem;" placeholder="Enter invoice number or reference">
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <label style="display: block; font-weight: 600; color: #4a5568;">
                            Medicines <span style="color: #e53e3e;">*</span>
                        </label>
                        <button type="button" onclick="addMedicineRow()" style="padding: 0.5rem 1rem; background: #10b981; color: white; border: none; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                            <span>+</span> Add Medicine
                        </button>
                    </div>
                    <div style="overflow-x: auto;">
                        <table id="medicines_table" style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 0.5rem; overflow: hidden;">
                            <thead>
                                <tr style="background: #f8fafc;">
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem; border-bottom: 1px solid #e2e8f0;">Medicine</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem; border-bottom: 1px solid #e2e8f0;">Unit Price (₱)</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem; border-bottom: 1px solid #e2e8f0;">Quantity</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem; border-bottom: 1px solid #e2e8f0;">Total</th>
                                    <th style="padding: 0.75rem; text-align: center; font-weight: 600; color: #475569; font-size: 0.875rem; border-bottom: 1px solid #e2e8f0; width: 60px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="medicines_tbody">
                                <!-- Medicine rows will be added here -->
                            </tbody>
                            <tfoot>
                                <tr style="background: #f0f9ff; border-top: 2px solid #bae6fd;">
                                    <td colspan="3" style="padding: 1rem; text-align: right; font-weight: 600; color: #0369a1;">
                                        Grand Total:
                                    </td>
                                    <td colspan="2" style="padding: 1rem; font-size: 1.25rem; font-weight: 700; color: #0c4a6e;" id="grand_total_display">
                                        ₱0.00
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                    <button type="button" onclick="closeCreateOrderModal()" style="padding: 0.75rem 1.5rem; background: #e2e8f0; color: #475569; border: none; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer;">Create Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const medicationsData = <?= json_encode($medications ?? []) ?>;

function openCreateOrderModal() {
    document.getElementById('createOrderModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Add first row when opening modal
    if (document.getElementById('medicines_tbody').children.length === 0) {
        addMedicineRow();
    }
}

function closeCreateOrderModal() {
    document.getElementById('createOrderModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('createOrderForm').reset();
    // Clear all medicine rows
    document.getElementById('medicines_tbody').innerHTML = '';
    updateGrandTotal();
}

function addMedicineRow() {
    const tbody = document.getElementById('medicines_tbody');
    const row = document.createElement('tr');
    const rowIndex = tbody.children.length;
    
    // Build options HTML from JavaScript data
    let optionsHtml = '<option value="">Select Medicine</option>';
    if (medicationsData && medicationsData.length > 0) {
        medicationsData.forEach(function(med) {
            const medId = med.id || '';
            const medName = med.name || '';
            const medPrice = parseFloat(med.price || 0);
            const priceDisplay = medPrice > 0 ? ' ₱' + medPrice.toFixed(2) : '';
            optionsHtml += `<option value="${medId}" data-name="${medName.replace(/"/g, '&quot;')}" data-price="${medPrice}">${medName}${priceDisplay}</option>`;
        });
    }
    
    row.innerHTML = `
        <td style="padding: 0.75rem;">
            <select class="medicine-select" data-row="${rowIndex}" onchange="onMedicineChange(this)" required style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.875rem;">
                ${optionsHtml}
            </select>
            <input type="hidden" class="medicine-name" data-row="${rowIndex}">
        </td>
        <td style="padding: 0.75rem;">
            <input type="number" class="unit-price" data-row="${rowIndex}" min="0" step="0.01" oninput="calculateRowTotal(this)" required style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.875rem;" placeholder="0.00">
        </td>
        <td style="padding: 0.75rem;">
            <input type="number" class="quantity" data-row="${rowIndex}" min="1" oninput="calculateRowTotal(this)" required style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.875rem;" placeholder="0">
        </td>
        <td style="padding: 0.75rem;">
            <div class="row-total" data-row="${rowIndex}" style="font-weight: 600; color: #059669;">₱0.00</div>
        </td>
        <td style="padding: 0.75rem; text-align: center;">
            <button type="button" onclick="removeMedicineRow(this)" style="padding: 0.375rem 0.75rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer;">Remove</button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function removeMedicineRow(button) {
    const row = button.closest('tr');
    row.remove();
    updateGrandTotal();
    // Re-index rows
    const rows = document.querySelectorAll('#medicines_tbody tr');
    rows.forEach((r, index) => {
        r.querySelectorAll('[data-row]').forEach(el => {
            el.setAttribute('data-row', index);
            if (el.oninput) {
                el.setAttribute('oninput', el.getAttribute('oninput').replace(/\d+/, index));
            }
            if (el.onchange) {
                el.setAttribute('onchange', el.getAttribute('onchange').replace(/\d+/, index));
            }
        });
    });
}

function onMedicineChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const medicineName = selectedOption.getAttribute('data-name') || '';
    const price = parseFloat(selectedOption.getAttribute('data-price') || 0);
    
    const row = select.closest('tr');
    const nameInput = row.querySelector('.medicine-name');
    const unitPriceInput = row.querySelector('.unit-price');
    
    if (nameInput) nameInput.value = medicineName;
    if (unitPriceInput && price > 0) {
        unitPriceInput.value = price.toFixed(2);
    }
    
    calculateRowTotal(unitPriceInput || select);
}

function calculateRowTotal(input) {
    const row = input.closest('tr');
    if (!row) return;
    
    const unitPrice = parseFloat(row.querySelector('.unit-price')?.value) || 0;
    const quantity = parseFloat(row.querySelector('.quantity')?.value) || 0;
    const total = unitPrice * quantity;
    
    const totalDisplay = row.querySelector('.row-total');
    if (totalDisplay) {
        totalDisplay.textContent = '₱' + total.toFixed(2);
    }
    
    updateGrandTotal();
}

function updateGrandTotal() {
    const rows = document.querySelectorAll('#medicines_tbody tr');
    let grandTotal = 0;
    
    rows.forEach(row => {
        const unitPrice = parseFloat(row.querySelector('.unit-price')?.value) || 0;
        const quantity = parseFloat(row.querySelector('.quantity')?.value) || 0;
        grandTotal += unitPrice * quantity;
    });
    
    document.getElementById('grand_total_display').textContent = '₱' + grandTotal.toFixed(2);
}

function submitOrder(event) {
    event.preventDefault();
    
    const supplierName = document.getElementById('supplier_name').value;
    const orderDate = document.getElementById('order_date').value;
    const reference = document.getElementById('reference').value;
    
    if (!supplierName || !orderDate) {
        alert('Please fill in supplier name and order date');
        return;
    }
    
    // Collect all medicine rows
    const rows = document.querySelectorAll('#medicines_tbody tr');
    const medicines = [];
    
    rows.forEach(row => {
        const select = row.querySelector('.medicine-select');
        const nameInput = row.querySelector('.medicine-name');
        const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        
        if (select.value && nameInput.value && unitPrice > 0 && quantity > 0) {
            medicines.push({
                medication_id: select.value,
                medicine_name: nameInput.value,
                unit_price: unitPrice,
                quantity_ordered: quantity,
                total_price: unitPrice * quantity
            });
        }
    });
    
    if (medicines.length === 0) {
        alert('Please add at least one medicine to the order');
        return;
    }
    
    // Prepare data
    const orderData = {
        supplier_name: supplierName,
        order_date: orderDate,
        reference: reference || null,
        medicines: medicines
    };
    
    fetch('<?= base_url('pharmacy/orders/create') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order created successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to create order'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating order');
    });
}

function updateOrderStatus(orderId, status) {
    const statusText = status === 'delivered' ? 'delivered' : 'cancelled';
    if (!confirm(`Are you sure you want to mark this order as ${statusText}?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', status);
    
    fetch('<?= base_url('pharmacy/orders/updateStatus') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update order status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order status');
    });
}

function processDeliveredOrders() {
    if (!confirm('This will update stock for all delivered orders that haven\'t been processed yet. Continue?')) {
        return;
    }
    
    // Show loading
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span>⏳</span> Processing...';
    
    fetch('<?= base_url('pharmacy/orders/processDelivered') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        button.innerHTML = originalText;
        
        if (data.success) {
            alert(data.message + (data.processed > 0 ? '\n\nStock has been updated! Please refresh the inventory page to see the changes.' : ''));
            // Optionally reload the page
            if (data.processed > 0) {
                if (confirm('Stock updated! Would you like to go to the Inventory page to see the changes?')) {
                    window.location.href = '<?= base_url('pharmacy/inventory') ?>';
                }
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to process delivered orders'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.disabled = false;
        button.innerHTML = originalText;
        alert('Error processing delivered orders');
    });
}
</script>

<?= $this->endSection() ?>
