<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container">
    <!-- Welcome Section -->
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">🏥 Admin Dashboard</h1>
            <p class="card-subtitle">Welcome back, <?= session()->get('name') ?>!</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">150+</div>
            <div class="stat-label">Total Patients</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">25+</div>
            <div class="stat-label">Doctors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">50+</div>
            <div class="stat-label">Nurses</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">12</div>
            <div class="stat-label">Departments</div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Admin Actions</h2>
            <p class="card-subtitle">Manage your hospital system</p>
        </div>

        <div class="quick-actions">
            <a href="#" class="action-btn">
                <div class="action-icon">👥</div>
                <div class="action-text">Manage Users</div>
            </a>
            <a href="#" class="action-btn">
                <div class="action-icon">🏥</div>
                <div class="action-text">Departments</div>
            </a>
            <a href="#" class="action-btn">
                <div class="action-icon">📊</div>
                <div class="action-text">Reports</div>
            </a>
            <a href="#" class="action-btn">
                <div class="action-icon">⚙️</div>
                <div class="action-text">Settings</div>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Activity</h2>
            <p class="card-subtitle">Latest hospital activities</p>
        </div>

        <div style="padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #eee;">
                <div>
                    <strong>New patient registered</strong>
                    <p style="color: #666; margin: 0;">John Doe - Emergency Department</p>
                </div>
                <span style="color: #999;">2 hours ago</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #eee;">
                <div>
                    <strong>Doctor appointment scheduled</strong>
                    <p style="color: #666; margin: 0;">Dr. Smith - Cardiology</p>
                </div>
                <span style="color: #999;">4 hours ago</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                <div>
                    <strong>New staff member added</strong>
                    <p style="color: #666; margin: 0;">Jane Nurse - ICU Department</p>
                </div>
                <span style="color: #999;">1 day ago</span>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <div style="text-align: center; margin-top: 2rem;">
        <a href="<?= base_url('auth/logout') ?>" class="btn" style="background: #e74c3c;">
            🚪 Logout
        </a>
    </div>
</div>
<?= $this->endSection() ?>
