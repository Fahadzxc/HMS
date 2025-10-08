<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Appointments</h2>
        <p>Manage patient appointments and scheduling</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Today's Appointments</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Confirmed</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-negative">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">This Week</div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Appointment Schedule</h2>
        <div class="row between">
            <input type="text" placeholder="Search appointments..." class="search-input">
            <a href="#" class="btn-primary">+ Schedule Appointment</a>
        </div>
    </header>
    
    <div class="stack">
        <!-- Empty state until real data exists -->
        <div class="card">
            <p>No appointments yet. Click "Schedule Appointment" to add one.</p>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
