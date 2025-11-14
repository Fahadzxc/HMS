<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>🏥</span>
                    Insurance Claims Management
                </h2>
                <p class="page-subtitle">
                    Welcome, <?= esc($user_name ?? 'Accountant') ?>
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
            <button class="btn-primary" onclick="openCreateClaimModal()">
                <span>➕</span> Create New Claim
            </button>
        </div>
    </header>
</section>

<!-- KPI Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Claims</div>
                <div class="kpi-value">₱<?= number_format($total_claims ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">All claims</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Approved Claims</div>
                <div class="kpi-value">₱<?= number_format($approved_claims ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">Approved amount</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Claims</div>
                <div class="kpi-value"><?= $pending_claims_count ?? 0 ?></div>
                <div class="kpi-change kpi-warning">Awaiting approval</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Records</div>
                <div class="kpi-value"><?= count($claims ?? []) ?></div>
                <div class="kpi-change">All claims</div>
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
        <form method="GET" action="/accounts/insurance" class="filter-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-input">
                        <option value="">All Status</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="submitted" <?= ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
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
                    <label>Insurance Provider</label>
                    <select name="insurance_provider" class="form-input">
                        <option value="">All Providers</option>
                        <?php foreach ($providers ?? [] as $provider): ?>
                            <option value="<?= esc($provider['insurance_provider']) ?>" <?= ($filters['insurance_provider'] ?? '') === $provider['insurance_provider'] ? 'selected' : '' ?>>
                                <?= esc($provider['insurance_provider']) ?>
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
                    <a href="/accounts/insurance" class="btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Claims Table -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h3>Insurance Claims</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Claim #</th>
                        <th>Bill #</th>
                        <th>Patient</th>
                        <th>Provider</th>
                        <th>Claim Amount</th>
                        <th>Approved Amount</th>
                        <th>Deductible</th>
                        <th>Co-Payment</th>
                        <th>Status</th>
                        <th>Submitted Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($claims)): ?>
                        <?php foreach ($claims as $claim): ?>
                            <tr>
                                <td><strong><?= esc($claim['claim_number'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($claim['bill_number'] ?? 'N/A') ?></td>
                                <td>
                                    <strong><?= esc($claim['patient_name'] ?? 'N/A') ?></strong>
                                    <br><small class="text-muted"><?= esc($claim['patient_code'] ?? '') ?></small>
                                </td>
                                <td><?= esc($claim['insurance_provider'] ?? 'N/A') ?></td>
                                <td><strong>₱<?= number_format($claim['claim_amount'] ?? 0, 2) ?></strong></td>
                                <td>₱<?= number_format($claim['approved_amount'] ?? 0, 2) ?></td>
                                <td>₱<?= number_format($claim['deductible'] ?? 0, 2) ?></td>
                                <td>₱<?= number_format($claim['co_payment'] ?? 0, 2) ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $claim['status'] === 'approved' ? 'success' : 
                                        ($claim['status'] === 'rejected' ? 'danger' : 
                                        ($claim['status'] === 'paid' ? 'info' : 'warning'))
                                    ?>">
                                        <?= ucfirst(esc($claim['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= $claim['submitted_date'] ? date('M j, Y', strtotime($claim['submitted_date'])) : '—' ?></td>
                                <td>
                                    <button class="btn-xs btn-primary" onclick="viewClaim(<?= $claim['id'] ?>)">View</button>
                                    <?php if (in_array($claim['status'], ['pending', 'submitted'])): ?>
                                        <button class="btn-xs btn-success" onclick="updateClaim(<?= $claim['id'] ?>, 'approved')">Approve</button>
                                        <button class="btn-xs btn-danger" onclick="updateClaim(<?= $claim['id'] ?>, 'rejected')">Reject</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center-empty">No insurance claims found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Create Claim Modal -->
<div id="createClaimModal" class="modal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Create Insurance Claim</h3>
            <button class="modal-close" onclick="closeCreateClaimModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createClaimForm">
                <div class="form-group">
                    <label>Bill *</label>
                    <select name="bill_id" id="claim_bill_id" class="form-input" required onchange="loadBillDetails(this.value)">
                        <option value="">Select Bill</option>
                        <?php foreach ($unclaimed_bills ?? [] as $bill): ?>
                            <option value="<?= $bill['id'] ?>" data-balance="<?= $bill['balance'] ?>">
                                <?= esc($bill['bill_number']) ?> - <?= esc($bill['patient_name']) ?> (₱<?= number_format($bill['balance'], 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Insurance Provider *</label>
                    <input type="text" name="insurance_provider" id="claim_provider" class="form-input" placeholder="e.g., PhilHealth, Maxicare, etc." required>
                </div>
                <div class="form-group">
                    <label>Policy Number</label>
                    <input type="text" name="policy_number" id="claim_policy" class="form-input">
                </div>
                <div class="form-group">
                    <label>Member ID</label>
                    <input type="text" name="member_id" id="claim_member" class="form-input">
                </div>
                <div class="form-group">
                    <label>Claim Amount *</label>
                    <input type="number" name="claim_amount" id="claim_amount" class="form-input" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Deductible</label>
                    <input type="number" name="deductible" id="claim_deductible" class="form-input" value="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Co-Payment</label>
                    <input type="number" name="co_payment" id="claim_copay" class="form-input" value="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" id="claim_notes" class="form-textarea" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Claim</button>
                    <button type="button" class="btn-secondary" onclick="closeCreateClaimModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Claim Modal -->
<div id="updateClaimModal" class="modal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="updateClaimTitle">Update Insurance Claim</h3>
            <button class="modal-close" onclick="closeUpdateClaimModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="updateClaimForm">
                <input type="hidden" name="claim_id" id="update_claim_id">
                <input type="hidden" name="status" id="update_status">
                <div id="approvedFields" style="display: none;">
                    <div class="form-group">
                        <label>Approved Amount *</label>
                        <input type="number" name="approved_amount" id="update_approved_amount" class="form-input" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="auto_create_payment" id="auto_payment" checked>
                            Auto-create payment when approved
                        </label>
                    </div>
                </div>
                <div id="rejectedFields" style="display: none;">
                    <div class="form-group">
                        <label>Rejection Reason *</label>
                        <textarea name="rejection_reason" id="update_rejection_reason" class="form-textarea" rows="3" required></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Update Claim</button>
                    <button type="button" class="btn-secondary" onclick="closeUpdateClaimModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateClaimModal() {
    document.getElementById('createClaimModal').style.display = 'flex';
}

function closeCreateClaimModal() {
    document.getElementById('createClaimModal').style.display = 'none';
    document.getElementById('createClaimForm').reset();
}

function loadBillDetails(billId) {
    const select = document.getElementById('claim_bill_id');
    const option = select.options[select.selectedIndex];
    if (option && option.dataset.balance) {
        document.getElementById('claim_amount').value = option.dataset.balance;
    }
}

function updateClaim(claimId, status) {
    document.getElementById('update_claim_id').value = claimId;
    document.getElementById('update_status').value = status;
    document.getElementById('updateClaimTitle').textContent = status === 'approved' ? 'Approve Insurance Claim' : 'Reject Insurance Claim';
    document.getElementById('approvedFields').style.display = status === 'approved' ? 'block' : 'none';
    document.getElementById('rejectedFields').style.display = status === 'rejected' ? 'block' : 'none';
    document.getElementById('updateClaimModal').style.display = 'flex';
}

function closeUpdateClaimModal() {
    document.getElementById('updateClaimModal').style.display = 'none';
    document.getElementById('updateClaimForm').reset();
}

function viewClaim(claimId) {
    window.location.href = '/accounts/insurance?claim_id=' + claimId;
}

// Handle form submissions
document.getElementById('createClaimForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/accounts/createInsuranceClaim', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            alert('Insurance claim created successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error creating claim: ' + error.message);
    }
});

document.getElementById('updateClaimForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.auto_create_payment = document.getElementById('auto_payment').checked;
    
    try {
        const response = await fetch('/accounts/updateInsuranceClaim', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            alert('Insurance claim updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error updating claim: ' + error.message);
    }
});
</script>

<?= $this->endSection() ?>

