<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container">
    <!-- Welcome Section -->
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">👨‍⚕️ Doctor Dashboard</h1>
            <p class="card-subtitle">Welcome back, Dr. <?= session()->get('name') ?>!</p>
        </div>
    </div>

    <!-- Doctor Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">25</div>
            <div class="stat-label">Today's Appointments</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">8</div>
            <div class="stat-label">Active Patients</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">3</div>
            <div class="stat-label">Emergency Cases</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">12</div>
            <div class="stat-label">Completed Today</div>
        </div>
    </div>

    <!-- Doctor Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Doctor Actions</h2>
            <p class="card-subtitle">Manage your daily medical practice</p>
        </div>

        <div class="quick-actions">
            <a href="#" class="action-btn">
                <div class="action-icon">📅</div>
                <div class="action-text">View Schedule</div>
            </a>
            <a href="#" class="action-btn">
                <div class="action-icon">👥</div>
                <div class="action-text">My Patients</div>
            </a>
            <a href="#" class="action-btn">
                <div class="action-icon">📋</div>
                <div class="action-text">Medical Records</div>
            </a>
            <a href="#" class="action-btn">
                <div class="action-icon">💊</div>
                <div class="action-text">Prescriptions</div>
            </a>
        </div>
    </div>

    <!-- Today's Schedule -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Today's Schedule</h2>
            <p class="card-subtitle">Your upcoming appointments</p>
        </div>

        <div style="padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #eee;">
                <div>
                    <strong>9:00 AM</strong> - John Smith
                    <p style="color: #666; margin: 0;">Follow-up consultation</p>
                </div>
                <span style="color: #27ae60;">Confirmed</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #eee;">
                <div>
                    <strong>10:30 AM</strong> - Maria Garcia
                    <p style="color: #666; margin: 0;">Initial consultation</p>
                </div>
                <span style="color: #f39c12;">Pending</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #eee;">
                <div>
                    <strong>2:00 PM</strong> - Robert Johnson
                    <p style="color: #666; margin: 0;">Annual checkup</p>
                </div>
                <span style="color: #27ae60;">Confirmed</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                <div>
                    <strong>4:15 PM</strong> - Sarah Wilson
                    <p style="color: #666; margin: 0;">Emergency consultation</p>
                </div>
                <span style="color: #e74c3c;">Emergency</span>
            </div>
        </div>
    </div>

    <!-- Recent Patients -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Patients</h2>
            <p class="card-subtitle">Your recently attended patients</p>
        </div>

        <div style="padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #eee;">
                <div>
                    <strong>Emily Davis</strong>
                    <p style="color: #666; margin: 0;">Blood pressure check</p>
                </div>
                <span style="color: #999;">1 hour ago</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #eee;">
                <div>
                    <strong>Michael Brown</strong>
                    <p style="color: #666; margin: 0;">Diabetes management</p>
                </div>
                <span style="color: #999;">3 hours ago</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                <div>
                    <strong>Lisa Anderson</strong>
                    <p style="color: #666; margin: 0;">Post-surgery follow-up</p>
                </div>
                <span style="color: #999;">5 hours ago</span>
            </div>
        </div>
    </div>

    <!-- Quick Action Buttons -->
    <div style="text-align: center; margin-top: 2rem; display: flex; justify-content: center; gap: 1rem;">
        <a href="#" class="btn btn-info">📝 Add Patient Note</a>
        <a href="#" class="btn btn-success">💊 Write Prescription</a>
        <a href="<?= base_url('auth/logout') ?>" class="btn btn-danger">🚪 Logout</a>
    </div>
</div>
<?= $this->endSection() ?>
