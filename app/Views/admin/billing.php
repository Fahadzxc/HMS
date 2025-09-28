<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Billing & Payments</h2>
        <p>Manage invoices, payments, and financial records</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Revenue</div>
                    <div class="kpi-value">$125,430</div>
                    <div class="kpi-change kpi-positive">+12.5% from last month</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending Invoices</div>
                    <div class="kpi-value">$8,250</div>
                    <div class="kpi-change kpi-warning">+5.2% from last month</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Overdue Payments</div>
                    <div class="kpi-value">$3,120</div>
                    <div class="kpi-change kpi-negative">-8.1% from last month</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">This Month</div>
                    <div class="kpi-value">$42,680</div>
                    <div class="kpi-change kpi-positive">+15.3% from last month</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Invoices -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Recent Invoices</h2>
        <div class="row">
            <button class="btn-secondary">Filter</button>
            <button class="btn-secondary">Export</button>
        </div>
    </header>
        
        <div class="stack">
            <!-- Table Header -->
            <div class="card table-header">
                <div class="row between">
                    <div class="col-id">Invoice ID</div>
                    <div class="col-name">Patient</div>
                    <div class="col-amount">Amount</div>
                    <div class="col-status">Status</div>
                    <div class="col-date">Date</div>
                    <div class="col-payment">Payment</div>
                    <div class="col-actions">Actions</div>
                </div>
            </div>

            <!-- Invoice Rows -->
            <div class="card table-row">
                <div class="row between">
                    <div class="col-id invoice-id">INV-2024-001</div>
                    <div class="col-name">
                        <strong>Sarah Johnson</strong>
                        <p class="phone">+1 (555) 123-4567</p>
                    </div>
                    <div class="col-amount">$450.00</div>
                    <div class="col-status"><span class="badge low">Paid</span></div>
                    <div class="col-date">1/15/2024</div>
                    <div class="col-payment">Credit Card</div>
                    <div class="col-actions">
                        <a href="#" class="action-link">View</a>
                        <a href="#" class="action-link">Download</a>
                        <a href="#" class="action-link">More</a>
                    </div>
                </div>
            </div>

            <div class="card table-row">
                <div class="row between">
                    <div class="col-id invoice-id">INV-2024-002</div>
                    <div class="col-name">
                        <strong>Michael Brown</strong>
                        <p class="phone">+1 (555) 234-5678</p>
                    </div>
                    <div class="col-amount">$325.50</div>
                    <div class="col-status"><span class="badge medium">Pending</span></div>
                    <div class="col-date">1/14/2024</div>
                    <div class="col-payment">Insurance</div>
                    <div class="col-actions">
                        <a href="#" class="action-link">View</a>
                        <a href="#" class="action-link">Download</a>
                        <a href="#" class="action-link">More</a>
                    </div>
                </div>
            </div>

            <div class="card table-row">
                <div class="row between">
                    <div class="col-id invoice-id">INV-2024-003</div>
                    <div class="col-name">
                        <strong>Emily Davis</strong>
                        <p class="phone">+1 (555) 345-6789</p>
                    </div>
                    <div class="col-amount">$180.00</div>
                    <div class="col-status"><span class="badge high">Overdue</span></div>
                    <div class="col-date">1/10/2024</div>
                    <div class="col-payment">Cash</div>
                    <div class="col-actions">
                        <a href="#" class="action-link">View</a>
                        <a href="#" class="action-link">Download</a>
                        <a href="#" class="action-link">More</a>
                    </div>
                </div>
            </div>

            <div class="card table-row">
                <div class="row between">
                    <div class="col-id invoice-id">INV-2024-004</div>
                    <div class="col-name">
                        <strong>David Wilson</strong>
                        <p class="phone">+1 (555) 456-7890</p>
                    </div>
                    <div class="col-amount">$520.75</div>
                    <div class="col-status"><span class="badge low">Paid</span></div>
                    <div class="col-date">1/12/2024</div>
                    <div class="col-payment">Insurance</div>
                    <div class="col-actions">
                        <a href="#" class="action-link">View</a>
                        <a href="#" class="action-link">Download</a>
                        <a href="#" class="action-link">More</a>
                    </div>
                </div>
            </div>

            <div class="card table-row">
                <div class="row between">
                    <div class="col-id invoice-id">INV-2024-005</div>
                    <div class="col-name">
                        <strong>Lisa Anderson</strong>
                        <p class="phone">+1 (555) 567-8901</p>
                    </div>
                    <div class="col-amount">$295.00</div>
                    <div class="col-status"><span class="badge medium">Pending</span></div>
                    <div class="col-date">1/13/2024</div>
                    <div class="col-payment">Credit Card</div>
                    <div class="col-actions">
                        <a href="#" class="action-link">View</a>
                        <a href="#" class="action-link">Download</a>
                        <a href="#" class="action-link">More</a>
                    </div>
                </div>
            </div>
        </div>
</section>

<?= $this->endSection() ?>
