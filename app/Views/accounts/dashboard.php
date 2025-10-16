<!-- Accountant dashboard partial (inner content only) -->
<section class="panel">
    <header class="panel-header">
        <h2>Accounts Dashboard</h2>
        <p>Welcome back, <?= session()->get('name') ?>. Here's your financial overview for today.</p>
    </header>
    <div class="stack">
        <div class="actions-grid">
            <div class="action-tile">
                <span>Today's Revenue</span>
                <strong>₱45,250</strong>
            </div>
            <div class="action-tile">
                <span>Pending Bills</span>
                <strong>12</strong>
            </div>
            <div class="action-tile">
                <span>Insurance Claims</span>
                <strong>8</strong>
            </div>
            <div class="action-tile">
                <span>Overdue Payments</span>
                <strong>3</strong>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Quick Actions</h3>
    </header>
    <div class="stack">
        <div class="button-group">
            <a href="/accounts/billing" class="button button-primary">Process Billing</a>
            <a href="/accounts/payments" class="button button-secondary">Record Payments</a>
            <a href="/accounts/insurance" class="button button-secondary">Insurance Claims</a>
            <a href="/accounts/reports" class="button button-secondary">Financial Reports</a>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Pending Bills</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Bill ID</th>
                        <th>Patient</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>B001234</td>
                        <td>Juan Dela Cruz</td>
                        <td>Consultation + Lab</td>
                        <td>₱2,500</td>
                        <td>Today</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td><a href="#" class="button button-small">Process</a></td>
                    </tr>
                    <tr>
                        <td>B001235</td>
                        <td>Maria Garcia</td>
                        <td>Emergency Room</td>
                        <td>₱8,750</td>
                        <td>Tomorrow</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td><a href="#" class="button button-small">Process</a></td>
                    </tr>
                    <tr>
                        <td>B001236</td>
                        <td>Pedro Rodriguez</td>
                        <td>Surgery</td>
                        <td>₱25,000</td>
                        <td>Overdue</td>
                        <td><span class="badge badge-danger">Overdue</span></td>
                        <td><a href="#" class="button button-small">Follow-up</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Recent Payments</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Patient</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>P001234</td>
                        <td>Ana Martinez</td>
                        <td>₱1,500</td>
                        <td>Cash</td>
                        <td><span class="badge badge-success">Paid</span></td>
                        <td>09:30 AM</td>
                    </tr>
                    <tr>
                        <td>P001235</td>
                        <td>Carlos Reyes</td>
                        <td>₱3,200</td>
                        <td>Credit Card</td>
                        <td><span class="badge badge-success">Paid</span></td>
                        <td>09:15 AM</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Insurance Claims</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Claim ID</th>
                        <th>Patient</th>
                        <th>Insurance</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>C001234</td>
                        <td>Juan Dela Cruz</td>
                        <td>PhilHealth</td>
                        <td>₱5,000</td>
                        <td><span class="badge badge-warning">Processing</span></td>
                        <td><a href="#" class="button button-small">Update</a></td>
                    </tr>
                    <tr>
                        <td>C001235</td>
                        <td>Maria Garcia</td>
                        <td>Maxicare</td>
                        <td>₱8,500</td>
                        <td><span class="badge badge-success">Approved</span></td>
                        <td><a href="#" class="button button-small">View</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
