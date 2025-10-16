<!-- Pharmacist dashboard partial (inner content only) -->
<section class="panel">
    <header class="panel-header">
        <h2>Pharmacy Dashboard</h2>
        <p>Welcome back, <?= session()->get('name') ?>. Here's your pharmacy overview for today.</p>
    </header>
    <div class="stack">
        <div class="actions-grid">
            <div class="action-tile">
                <span>Pending Prescriptions</span>
                <strong>12</strong>
            </div>
            <div class="action-tile">
                <span>Dispensed Today</span>
                <strong>18</strong>
            </div>
            <div class="action-tile">
                <span>Low Stock Items</span>
                <strong>5</strong>
            </div>
            <div class="action-tile">
                <span>Expiring Soon</span>
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
            <a href="/pharmacy/prescriptions" class="button button-primary">View Prescriptions</a>
            <a href="/pharmacy/inventory" class="button button-secondary">Inventory Management</a>
            <a href="/pharmacy/dispense" class="button button-secondary">Dispense Medicine</a>
            <a href="/pharmacy/orders" class="button button-secondary">Place Orders</a>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Pending Prescriptions</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Medication</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>RX001234</td>
                        <td>Juan Dela Cruz</td>
                        <td>Dr. Santos</td>
                        <td>Paracetamol 500mg</td>
                        <td>30 tablets</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td><a href="#" class="button button-small">Dispense</a></td>
                    </tr>
                    <tr>
                        <td>RX001235</td>
                        <td>Maria Garcia</td>
                        <td>Dr. Lopez</td>
                        <td>Amoxicillin 250mg</td>
                        <td>21 capsules</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td><a href="#" class="button button-small">Dispense</a></td>
                    </tr>
                    <tr>
                        <td>RX001236</td>
                        <td>Pedro Rodriguez</td>
                        <td>Dr. Santos</td>
                        <td>Ibuprofen 400mg</td>
                        <td>20 tablets</td>
                        <td><span class="badge badge-success">Ready</span></td>
                        <td><a href="#" class="button button-small">Dispense</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Low Stock Alert</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Current Stock</th>
                        <th>Minimum Level</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Paracetamol 500mg</td>
                        <td>15</td>
                        <td>50</td>
                        <td><span class="badge badge-danger">Low</span></td>
                        <td><a href="#" class="button button-small">Order</a></td>
                    </tr>
                    <tr>
                        <td>Amoxicillin 250mg</td>
                        <td>8</td>
                        <td>30</td>
                        <td><span class="badge badge-danger">Critical</span></td>
                        <td><a href="#" class="button button-small">Order</a></td>
                    </tr>
                    <tr>
                        <td>Ibuprofen 400mg</td>
                        <td>25</td>
                        <td>40</td>
                        <td><span class="badge badge-warning">Low</span></td>
                        <td><a href="#" class="button button-small">Order</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
