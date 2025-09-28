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
                    <div class="kpi-value">18</div>
                    <div class="kpi-change kpi-positive">+3 from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Confirmed</div>
                    <div class="kpi-value">12</div>
                    <div class="kpi-change kpi-positive">67% of total</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Pending</div>
                    <div class="kpi-value">4</div>
                    <div class="kpi-change kpi-negative">22% of total</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">This Week</div>
                    <div class="kpi-value">89</div>
                    <div class="kpi-change kpi-positive">+12% from last week</div>
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
        <!-- Table Header -->
        <div class="card table-header">
            <div class="row between">
                <div class="col-id">Appointment ID</div>
                <div class="col-name">Patient</div>
                <div class="col-doctor">Doctor</div>
                <div class="col-datetime">Date & Time</div>
                <div class="col-type">Type</div>
                <div class="col-status">Status</div>
                <div class="col-actions">Actions</div>
            </div>
        </div>

        <!-- Appointment Rows -->
        <div class="card table-row">
            <div class="row between">
                <div class="col-id appointment-id">APT001</div>
                <div class="col-name">
                    <strong>Sarah Johnson</strong>
                    <p class="phone">+1 (555) 123-4567</p>
                </div>
                <div class="col-doctor">Dr. Smith</div>
                <div class="col-datetime">1/15/2024, 09:00 AM</div>
                <div class="col-type">Consultation</div>
                <div class="col-status"><span class="badge low">Confirmed</span></div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id appointment-id">APT002</div>
                <div class="col-name">
                    <strong>Michael Brown</strong>
                    <p class="phone">+1 (555) 234-5678</p>
                </div>
                <div class="col-doctor">Dr. Wilson</div>
                <div class="col-datetime">1/15/2024, 10:30 AM</div>
                <div class="col-type">Follow-up</div>
                <div class="col-status"><span class="badge medium">Pending</span></div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id appointment-id">APT003</div>
                <div class="col-name">
                    <strong>Emily Davis</strong>
                    <p class="phone">+1 (555) 345-6789</p>
                </div>
                <div class="col-doctor">Dr. Johnson</div>
                <div class="col-datetime">1/15/2024, 02:00 PM</div>
                <div class="col-type">Checkup</div>
                <div class="col-status"><span class="badge medium badge-blue">Completed</span></div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id appointment-id">APT004</div>
                <div class="col-name">
                    <strong>James Wilson</strong>
                    <p class="phone">+1 (555) 456-7890</p>
                </div>
                <div class="col-doctor">Dr. Anderson</div>
                <div class="col-datetime">1/16/2024, 11:00 AM</div>
                <div class="col-type">Emergency</div>
                <div class="col-status"><span class="badge low">Confirmed</span></div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id appointment-id">APT005</div>
                <div class="col-name">
                    <strong>Lisa Martinez</strong>
                    <p class="phone">+1 (555) 567-8901</p>
                </div>
                <div class="col-doctor">Dr. Smith</div>
                <div class="col-datetime">1/16/2024, 03:30 PM</div>
                <div class="col-type">Consultation</div>
                <div class="col-status"><span class="badge high">Cancelled</span></div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id appointment-id">APT006</div>
                <div class="col-name">
                    <strong>David Thompson</strong>
                    <p class="phone">+1 (555) 678-9012</p>
                </div>
                <div class="col-doctor">Dr. Wilson</div>
                <div class="col-datetime">1/17/2024, 09:30 AM</div>
                <div class="col-type">Follow-up</div>
                <div class="col-status"><span class="badge low">Confirmed</span></div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
