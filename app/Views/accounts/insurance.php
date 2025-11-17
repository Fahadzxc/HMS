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
                    <select name="insurance_provider" id="claim_provider" class="form-input" required onchange="updateProviderDefaults()">
                        <option value="">Select Insurance Provider</option>
                        <option value="PhilHealth">PhilHealth</option>
                        <option value="Maxicare">Maxicare</option>
                        <option value="Medicard">Medicard</option>
                        <option value="Intellicare">Intellicare</option>
                        <option value="Cocolife">Cocolife</option>
                        <option value="Pacific Cross">Pacific Cross</option>
                        <option value="Aetna">Aetna</option>
                        <option value="Blue Cross">Blue Cross</option>
                        <option value="Caritas Health Shield">Caritas Health Shield</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="text" name="insurance_provider_other" id="claim_provider_other" class="form-input" placeholder="Specify other provider" style="display: none; margin-top: 0.5rem;">
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
    // Hide "Other" provider input
    document.getElementById('claim_provider_other').style.display = 'none';
    document.getElementById('claim_provider_other').value = '';
}

async function loadBillDetails(billId) {
    if (!billId) {
        // Clear all fields if no bill selected
        document.getElementById('claim_amount').value = '';
        document.getElementById('claim_deductible').value = '0';
        document.getElementById('claim_copay').value = '0';
        document.getElementById('claim_policy').value = '';
        document.getElementById('claim_member').value = '';
        return;
    }
    
    const select = document.getElementById('claim_bill_id');
    const option = select.options[select.selectedIndex];
    if (option && option.dataset.balance) {
        // Auto-populate claim amount with bill balance
        const balance = parseFloat(option.dataset.balance) || 0;
        document.getElementById('claim_amount').value = balance.toFixed(2);
        
        // Get patient insurance info
        try {
            const response = await fetch(`<?= base_url('accounts/getPatientInsuranceInfo') ?>/${billId}`);
            const result = await response.json();
            
            if (result.success && result.insurance_info) {
                const info = result.insurance_info;
                
                // Auto-populate policy number and member ID if available
                if (info.policy_number) {
                    document.getElementById('claim_policy').value = info.policy_number;
                }
                if (info.member_id) {
                    document.getElementById('claim_member').value = info.member_id;
                }
                
                // If patient has previous claims with a provider, suggest it
                if (info.last_provider) {
                    const providerSelect = document.getElementById('claim_provider');
                    // Check if the provider exists in the dropdown
                    for (let i = 0; i < providerSelect.options.length; i++) {
                        if (providerSelect.options[i].value === info.last_provider) {
                            providerSelect.value = info.last_provider;
                            break;
                        }
                    }
                }
            }
        } catch (error) {
            console.log('Could not load patient insurance info:', error);
            // Continue without insurance info - user can fill manually
        }
        
        // Auto-calculate deductible and co-payment based on provider
        updateProviderDefaults();
    }
}

function updateProviderDefaults() {
    const provider = document.getElementById('claim_provider').value;
    const claimAmount = parseFloat(document.getElementById('claim_amount').value) || 0;
    
    // Show/hide "Other" provider input
    const otherInput = document.getElementById('claim_provider_other');
    if (provider === 'Other') {
        otherInput.style.display = 'block';
        otherInput.required = true;
    } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
    
    // Auto-generate Policy Number and Member ID if empty
    if (provider && provider !== 'Other' && provider !== '') {
        const policyInput = document.getElementById('claim_policy');
        const memberInput = document.getElementById('claim_member');
        
        // Only auto-generate if fields are empty
        if (!policyInput.value.trim()) {
            // Generate policy number based on provider and date
            const year = new Date().getFullYear();
            const month = String(new Date().getMonth() + 1).padStart(2, '0');
            const providerCode = provider.substring(0, 3).toUpperCase();
            policyInput.value = `${providerCode}-${year}${month}-${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}`;
        }
        
        if (!memberInput.value.trim()) {
            // Generate member ID based on provider
            const providerCode = provider.substring(0, 2).toUpperCase();
            const randomNum = Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
            memberInput.value = `${providerCode}${randomNum}`;
        }
    }
    
    // Auto-set deductible and co-payment based on provider
    let deductible = 0;
    let copay = 0;
    
    switch(provider) {
        case 'PhilHealth':
            // PhilHealth typically covers 80-90%, patient pays 10-20%
            deductible = 0;
            copay = (claimAmount * 0.10).toFixed(2); // 10% co-payment
            break;
        case 'Maxicare':
            // Maxicare usually has minimal deductible
            deductible = 0;
            copay = (claimAmount * 0.05).toFixed(2); // 5% co-payment
            break;
        case 'Medicard':
            deductible = 0;
            copay = (claimAmount * 0.05).toFixed(2);
            break;
        case 'Intellicare':
            deductible = 0;
            copay = (claimAmount * 0.05).toFixed(2);
            break;
        case 'Cocolife':
            deductible = 0;
            copay = (claimAmount * 0.10).toFixed(2);
            break;
        case 'Pacific Cross':
            deductible = 0;
            copay = (claimAmount * 0.10).toFixed(2);
            break;
        case 'Aetna':
            deductible = (claimAmount * 0.05).toFixed(2); // 5% deductible
            copay = (claimAmount * 0.10).toFixed(2);
            break;
        case 'Blue Cross':
            deductible = 0;
            copay = (claimAmount * 0.15).toFixed(2);
            break;
        case 'Caritas Health Shield':
            deductible = 0;
            copay = (claimAmount * 0.10).toFixed(2);
            break;
        default:
            deductible = 0;
            copay = 0;
    }
    
    document.getElementById('claim_deductible').value = deductible;
    document.getElementById('claim_copay').value = copay;
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
    
    // Handle "Other" provider
    const provider = document.getElementById('claim_provider').value;
    if (provider === 'Other') {
        const otherProvider = document.getElementById('claim_provider_other').value.trim();
        if (!otherProvider) {
            alert('Please specify the insurance provider name.');
            return;
        }
        data.insurance_provider = otherProvider;
    }
    
    // Convert numeric fields
    data.claim_amount = parseFloat(data.claim_amount) || 0;
    data.deductible = parseFloat(data.deductible) || 0;
    data.co_payment = parseFloat(data.co_payment) || 0;
    
    // Disable submit button
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
    }
    
    try {
        console.log('Sending data:', data);
        const response = await fetch('<?= base_url('accounts/createInsuranceClaim') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            throw new Error('Server returned non-JSON response. Please check server logs.');
        }
        
        const result = await response.json();
        console.log('Result:', result);
        
        if (result.success) {
            alert('Insurance claim created successfully!');
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Claim';
            }
        }
    } catch (error) {
        console.error('Error creating claim:', error);
        alert('Error creating claim: ' + error.message);
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Claim';
        }
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

