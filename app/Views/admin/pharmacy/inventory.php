<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>💊</span>
                    Pharmacy & Inventory Dashboard
                </h2>
                <p class="page-subtitle">
                    Monitor pharmacy inventory, stock levels, and purchase orders
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Summary Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Medicines</div>
                <div class="kpi-value"><?= number_format($stats['total_medicines'] ?? 0) ?></div>
                <div class="kpi-change">In system</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Stock Quantity</div>
                <div class="kpi-value"><?= number_format($stats['total_stock_quantity'] ?? 0) ?></div>
                <div class="kpi-change">All units</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Low Stock Alerts</div>
                <div class="kpi-value"><?= number_format($stats['low_stock_alerts'] ?? 0) ?></div>
                <div class="kpi-change kpi-warning">Needs attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Expired Medicines</div>
                <div class="kpi-value"><?= number_format($stats['expired_medicines'] ?? 0) ?></div>
                <div class="kpi-change kpi-negative">Critical</div>
            </div>
        </div>
    </div>
</section>

<!-- Inventory Table -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Medicine Inventory</h2>
        <p>Complete inventory overview of all medicines</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Unit</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inventory)): ?>
                        <?php foreach ($inventory as $item): ?>
                            <?php
                            $med = $item['medicine'];
                            $inv = $item['inventory'];
                            $stockQty = $item['stock_quantity'];
                            $status = $item['status'];
                            
                            $statusClass = 'badge-success';
                            $statusLabel = 'OK';
                            if ($status === 'expired') {
                                $statusClass = 'badge-danger';
                                $statusLabel = 'EXPIRED';
                            } elseif ($status === 'low_stock') {
                                $statusClass = 'badge-warning';
                                $statusLabel = 'LOW STOCK';
                            } elseif ($status === 'out_of_stock') {
                                $statusClass = 'badge-danger';
                                $statusLabel = 'OUT OF STOCK';
                            }
                            ?>
                            <tr>
                                <td><strong><?= esc($med['name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($item['category'] ?? 'General') ?></td>
                                <td>
                                    <strong style="color: <?= $stockQty <= 0 ? '#ef4444' : ($stockQty < ($item['reorder_level'] ?? 10) ? '#f59e0b' : '#10b981') ?>;">
                                        <?= number_format($stockQty) ?>
                                    </strong>
                                </td>
                                <td>units</td>
                                <td>
                                    <?php if ($item['expiration_date']): ?>
                                        <?= date('M j, Y', strtotime($item['expiration_date'])) ?>
                                        <?php if (strtotime($item['expiration_date']) < strtotime('today')): ?>
                                            <br><small style="color: #ef4444;">🔴 Expired</small>
                                        <?php elseif (strtotime($item['expiration_date']) <= strtotime('+30 days')): ?>
                                            <br><small style="color: #f59e0b;">⚠️ Expiring soon</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?>" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="viewMedicineDetails(<?= $med['id'] ?>)" style="padding: 0.35rem 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">View Details</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                                No medicines found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Low Stock Alerts -->
<?php if (!empty($lowStockItems)): ?>
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>⚠️ Low Stock Alerts</h2>
        <p>Medicines with stock below reorder level</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table" style="background: #fef3c7;">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStockItems as $item): ?>
                        <tr>
                            <td><strong><?= esc($item['medicine']['name'] ?? 'N/A') ?></strong></td>
                            <td><strong style="color: #f59e0b;"><?= number_format($item['stock_quantity']) ?></strong></td>
                            <td><?= number_format($item['reorder_level']) ?></td>
                            <td><?= esc($item['inventory']['category'] ?? 'General') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Expired Medicines -->
<?php if (!empty($expiredItems)): ?>
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>🔴 Expired Medicines</h2>
        <p>Medicines that have passed their expiration date</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table" style="background: #fee2e2;">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Current Stock</th>
                        <th>Expiry Date</th>
                        <th>Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expiredItems as $item): ?>
                        <tr>
                            <td><strong><?= esc($item['medicine']['name'] ?? 'N/A') ?></strong></td>
                            <td><strong style="color: #ef4444;"><?= number_format($item['stock_quantity']) ?></strong></td>
                            <td><strong style="color: #ef4444;"><?= date('M j, Y', strtotime($item['expiration_date'])) ?></strong></td>
                            <td><?= esc($item['inventory']['category'] ?? 'General') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Stock Movement Logs -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Stock Movements</h2>
        <p>Last 10 stock movement transactions</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Medicine</th>
                        <th>Movement Type</th>
                        <th>Quantity Change</th>
                        <th>Performed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stockMovements)): ?>
                        <?php foreach ($stockMovements as $movement): ?>
                            <?php
                            $movementType = strtolower($movement['movement_type'] ?? '');
                            $quantityChange = (int)($movement['quantity_change'] ?? 0);
                            $typeClass = '';
                            $typeLabel = '';
                            
                            if ($movementType === 'add') {
                                $typeClass = 'badge-success';
                                $typeLabel = 'ADDITION';
                            } elseif ($movementType === 'dispense') {
                                $typeClass = 'badge-warning';
                                $typeLabel = 'DISPENSE';
                            } elseif ($movementType === 'adjust') {
                                $typeClass = 'badge-info';
                                $typeLabel = 'ADJUSTMENT';
                            } else {
                                $typeClass = 'badge-secondary';
                                $typeLabel = strtoupper($movementType);
                            }
                            ?>
                            <tr>
                                <td>
                                    <strong><?= !empty($movement['created_at']) ? date('M j, Y', strtotime($movement['created_at'])) : '—' ?></strong><br>
                                    <small style="color: #64748b;"><?= !empty($movement['created_at']) ? date('g:i A', strtotime($movement['created_at'])) : '—' ?></small>
                                </td>
                                <td><strong><?= esc($movement['medicine_name'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <span class="badge <?= $typeClass ?>" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                        <?= $typeLabel ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: <?= $quantityChange > 0 ? '#10b981' : '#ef4444' ?>;">
                                        <?= $quantityChange > 0 ? '+' : '' ?><?= number_format($quantityChange) ?>
                                    </strong>
                                </td>
                                <td><?= esc($movement['action_by_name'] ?? 'System') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 2rem; text-align: center; color: #64748b;">
                                No stock movements found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Orders Tracking -->
<section class="panel panel-spaced">
    <header class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Purchase Orders Tracking</h2>
            <p>All medicine purchase orders from suppliers</p>
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
                        <th>Reference Code</th>
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
                                <td>
                                    <?php if (!empty($order['reference'])): ?>
                                        <span style="color: #64748b; font-size: 0.875rem;"><?= esc($order['reference']) ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status === 'pending'): ?>
                                        <button onclick="markAsDelivered(<?= $order['id'] ?>)" class="btn-xs btn-success" style="padding: 0.5rem 1rem; background: #10b981; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; cursor: pointer;">
                                            Mark as Delivered
                                        </button>
                                    <?php elseif ($status === 'delivered'): ?>
                                        <span style="color: #10b981; font-size: 0.875rem; font-weight: 600;">✓ Delivered</span>
                                        <?php if (!empty($order['delivered_at'])): ?>
                                            <br><small style="color: #64748b;"><?= date('M j, Y', strtotime($order['delivered_at'])) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #64748b; font-size: 0.875rem;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding: 2rem; text-align: center; color: #64748b;">
                                No orders found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Medicine Details Modal -->
<div id="medicineDetailsModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeMedicineDetailsModal()"></div>
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h3>Medicine Details</h3>
            <button class="modal-close" onclick="closeMedicineDetailsModal()">&times;</button>
        </div>
        <div class="modal-body" id="medicineDetailsBody" style="max-height: 70vh; overflow-y: auto;">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
function viewMedicineDetails(medicationId) {
    fetch('<?= base_url('admin/pharmacy-inventory/details/') ?>' + medicationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('medicineDetailsModal');
                const modalBody = document.getElementById('medicineDetailsBody');
                
                let html = `
                    <div style="margin-bottom: 2rem;">
                        <h4 style="margin-bottom: 1rem; color: #1e293b;">Medicine Information</h4>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                            <div>
                                <small style="color: #64748b;">Medicine Name</small>
                                <p style="margin: 0.25rem 0 0; font-weight: 600; font-size: 1.1rem;">${data.medication.name || 'N/A'}</p>
                            </div>
                            <div>
                                <small style="color: #64748b;">Category</small>
                                <p style="margin: 0.25rem 0 0; font-weight: 600;">${data.inventory?.category || 'General'}</p>
                            </div>
                            <div>
                                <small style="color: #64748b;">Current Stock</small>
                                <p style="margin: 0.25rem 0 0; font-weight: 600; color: #10b981;">${data.inventory?.stock_quantity || 0} units</p>
                            </div>
                            <div>
                                <small style="color: #64748b;">Reorder Level</small>
                                <p style="margin: 0.25rem 0 0; font-weight: 600;">${data.inventory?.reorder_level || 10}</p>
                            </div>
                            ${data.inventory?.expiration_date ? `
                            <div>
                                <small style="color: #64748b;">Expiration Date</small>
                                <p style="margin: 0.25rem 0 0; font-weight: 600;">${new Date(data.inventory.expiration_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 2rem;">
                        <h4 style="margin-bottom: 1rem; color: #1e293b;">Stock History</h4>
                        <div class="table-container">
                            <table class="data-table" style="font-size: 0.875rem;">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Movement Type</th>
                                        <th>Quantity Change</th>
                                        <th>Previous Stock</th>
                                        <th>New Stock</th>
                                        <th>Performed By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;
                
                if (data.stockHistory && data.stockHistory.length > 0) {
                    data.stockHistory.forEach(movement => {
                        const movementType = (movement.movement_type || '').toLowerCase();
                        const typeClass = movementType === 'add' ? 'badge-success' : (movementType === 'dispense' ? 'badge-warning' : 'badge-info');
                        const typeLabel = movementType === 'add' ? 'ADDITION' : (movementType === 'dispense' ? 'DISPENSE' : 'ADJUSTMENT');
                        const qtyChange = movement.quantity_change || 0;
                        
                        html += `
                            <tr>
                                <td>${new Date(movement.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })}</td>
                                <td><span class="badge ${typeClass}" style="padding: 0.25rem 0.5rem; border-radius: 999px; font-size: 0.7rem; font-weight: 600;">${typeLabel}</span></td>
                                <td style="color: ${qtyChange > 0 ? '#10b981' : '#ef4444'}; font-weight: 600;">${qtyChange > 0 ? '+' : ''}${qtyChange}</td>
                                <td>${movement.previous_stock || 0}</td>
                                <td><strong>${movement.new_stock || 0}</strong></td>
                                <td>${movement.action_by_name || 'System'}</td>
                                <td style="color: #64748b; font-size: 0.8rem;">${movement.notes || '—'}</td>
                            </tr>
                        `;
                    });
                } else {
                    html += '<tr><td colspan="7" style="padding: 1rem; text-align: center; color: #94a3b8;">No stock history available</td></tr>';
                }
                
                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div>
                        <h4 style="margin-bottom: 1rem; color: #1e293b;">Purchase Orders</h4>
                        <div class="table-container">
                            <table class="data-table" style="font-size: 0.875rem;">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Supplier</th>
                                        <th>Quantity</th>
                                        <th>Order Date</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;
                
                if (data.orders && data.orders.length > 0) {
                    data.orders.forEach(order => {
                        const status = (order.status || 'pending').toLowerCase();
                        const statusClass = status === 'delivered' ? 'badge-success' : (status === 'pending' ? 'badge-warning' : 'badge-secondary');
                        
                        html += `
                            <tr>
                                <td><strong style="color: #3b82f6; font-family: monospace;">${order.order_number || 'N/A'}</strong></td>
                                <td>${order.supplier_name || 'N/A'}</td>
                                <td><strong>${order.quantity_ordered || 0}</strong></td>
                                <td>${order.order_date ? new Date(order.order_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</td>
                                <td><span class="badge ${statusClass}" style="padding: 0.25rem 0.5rem; border-radius: 999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">${status.toUpperCase()}</span></td>
                                <td style="color: #64748b; font-size: 0.8rem;">${order.reference || '—'}</td>
                            </tr>
                        `;
                    });
                } else {
                    html += '<tr><td colspan="6" style="padding: 1rem; text-align: center; color: #94a3b8;">No orders found for this medicine</td></tr>';
                }
                
                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                
                modalBody.innerHTML = html;
                modal.style.display = 'flex';
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

function closeMedicineDetailsModal() {
    const modal = document.getElementById('medicineDetailsModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}
</script>

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
    
    row.innerHTML = `
        <td style="padding: 0.75rem;">
            <select class="medicine-select" data-row="${rowIndex}" onchange="onMedicineChange(this)" required style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.875rem;">
                <option value="">Select Medicine</option>
                <?php foreach ($medications ?? [] as $med): ?>
                    <option value="<?= $med['id'] ?>" data-name="<?= esc($med['name']) ?>" data-price="<?= $med['price'] ?? 0 ?>"><?= esc($med['name']) ?> <?= isset($med['price']) && $med['price'] > 0 ? '₱' . number_format($med['price'], 2) : '' ?></option>
                <?php endforeach; ?>
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
    
    fetch('<?= base_url('admin/pharmacy-inventory/create-order') ?>', {
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

// Mark order as delivered
function markAsDelivered(orderId) {
    if (!confirm('Mark this order as delivered? This will update the inventory stock.')) {
        return;
    }
    
    fetch('<?= base_url('admin/pharmacy-inventory/mark-delivered') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ order_id: orderId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Order marked as delivered! Stock has been updated.');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to mark order as delivered'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error marking order as delivered');
    });
}
</script>

<?= $this->endSection() ?>

