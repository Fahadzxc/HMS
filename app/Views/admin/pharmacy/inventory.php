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
    <header class="panel-header">
        <h2>Purchase Orders Tracking</h2>
        <p>All medicine purchase orders from suppliers</p>
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
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
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

<?= $this->endSection() ?>

