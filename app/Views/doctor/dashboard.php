<!-- Doctor dashboard partial (inner content only) -->
<section class="panel">
    <header class="panel-header">
        <h2>Doctor Dashboard</h2>
        <p>Quick overview of today's appointments and patient care</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Today's Appointments</div>
                    <div class="kpi-value">12</div>
                    <div class="kpi-change kpi-positive">+2 from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Patients</div>
                    <div class="kpi-value">1,247</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending Reports</div>
                    <div class="kpi-value">8</div>
                    <div class="kpi-change kpi-negative">-1 from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Revenue This Month</div>
                    <div class="kpi-value">₱125,580</div>
                    <div class="kpi-change kpi-positive">+8% from last month</div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="page-grid">
    <section class="panel">
        <header class="panel-header">
            <h2>Today's Appointments</h2>
            <p>Your scheduled appointments for today</p>
        </header>
        <div class="stack">
            <div class="card">
                <div class="row between">
                    <div>
                        <h3>Sarah Johnson</h3>
                        <p>Regular Checkup</p>
                    </div>
                    <div class="row">
                        <span>09:00 AM</span>
                        <span class="badge low">completed</span>
                    </div>
                </div>
                <a class="link" href="#">View Details</a>
            </div>
            <div class="card">
                <div class="row between">
                    <div>
                        <h3>Michael Chen</h3>
                        <p>Follow-up</p>
                    </div>
                    <div class="row">
                        <span>10:30 AM</span>
                        <span class="badge medium">in progress</span>
                    </div>
                </div>
                <a class="link" href="#">View Details</a>
            </div>
            <div class="card">
                <div class="row between">
                    <div>
                        <h3>Emma Williams</h3>
                        <p>Consultation</p>
                    </div>
                    <div class="row">
                        <span>11:45 AM</span>
                        <span class="badge high">upcoming</span>
                    </div>
                </div>
                <a class="link" href="#">View Details</a>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel-header">
            <h2>Quick Actions</h2>
        </header>
        <div class="actions-grid">
            <a class="action-tile" href="#"><span class="icon"></span><span>New Appointment</span></a>
            <a class="action-tile" href="#"><span class="icon"></span><span>Write Prescription</span></a>
            <a class="action-tile" href="#"><span class="icon"></span><span>Lab Request</span></a>
            <a class="action-tile" href="#"><span class="icon"></span><span>Patient Report</span></a>
        </div>
    </section>
</div>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Patient Care Overview</h2>
        <p>Your current patient care activities and statistics</p>
    </header>
    <div class="stack">
        <div class="card">
            <div class="row between">
                <div>
                    <h3>This Week's Consultations</h3>
                    <p>Summary of your weekly activities</p>
                </div>
                <div class="row">
                    <span class="badge success">Active</span>
                </div>
            </div>
            <ul class="list">
                <li class="list-item info"><span class="dot"></span>Regular Checkups <strong>28</strong></li>
                <li class="list-item success"><span class="dot"></span>Follow-up Visits <strong>15</strong></li>
                <li class="list-item warn"><span class="dot"></span>Emergency Cases <strong>6</strong></li>
                <li class="list-item"><span class="dot"></span>Consultations <strong>22</strong></li>
            </ul>
        </div>
    </div>
</section>
