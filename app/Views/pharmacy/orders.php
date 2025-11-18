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
        <button onclick="openCreateOrderModal()" style="padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
            <span>+</span> Create Order
        </button>
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
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Create New Order</h3>
            <button class="modal-close" onclick="closeCreateOrderModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createOrderForm" onsubmit="submitOrder(event)">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem;">
                        Medicine <span style="color: #e53e3e;">*</span>
                    </label>
                    <select id="medication_id" name="medication_id" onchange="onMedicationChange()" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem;" required>
                        <option value="">Select Medicine</option>
                        <?php foreach ($medications ?? [] as $med): ?>
                            <option value="<?= $med['id'] ?>" data-name="<?= esc($med['name']) ?>"><?= esc($med['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" id="medicine_name" name="medicine_name">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem;">
                        Supplier Name <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="text" id="supplier_name" name="supplier_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem;" placeholder="Enter supplier name">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem;">
                        Quantity Ordered <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="number" id="quantity_ordered" name="quantity_ordered" required min="1" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem;" placeholder="Enter quantity">
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
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                    <button type="button" onclick="closeCreateOrderModal()" style="padding: 0.75rem 1.5rem; background: #e2e8f0; color: #475569; border: none; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer;">Create Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateOrderModal() {
    document.getElementById('createOrderModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeCreateOrderModal() {
    document.getElementById('createOrderModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('createOrderForm').reset();
    document.getElementById('medicine_name').value = '';
}

function onMedicationChange() {
    const select = document.getElementById('medication_id');
    const selectedOption = select.options[select.selectedIndex];
    document.getElementById('medicine_name').value = selectedOption.getAttribute('data-name') || '';
}

function submitOrder(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('medicine_name', document.getElementById('medicine_name').value);
    
    fetch('<?= base_url('pharmacy/orders/create') ?>', {
        method: 'POST',
        body: formData
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
</script>

<?= $this->endSection() ?>
