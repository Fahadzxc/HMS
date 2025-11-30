<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>💰</span>
                    Billing & Payments
                </h2>
                <p class="page-subtitle">
                    Manage invoices, payments, and financial records
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- KPI Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-value">₱<?= number_format($total_revenue ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">All time</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Invoices</div>
                <div class="kpi-value">₱<?= number_format($pending_amount ?? 0, 2) ?></div>
                <div class="kpi-change kpi-warning">Awaiting payment</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Overdue Payments</div>
                <div class="kpi-value">₱<?= number_format($overdue_amount ?? 0, 2) ?></div>
                <div class="kpi-change kpi-negative">Requires attention</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">This Month</div>
                <div class="kpi-value">₱<?= number_format($this_month_revenue ?? 0, 2) ?></div>
                <div class="kpi-change kpi-positive">Current month</div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Invoices -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Invoices</h2>
        <div class="row">
            <button class="btn-secondary" onclick="createBillsForCompleted()">Create Bills for Completed Prescriptions</button>
            <button class="btn-secondary" onclick="window.print()">Export</button>
        </div>
    </header>
        
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Patient</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><strong><?= esc($bill['bill_number'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <strong><?= esc($bill['patient_name'] ?? 'N/A') ?></strong>
                                    <?php if (!empty($bill['contact'])): ?>
                                        <p class="text-muted" style="margin: 0; font-size: 0.875rem;"><?= esc($bill['contact']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td><?= ucfirst(esc($bill['bill_type'] ?? 'N/A')) ?></td>
                                <td><strong>₱<?= number_format($bill['total_amount'] ?? 0, 2) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $bill['status'] === 'paid' ? 'success' : 
                                        ($bill['status'] === 'overdue' ? 'danger' : 
                                        ($bill['status'] === 'partial' ? 'warning' : 'warning'))
                                    ?>">
                                        <?= strtoupper(esc($bill['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= $bill['created_at'] ? date('M j, Y', strtotime($bill['created_at'])) : '—' ?></td>
                                <td><?= !empty($bill['payment_method']) ? ucfirst(str_replace('_', ' ', esc($bill['payment_method']))) : '—' ?></td>
                                <td>
                                    <button class="btn-xs btn-primary" onclick="viewBill(<?= $bill['id'] ?>)">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center-empty">No invoices found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- View Bill Modal -->
<div id="viewBillModal" class="modal" style="display: none;" onclick="if(event.target === this) closeViewBillModal()">
    <div class="modal-dialog view-bill-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Bill Details</h3>
            <button class="modal-close" onclick="closeViewBillModal()" type="button">&times;</button>
        </div>
        <div class="modal-body">
            <div id="viewBillContent">
                <div class="text-center-empty" style="padding: 2rem;">
                    <p>Loading bill details...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeViewBillModal()">Close</button>
        </div>
    </div>
</div>

<script>
async function createBillsForCompleted() {
    if (!confirm('Create bills for all completed prescriptions that don\'t have bills yet?')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/billing/createBillsForCompleted', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function viewBill(billId) {
    try {
        const modal = document.getElementById('viewBillModal');
        const content = document.getElementById('viewBillContent');
        
        // Show modal with loading state
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        content.innerHTML = '<div class="text-center-empty" style="padding: 2rem;"><p>Loading bill details...</p></div>';
        
        // Fetch bill details
        const response = await fetch(`<?= base_url('accounts/getBillDetails') ?>/${billId}`);
        const result = await response.json();
        
        if (result.success && result.bill) {
            const bill = result.bill;
            const patient = bill.patient || {};
            const items = bill.items || [];
            const payments = bill.payments || [];
            
            // Format bill details HTML
            let html = `
                <div class="view-bill-section">
                    <h4>Bill Information</h4>
                    <div class="view-bill-grid">
                        <div class="view-bill-info">
                            <span class="view-bill-label">Bill Number:</span>
                            <span class="view-bill-value"><strong>${bill.bill_number || 'N/A'}</strong></span>
                        </div>
                        <div class="view-bill-info">
                            <span class="view-bill-label">Status:</span>
                            <span class="view-bill-value">
                                <span class="badge badge-${bill.status === 'paid' ? 'success' : (bill.status === 'overdue' ? 'danger' : 'warning')}">
                                    ${(bill.status || 'pending').toUpperCase()}
                                </span>
                            </span>
                        </div>
                        <div class="view-bill-info">
                            <span class="view-bill-label">Bill Date:</span>
                            <span class="view-bill-value">${bill.created_at ? new Date(bill.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'}</span>
                        </div>
                        <div class="view-bill-info">
                            <span class="view-bill-label">Due Date:</span>
                            <span class="view-bill-value">${bill.due_date ? new Date(bill.due_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'}</span>
                        </div>
                    </div>
                </div>
                
                <div class="view-bill-section">
                    <h4>Patient Information</h4>
                    <div class="view-bill-grid">
                        <div class="view-bill-info">
                            <span class="view-bill-label">Patient Name:</span>
                            <span class="view-bill-value"><strong>${patient.full_name || patient.name || 'N/A'}</strong></span>
                        </div>
                        <div class="view-bill-info">
                            <span class="view-bill-label">Patient ID:</span>
                            <span class="view-bill-value">${patient.patient_id || 'N/A'}</span>
                        </div>
                        ${patient.phone ? `
                        <div class="view-bill-info">
                            <span class="view-bill-label">Contact:</span>
                            <span class="view-bill-value">${patient.phone}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="view-bill-section">
                    <h4>Bill Items</h4>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Code</th>
                                    <th>Particulars</th>
                                    <th>Rate (₱)</th>
                                    <th>Units</th>
                                    <th>Amount (₱)</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (items.length > 0) {
                items.forEach(item => {
                    // Extract code from description if available, or generate based on item type
                    let itemCode = 'N/A';
                    if (item.description && item.description.includes('Code:')) {
                        itemCode = item.description.split('Code:')[1]?.trim() || 'N/A';
                    } else if (item.reference_id) {
                        // Generate code based on item type
                        const typePrefix = item.item_type === 'professional' ? 'PROF' : 
                                         item.item_type === 'medication' ? 'MED' : 
                                         item.item_type === 'laboratory' ? 'LAB' : 'SRV';
                        itemCode = `${typePrefix}-${String(item.reference_id).padStart(6, '0')}`;
                    }
                    
                    // Use item_type as category, capitalize it
                    const category = item.item_type ? item.item_type.charAt(0).toUpperCase() + item.item_type.slice(1) : 'N/A';
                    
                    html += `
                        <tr>
                            <td>${category}</td>
                            <td>${itemCode}</td>
                            <td>${item.item_name || 'N/A'}</td>
                            <td>₱${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                            <td>${item.quantity || 0}</td>
                            <td><strong>₱${parseFloat(item.total_price || 0).toFixed(2)}</strong></td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="6" class="text-center-empty">No items found</td></tr>';
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="view-bill-section">
                    <h4>Summary</h4>
                    <div class="view-bill-summary">
                        <div class="view-bill-summary-row">
                            <span class="view-bill-summary-label">Subtotal:</span>
                            <span class="view-bill-summary-value">₱${parseFloat(bill.subtotal || 0).toFixed(2)}</span>
                        </div>
                        <div class="view-bill-summary-row">
                            <span class="view-bill-summary-label">Discount:</span>
                            <span class="view-bill-summary-value">₱${parseFloat(bill.discount || 0).toFixed(2)}</span>
                        </div>
                        <div class="view-bill-summary-row view-bill-total">
                            <span class="view-bill-summary-label"><strong>Total Amount:</strong></span>
                            <span class="view-bill-summary-value"><strong>₱${parseFloat(bill.total_amount || 0).toFixed(2)}</strong></span>
                        </div>
                        <div class="view-bill-summary-row">
                            <span class="view-bill-summary-label">Paid Amount:</span>
                            <span class="view-bill-summary-value">₱${parseFloat(bill.paid_amount || 0).toFixed(2)}</span>
                        </div>
                        <div class="view-bill-summary-row">
                            <span class="view-bill-summary-label">Balance:</span>
                            <span class="view-bill-summary-value"><strong>₱${parseFloat(bill.balance || 0).toFixed(2)}</strong></span>
                        </div>
                    </div>
                </div>
            `;
            
            if (payments.length > 0) {
                html += `
                    <div class="view-bill-section">
                        <h4>Payment History</h4>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Amount (₱)</th>
                                        <th>Method</th>
                                        <th>Transaction ID</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;
                
                payments.forEach(payment => {
                    html += `
                        <tr>
                            <td>${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'}</td>
                            <td><strong>₱${parseFloat(payment.amount || 0).toFixed(2)}</strong></td>
                            <td>${(payment.payment_method || 'N/A').toUpperCase()}</td>
                            <td>${payment.transaction_id || 'N/A'}</td>
                            <td>
                                <span class="badge badge-${payment.status === 'completed' ? 'success' : 'warning'}">
                                    ${(payment.status || 'pending').toUpperCase()}
                                </span>
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }
            
            if (bill.notes) {
                html += `
                    <div class="view-bill-section">
                        <h4>Notes</h4>
                        <p style="color: #64748b; padding: 0.75rem; background: #f8fafc; border-radius: 6px;">${bill.notes}</p>
                    </div>
                `;
            }
            
            content.innerHTML = html;
        } else {
            content.innerHTML = '<div class="text-center-empty" style="padding: 2rem;"><p style="color: #dc2626;">Error: Could not load bill details</p></div>';
        }
    } catch (error) {
        console.error('Error loading bill:', error);
        document.getElementById('viewBillContent').innerHTML = '<div class="text-center-empty" style="padding: 2rem;"><p style="color: #dc2626;">Error loading bill details. Please try again.</p></div>';
    }
}

function closeViewBillModal() {
    document.getElementById('viewBillModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('viewBillContent').innerHTML = '<div class="text-center-empty" style="padding: 2rem;"><p>Loading bill details...</p></div>';
}
</script>

<?= $this->endSection() ?>
