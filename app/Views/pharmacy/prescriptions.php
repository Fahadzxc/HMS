    <?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>💊</span>
                    Prescription Management
                </h2>
                <p class="page-subtitle">
                    Manage and dispense patient prescriptions
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
                <div class="kpi-label">Pending Prescriptions</div>
                <div class="kpi-value"><?= number_format(count(array_filter($prescriptions ?? [], fn($p) => $p['status'] === 'pending'))) ?></div>
                <div class="kpi-change kpi-warning">Awaiting dispense</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Dispensed Today</div>
                <div class="kpi-value"><?= number_format(count(array_filter($prescriptions ?? [], fn($p) => $p['status'] === 'dispensed' && date('Y-m-d', strtotime($p['updated_at'] ?? '')) === date('Y-m-d')))) ?></div>
                <div class="kpi-change kpi-positive">Today</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Prescriptions</div>
                <div class="kpi-value"><?= number_format(count($prescriptions ?? [])) ?></div>
                <div class="kpi-change">All time</div>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Filter Prescriptions</h2>
    </header>
    <div style="display: flex; gap: 1rem; align-items: center;">
        <select id="statusFilter" onchange="filterPrescriptions()" style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem;">
            <option value="all" <?= ($filterStatus ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
            <option value="pending" <?= ($filterStatus ?? 'all') === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="dispensed" <?= ($filterStatus ?? 'all') === 'dispensed' ? 'selected' : '' ?>>Dispensed</option>
            <option value="completed" <?= ($filterStatus ?? 'all') === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= ($filterStatus ?? 'all') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </div>
</section>

<!-- Prescriptions Table -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Prescription List</h2>
        <p>View and manage all prescriptions</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>RX#</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Medications</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Date Issued</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($prescriptions)): ?>
                        <?php foreach ($prescriptions as $rx): ?>
                            <tr>
                                <td><strong><?= esc($rx['rx_number'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <strong><?= esc($rx['patient_name'] ?? 'N/A') ?></strong>
                                    <?php if (!empty($rx['patient_age'])): ?>
                                        <br><small style="color: #64748b;">Age: <?= esc($rx['patient_age']) ?> • <?= ucfirst(esc($rx['patient_gender'] ?? '')) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($rx['doctor_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($rx['items'])): ?>
                                        <?php foreach (array_slice($rx['items'], 0, 2) as $item): ?>
                                            <div style="margin-bottom: 0.25rem;">
                                                <strong><?= esc($item['name'] ?? 'N/A') ?></strong>
                                                <?php if (!empty($item['dosage'])): ?>
                                                    <br><small style="color: #64748b;"><?= esc($item['dosage']) ?> • <?= esc($item['frequency'] ?? '') ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($rx['items']) > 2): ?>
                                            <small style="color: #64748b;">+<?= count($rx['items']) - 2 ?> more</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">No medications</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($rx['items'])): ?>
                                        <?php 
                                        // Use calculated total_quantity from controller, or calculate here
                                        $totalQty = $rx['total_quantity'] ?? 0;
                                        if ($totalQty <= 0) {
                                            foreach ($rx['items'] as $item) {
                                                $qty = (int)($item['quantity'] ?? 0);
                                                if ($qty <= 0) {
                                                    // Calculate from duration and frequency
                                                    $durationStr = $item['duration'] ?? '';
                                                    $frequency = $item['frequency'] ?? '';
                                                    if (!empty($durationStr)) {
                                                        preg_match('/(\d+)/', $durationStr, $matches);
                                                        if (!empty($matches[1])) {
                                                            $durationDays = (int)$matches[1];
                                                            if (strpos(strtolower($frequency), '2x') !== false || 
                                                                strpos(strtolower($frequency), 'twice') !== false ||
                                                                strpos(strtolower($frequency), '2') !== false) {
                                                                $qty = $durationDays * 2;
                                                            } elseif (strpos(strtolower($frequency), '3x') !== false || 
                                                                     strpos(strtolower($frequency), 'thrice') !== false ||
                                                                     strpos(strtolower($frequency), '3') !== false) {
                                                                $qty = $durationDays * 3;
                                                            } elseif (strpos(strtolower($frequency), 'every 6 hours') !== false) {
                                                                $qty = $durationDays * 4;
                                                            } elseif (strpos(strtolower($frequency), 'every 8 hours') !== false) {
                                                                $qty = $durationDays * 3;
                                                            } else {
                                                                $qty = $durationDays;
                                                            }
                                                        }
                                                    }
                                                    if ($qty <= 0) $qty = 1;
                                                }
                                                $totalQty += $qty;
                                            }
                                        }
                                        ?>
                                        <strong style="color: #1e293b; font-size: 1rem;"><?= number_format($totalQty) ?></strong>
                                        <br><small style="color: #64748b; font-size: 0.75rem;">units</small>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status = strtolower($rx['status'] ?? 'pending');
                                    $badgeClass = '';
                                    $badgeText = '';
                                    if ($status === 'dispensed' || $status === 'completed') {
                                        $badgeClass = 'badge-success';
                                        $badgeText = 'COMPLETED';
                                    } elseif ($status === 'pending') {
                                        $badgeClass = 'badge-warning';
                                        $badgeText = 'AWAITING DISPENSE';
                                    } else {
                                        $badgeClass = 'badge-danger';
                                        $badgeText = strtoupper($status);
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; display: inline-flex; align-items: center; gap: 0.35rem;">
                                        <?php if ($status === 'pending'): ?>
                                            <span style="display: inline-block; width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        <?php endif; ?>
                                        <?= $badgeText ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= !empty($rx['created_at']) ? date('M j, Y', strtotime($rx['created_at'])) : '—' ?></strong>
                                    <?php if (!empty($rx['created_at'])): ?>
                                        <br><small style="color: #64748b;">Issued</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick="viewPrescription(<?= $rx['id'] ?>)" style="padding: 0.35rem 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; margin-right: 0.5rem;">View</button>
                                    <?php if ($status === 'pending'): ?>
                                        <button onclick="dispensePrescription(<?= $rx['id'] ?>)" style="padding: 0.35rem 0.75rem; background: #10b981; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">Dispense</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding: 2rem; text-align: center; color: #64748b;">
                                No prescriptions found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Prescription Details Modal -->
<div id="prescriptionModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closePrescriptionModal()"></div>
    <div class="modal-dialog" style="max-width: 800px;">
        <div class="modal-header">
            <h3>Prescription Details</h3>
            <button class="modal-close" onclick="closePrescriptionModal()">&times;</button>
        </div>
        <div class="modal-body" id="prescriptionModalBody">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>


<script>
function filterPrescriptions() {
    const status = document.getElementById('statusFilter').value;
    const url = new URL(window.location.href);
    if (status === 'all') {
        url.searchParams.delete('status');
    } else {
        url.searchParams.set('status', status);
    }
    window.location.href = url.toString();
}

function viewPrescription(prescriptionId) {
    // Fetch prescription details
    fetch('<?= base_url('pharmacy/prescriptions/view/') ?>' + prescriptionId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('prescriptionModal');
                const modalBody = document.getElementById('prescriptionModalBody');
                
                let html = `
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 0.75rem; color: #1e293b;">Patient Information</h4>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                            <div>
                                <small style="color: #64748b;">Patient Name</small>
                                <p style="margin: 0.25rem 0 0; font-weight: 600;">${data.prescription.patient_name || 'N/A'}</p>
                            </div>
                            <div>
                                <small style="color: #64748b;">Doctor</small>
                                <p style="margin: 0.25rem 0 0; font-weight: 600;">${data.prescription.doctor_name || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 0.75rem; color: #1e293b;">Medications</h4>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: #475569;">Medication</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: #475569;">Dosage</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: #475569;">Frequency</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: #475569;">Quantity</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: #475569;">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                if (data.prescription.items && data.prescription.items.length > 0) {
                    data.prescription.items.forEach(item => {
                        html += `
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem;">${item.name || 'N/A'}</td>
                                <td style="padding: 0.75rem;">${item.dosage || '—'}</td>
                                <td style="padding: 0.75rem;">${item.frequency || '—'}</td>
                                <td style="padding: 0.75rem;">${item.quantity || '—'}</td>
                                <td style="padding: 0.75rem;">${item.duration || '—'}</td>
                            </tr>
                        `;
                    });
                } else {
                    html += '<tr><td colspan="5" style="padding: 1rem; text-align: center; color: #94a3b8;">No medications</td></tr>';
                }
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                if (data.prescription.notes) {
                    html += `
                        <div>
                            <h4 style="margin-bottom: 0.75rem; color: #1e293b;">Notes</h4>
                            <p style="color: #475569; line-height: 1.6;">${data.prescription.notes}</p>
                        </div>
                    `;
                }
                
                modalBody.innerHTML = html;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            } else {
                alert('Error loading prescription details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading prescription details');
        });
}

function closePrescriptionModal() {
    const modal = document.getElementById('prescriptionModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

function dispensePrescription(prescriptionId) {
    if (!confirm('Are you sure you want to dispense this prescription?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('prescription_id', prescriptionId);
    
    fetch('<?= base_url('pharmacy/dispensePrescription') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Prescription dispensed successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to dispense prescription'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error dispensing prescription');
    });
}
</script>

<?= $this->endSection() ?>

