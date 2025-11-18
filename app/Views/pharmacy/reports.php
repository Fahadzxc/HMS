<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📈</span>
                    Pharmacy Reports
                </h2>
                <p class="page-subtitle">
                    View dispensing reports and expiring medicines
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Report Filters -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Report Filters</h2>
    </header>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Report Type</label>
            <select id="reportType" onchange="updateReportType()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="daily" <?= ($reportType ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily Report</option>
                <option value="monthly" <?= ($reportType ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly Report</option>
                <option value="expiring" <?= ($reportType ?? '') === 'expiring' ? 'selected' : '' ?>>Expiring Medicines</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date From</label>
            <input type="date" id="dateFrom" value="<?= esc($dateFrom ?? date('Y-m-01')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date To</label>
            <input type="date" id="dateTo" value="<?= esc($dateTo ?? date('Y-m-d')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div>
            <button onclick="applyReportFilters()" style="padding: 0.5rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Generate Report</button>
        </div>
    </div>
</section>

<!-- Dispensing Report -->
<?php if (($reportType ?? 'daily') !== 'expiring'): ?>
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2><?= ($reportType ?? 'daily') === 'daily' ? 'Daily' : 'Monthly' ?> Dispensing Report</h2>
        <p>Prescriptions dispensed from <?= date('M j, Y', strtotime($dateFrom ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($dateTo ?? date('Y-m-d'))) ?></p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>RX#</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Medications</th>
                        <th>Total Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dispensingReport)): ?>
                        <?php foreach ($dispensingReport as $rx): ?>
                            <?php
                            $items = json_decode($rx['items_json'] ?? '[]', true);
                            $totalQty = 0;
                            foreach ($items as $item) {
                                $totalQty += (int)($item['quantity'] ?? 0);
                            }
                            ?>
                            <tr>
                                <td><?= !empty($rx['updated_at']) ? date('M j, Y', strtotime($rx['updated_at'])) : '—' ?></td>
                                <td><strong>RX#<?= str_pad((string)$rx['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td>
                                    <?php
                                    $patientModel = new \App\Models\PatientModel();
                                    $patient = $patientModel->find($rx['patient_id']);
                                    echo esc($patient['full_name'] ?? 'N/A');
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $db = \Config\Database::connect();
                                    $doctor = $db->table('users')->where('id', $rx['doctor_id'])->get()->getRowArray();
                                    echo esc($doctor['name'] ?? 'N/A');
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($items)): ?>
                                        <?php foreach (array_slice($items, 0, 2) as $item): ?>
                                            <div><?= esc($item['name'] ?? 'N/A') ?></div>
                                        <?php endforeach; ?>
                                        <?php if (count($items) > 2): ?>
                                            <small style="color: #64748b;">+<?= count($items) - 2 ?> more</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= number_format($totalQty) ?></strong></td>
                                <td>
                                    <span class="badge badge-success" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; background: #dcfce7; color: #15803d; text-transform: uppercase;">
                                        DISPENSED
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                                No dispensing records found for the selected period
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Expiring Medicines Report -->
<?php if (($reportType ?? 'daily') === 'expiring'): ?>
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Expiring Medicines Report</h2>
        <p>Medicines expiring within the next 90 days</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Expiration Date</th>
                        <th>Days Until Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($expiringMedicines)): ?>
                        <?php foreach ($expiringMedicines as $medicine): ?>
                            <?php
                            $expDate = $medicine['expiration_date'] ?? null;
                            $daysUntilExpiry = $expDate ? (strtotime($expDate) - strtotime('today')) / 86400 : null;
                            $statusClass = 'badge-warning';
                            $statusLabel = 'Expiring Soon';
                            
                            if ($daysUntilExpiry !== null) {
                                if ($daysUntilExpiry < 0) {
                                    $statusClass = 'badge-danger';
                                    $statusLabel = 'Expired';
                                } elseif ($daysUntilExpiry <= 7) {
                                    $statusClass = 'badge-danger';
                                    $statusLabel = 'Critical';
                                } elseif ($daysUntilExpiry <= 30) {
                                    $statusClass = 'badge-warning';
                                    $statusLabel = 'Expiring Soon';
                                }
                            }
                            ?>
                            <tr>
                                <td><strong><?= esc($medicine['name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($medicine['category'] ?? 'General') ?></td>
                                <td><strong><?= number_format($medicine['stock_quantity'] ?? 0) ?></strong></td>
                                <td>
                                    <?php if ($expDate): ?>
                                        <strong><?= date('M j, Y', strtotime($expDate)) ?></strong>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($daysUntilExpiry !== null): ?>
                                        <strong style="color: <?= $daysUntilExpiry < 0 ? '#ef4444' : ($daysUntilExpiry <= 7 ? '#f59e0b' : '#10b981') ?>;">
                                            <?= $daysUntilExpiry < 0 ? 'Expired' : number_format($daysUntilExpiry) . ' days' ?>
                                        </strong>
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
                                    <button onclick="viewMedicine(<?= $medicine['id'] ?? 0 ?>)" style="padding: 0.35rem 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                                No expiring medicines found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>


<script>
function updateReportType() {
    const reportType = document.getElementById('reportType').value;
    if (reportType === 'expiring') {
        document.getElementById('dateFrom').style.display = 'none';
        document.getElementById('dateTo').style.display = 'none';
    } else {
        document.getElementById('dateFrom').style.display = 'block';
        document.getElementById('dateTo').style.display = 'block';
    }
}

function applyReportFilters() {
    const reportType = document.getElementById('reportType').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const url = new URL(window.location.href);
    url.searchParams.set('type', reportType);
    if (reportType !== 'expiring') {
        url.searchParams.set('date_from', dateFrom);
        url.searchParams.set('date_to', dateTo);
    }
    
    window.location.href = url.toString();
}

function viewMedicine(medicineId) {
    window.location.href = '<?= base_url('pharmacy/inventory') ?>?medicine_id=' + medicineId;
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    updateReportType();
});
</script>

<?= $this->endSection() ?>

