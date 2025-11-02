<!-- Receptionist dashboard partial (inner content only) -->
<section class="panel">
    <header class="panel-header">
        <h2>Reception Dashboard</h2>
        <p>Quick overview of today's patient flow and appointments</p>
    </header>
    <div class="stack">
        <div class="card receptionist-card">
            <div class="row between">
                <div>
                    <h3><?= esc($user_name ?? 'Logged-in User') ?></h3>
                    <p><?= esc($user_email ?? '') ?></p>
                </div>
                <?php if (!empty($receptionistProfile)): ?>
                <div class="receptionist-meta">
                    <?php if (!empty($receptionistProfile['employee_id'])): ?>
                        <div><strong>Employee ID:</strong> <?= esc($receptionistProfile['employee_id']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($receptionistProfile['department'])): ?>
                        <div><strong>Department:</strong> <?= esc($receptionistProfile['department']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($receptionistProfile['shift'])): ?>
                        <div><strong>Shift:</strong> <?= esc(ucfirst($receptionistProfile['shift'])) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">New Patients Today</div>
                    <div class="kpi-value">8</div>
                    <div class="kpi-change kpi-positive">+2 from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Appointments</div>
                    <div class="kpi-value">24</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Walk-ins</div>
                    <div class="kpi-value">12</div>
                    <div class="kpi-change kpi-positive">+3 from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Discharged</div>
                    <div class="kpi-value">6</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="page-grid">
    <section class="panel">
        <header class="panel-header">
            <h2>Today's Tasks</h2>
            <p>Your assigned tasks for today</p>
        </header>
        <div class="stack">
            <div class="card">
                <div class="row between">
                    <h3>Patient Registration</h3>
                    <span class="badge medium">pending</span>
                </div>
                <p>New patient walk-ins waiting</p>
                <a href="#" class="link">Process</a>
            </div>
            <div class="card">
                <div class="row between">
                    <h3>Appointment Confirmations</h3>
                    <span class="badge high">urgent</span>
                </div>
                <p>5 appointments need confirmation</p>
                <a href="#" class="link">Review</a>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel-header">
            <h2>Quick Actions</h2>
        </header>
        <div class="actions-grid">
            <a class="action-tile" href="#"><span class="icon"></span><span>Register Patient</span></a>
            <a class="action-tile" href="#"><span class="icon"></span><span>Book Appointment</span></a>
            <a class="action-tile" href="#"><span class="icon"></span><span>Patient Check-in</span></a>
            <a class="action-tile" href="#"><span class="icon"></span><span>Process Billing</span></a>
        </div>
    </section>
</div>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Patient Flow Overview</h2>
        <p>Current patient flow and appointment status</p>
    </header>
    <div class="stack">
        <div class="card">
            <div class="row between">
                <div>
                    <h3>Today's Appointments</h3>
                    <p>24 appointments scheduled</p>
                </div>
                <div class="row">
                    <span class="badge success">Active</span>
                </div>
            </div>
            <div class="status-list">
                <div class="status-row">
                    <span><span class="dot ok"></span>Confirmed</span>
                    <span>18</span>
                </div>
                <div class="status-row">
                    <span><span class="dot warn"></span>Pending</span>
                    <span>5</span>
                </div>
                <div class="status-row">
                    <span><span class="dot error"></span>Cancelled</span>
                    <span>1</span>
                </div>
            </div>
        </div>
    </div>
</section>
