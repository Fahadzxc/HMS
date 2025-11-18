<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Stock Movement Logs
                </h2>
                <p class="page-subtitle">
                    Track all stock additions, deductions, and adjustments
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Statistics Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Movements</div>
                <div class="kpi-value"><?= number_format($stats['total'] ?? 0) ?></div>
                <div class="kpi-change">All time</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Stock Added</div>
                <div class="kpi-value"><?= number_format($stats['added'] ?? 0) ?></div>
                <div class="kpi-change kpi-positive">Additions</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Stock Dispensed</div>
                <div class="kpi-value"><?= number_format($stats['dispensed'] ?? 0) ?></div>
                <div class="kpi-change kpi-warning">Deductions</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Adjustments</div>
                <div class="kpi-value"><?= number_format($stats['adjustments'] ?? 0) ?></div>
                <div class="kpi-change">Manual changes</div>
            </div>
        </div>
    </div>
</section>

<!-- Stock Movement Table -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Stock Movement History</h2>
        <p>Complete audit trail of all stock movements</p>
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
                        <th>Previous Stock</th>
                        <th>New Stock</th>
                        <th>Action By</th>
                        <th>Notes</th>
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
                                $typeLabel = 'ADD';
                            } elseif ($movementType === 'dispense') {
                                $typeClass = 'badge-warning';
                                $typeLabel = 'DISPENSE';
                            } elseif ($movementType === 'adjust') {
                                $typeClass = 'badge-info';
                                $typeLabel = 'ADJUST';
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
                                <td><?= number_format($movement['previous_stock'] ?? 0) ?></td>
                                <td><strong><?= number_format($movement['new_stock'] ?? 0) ?></strong></td>
                                <td><?= esc($movement['action_by_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($movement['notes'])): ?>
                                        <span style="color: #475569;"><?= esc(substr($movement['notes'], 0, 50)) ?><?= strlen($movement['notes']) > 50 ? '...' : '' ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding: 2rem; text-align: center; color: #64748b;">
                                No stock movements found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>


<?= $this->endSection() ?>

