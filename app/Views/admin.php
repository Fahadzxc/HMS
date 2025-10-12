<!-- Admin dashboard partial (inner content only) -->
<!-- Dashboard KPI Cards -->
<section class="panel">
    <header class="panel-header">
        <h2>Dashboard Overview</h2>
        <p>Today's key metrics and statistics</p>
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
                    <div class="kpi-value">$4,320</div>
                    <div class="kpi-change kpi-positive">+12% from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Appointments Scheduled</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Low Stock Items</div>
                    <div class="kpi-value">5</div>
                    <div class="kpi-change kpi-negative">-1 from yesterday</div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="page-grid">
    <!-- Pending Approvals/Tasks -->
    <section class="panel">
        <header class="panel-header">
            <h2>Pending Approvals/Tasks</h2>
            <p>Items that need your attention</p>
        </header>
        <div class="stack">
            <article class="card">
                <div class="row between">
                    <h3>Appointment request</h3>
                    <span class="badge high">high</span>
                </div>
                <p>Assigned to: <strong>Alice Smith</strong></p>
                <p>Status: Pending</p>
                <a href="#" class="link">Review</a>
            </article>

            <article class="card">
                <div class="row between">
                    <h3>Order supplies</h3>
                    <span class="badge medium">medium</span>
                </div>
                <p>Assigned to: <strong>David Johnson</strong></p>
                <p>Status: Pending</p>
                <a href="#" class="link">Review</a>
            </article>

            <article class="card">
                <div class="row between">
                    <h3>Review payroll</h3>
                    <span class="badge medium">medium</span>
                </div>
                <p>Assigned to: <strong>Elizabeth Brown</strong></p>
                <p>Status: Pending</p>
                <a href="#" class="link">Review</a>
            </article>

            <article class="card">
                <div class="row between">
                    <h3>System update</h3>
                    <span class="badge low">low</span>
                </div>
                <p>Assigned to: <strong>Robert Wilson</strong></p>
                <p>Status: Pending</p>
                <a href="#" class="link">Review</a>
            </article>
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

<div class="page-grid">
    <!-- Recent Activities -->
    <section class="panel">
        <header class="panel-header">
            <h2>Recent Activities</h2>
        </header>
        <ul class="list">
            <li class="list-item info">
                <span class="dot"></span>New patient registered - John Smith - 10 minutes ago
            </li>
            <li class="list-item warn">
                <span class="dot"></span>Lab results updated - Michael Brown - 1 hour ago
            </li>
        </ul>
    </section>

    <!-- System Status -->
    <section class="panel">
        <header class="panel-header">
            <h2>System Status</h2>
        </header>
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
    </section>

    <!-- Today's Summary -->
    <section class="panel">
        <header class="panel-header">
            <h2>Today's Summary</h2>
        </header>
        <ul class="list">
            <li class="list-item">
                <span class="icon icon-patients"></span>Patients Seen: <strong>24</strong>
            </li>
            <li class="list-item">
                <span class="icon icon-appointments"></span>Appointments: <strong>0</strong>
            </li>
            <li class="list-item">
                <span class="icon icon-lab"></span>Lab Tests: <strong>12</strong>
            </li>
        </ul>
    </section>
</div>


