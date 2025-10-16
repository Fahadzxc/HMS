<!-- Admin dashboard partial (inner content only) -->
<section class="panel">
    <header class="panel-header">
        <h2>Admin Dashboard</h2>
        <p>Quick overview of today's key metrics and system status</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Patients Today</div>
                    <div class="kpi-value">24</div>
                    <div class="kpi-change kpi-positive">+3 from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Revenue Today</div>
                    <div class="kpi-value">₱45,250</div>
                    <div class="kpi-change kpi-positive">+12% from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Active Staff</div>
                    <div class="kpi-value">32</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">System Alerts</div>
                    <div class="kpi-value">2</div>
                    <div class="kpi-change kpi-negative">-1 from yesterday</div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="page-grid">
    <!-- Today's Tasks -->
    <section class="panel">
        <header class="panel-header">
            <h2>Today's Tasks</h2>
            <p>Items that need your attention</p>
        </header>
        <div class="stack">
            <div class="card">
                <div class="row between">
                    <h3>Appointment request</h3>
                    <span class="badge high">high</span>
                </div>
                <p>Assigned to: <strong>Dr. Santos</strong></p>
                <p>Status: Pending</p>
                <a href="#" class="link">Review</a>
            </div>
            <div class="card">
                <div class="row between">
                    <h3>Order supplies</h3>
                    <span class="badge medium">medium</span>
                </div>
                <p>Assigned to: <strong>Pharmacy Manager</strong></p>
                <p>Status: Pending</p>
                <a href="#" class="link">Review</a>
            </div>
            <div class="card">
                <div class="row between">
                    <h3>Review payroll</h3>
                    <span class="badge medium">medium</span>
                </div>
                <p>Assigned to: <strong>Accountant</strong></p>
                <p>Status: Pending</p>
                <a href="#" class="link">Review</a>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="panel">
        <header class="panel-header">
            <h2>Quick Actions</h2>
        </header>
        <div class="actions-grid">
            <a class="action-tile" href="#"><span class="icon icon-add"></span><span>Add Patient</span></a>
            <a class="action-tile" href="#"><span class="icon icon-schedule"></span><span>Schedule</span></a>
            <a class="action-tile" href="#"><span class="icon icon-reports"></span><span>Reports</span></a>
            <a class="action-tile" href="#"><span class="icon icon-settings"></span><span>Settings</span></a>
        </div>
    </section>
</div>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>System Overview</h2>
        <p>Current system status and recent activities</p>
    </header>
    <div class="stack">
        <div class="card">
            <div class="row between">
                <div>
                    <h3>System Status</h3>
                    <p>All systems operational</p>
                </div>
                <div class="row">
                    <span class="badge success">Online</span>
                </div>
            </div>
            <div class="status-list">
                <div class="status-row">
                    <span><span class="dot ok"></span>Database</span>
                    <span>Online</span>
                </div>
                <div class="status-row">
                    <span><span class="dot ok"></span>Backup System</span>
                    <span>Active</span>
                </div>
                <div class="status-row">
                    <span>Server Load</span>
                    <span>42%</span>
                </div>
                <div class="status-row">
                    <span>Storage Used</span>
                    <span>68%</span>
                </div>
            </div>
        </div>
    </div>
</section>
