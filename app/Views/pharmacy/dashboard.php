<!-- Pharmacy dashboard partial (inner content only) -->

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
                                    <?php
                                    $stockClass = 'stock-ok';
                                    if ($stockQty <= 0) {
                                        $stockClass = 'stock-zero';
                                    } elseif ($stockQty < ($item['reorder_level'] ?? 10)) {
                                        $stockClass = 'stock-low';
                                    }
                                    ?>
                                    <strong class="pharmacy-stock-qty <?= $stockClass ?>">
                                        <?= number_format($stockQty) ?>
                                    </strong>
                                </td>
                                <td>units</td>
                                <td>
                                    <?php if ($item['expiration_date']): ?>
                                        <?= date('M j, Y', strtotime($item['expiration_date'])) ?>
                                        <?php if (strtotime($item['expiration_date']) < strtotime('today')): ?>
                                            <br><small class="pharmacy-expired-text">🔴 Expired</small>
                                        <?php elseif (strtotime($item['expiration_date']) <= strtotime('+30 days')): ?>
                                            <br><small class="pharmacy-expiring-text">⚠️ Expiring soon</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="pharmacy-empty-text">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?> pharmacy-badge">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('pharmacy/inventory') ?>" class="pharmacy-manage-btn">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="pharmacy-empty-cell">
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
            <table class="data-table pharmacy-low-stock-table">
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
                            <td><strong class="pharmacy-low-stock-qty"><?= number_format($item['stock_quantity']) ?></strong></td>
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
            <table class="data-table pharmacy-expired-table">
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
                            <td><strong class="pharmacy-expired-qty"><?= number_format($item['stock_quantity']) ?></strong></td>
                            <td><strong class="pharmacy-expired-date"><?= date('M j, Y', strtotime($item['expiration_date'])) ?></strong></td>
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
                                    <small class="pharmacy-movement-time"><?= !empty($movement['created_at']) ? date('g:i A', strtotime($movement['created_at'])) : '—' ?></small>
                                </td>
                                <td><strong><?= esc($movement['medicine_name'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <span class="badge <?= $typeClass ?> pharmacy-badge">
                                        <?= $typeLabel ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="<?= $quantityChange > 0 ? 'pharmacy-qty-positive' : 'pharmacy-qty-negative' ?>">
                                        <?= $quantityChange > 0 ? '+' : '' ?><?= number_format($quantityChange) ?>
                                    </strong>
                                </td>
                                <td><?= esc($movement['action_by_name'] ?? 'System') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="pharmacy-empty-cell">
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
                                <td><strong class="pharmacy-order-number"><?= esc($order['order_number'] ?? 'N/A') ?></strong></td>
                                <td><strong><?= esc($order['medicine_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($order['supplier_name'] ?? 'N/A') ?></td>
                                <td><strong><?= number_format($order['quantity_ordered'] ?? 0) ?></strong></td>
                                <td><?= !empty($order['order_date']) ? date('M j, Y', strtotime($order['order_date'])) : '—' ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?> pharmacy-badge">
                                        <?= strtoupper($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($order['reference'])): ?>
                                        <span class="pharmacy-reference-text"><?= esc($order['reference']) ?></span>
                                    <?php else: ?>
                                        <span class="pharmacy-empty-ref">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="pharmacy-empty-cell">
                                No orders found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
