<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Patients</h2>
        <p>Manage patient records and information</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Patients</div>
                    <div class="kpi-value">1,247</div>
                    <div class="kpi-change kpi-positive">+12% from last month</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">New Patients Today</div>
                    <div class="kpi-value">24</div>
                    <div class="kpi-change kpi-positive">+8% from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Admitted Patients</div>
                    <div class="kpi-value">89</div>
                    <div class="kpi-change kpi-negative">-3% from yesterday</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Critical Patients</div>
                    <div class="kpi-value">7</div>
                    <div class="kpi-change kpi-positive">+2% improvement</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Patient Records</h2>
        <div class="row between">
            <input type="text" placeholder="Search patients..." class="search-input">
            <a href="#" class="btn-primary">+ Add Patient</a>
        </div>
    </header>
    
    <div class="stack">
        <!-- Table Header -->
        <div class="card table-header">
            <div class="row between">
                <div class="col-id">Patient ID</div>
                <div class="col-name">Name</div>
                <div class="col-age">Age/Gender</div>
                <div class="col-contact">Contact</div>
                <div class="col-status">Status</div>
                <div class="col-doctor">Doctor</div>
                <div class="col-visit">Last Visit</div>
                <div class="col-actions">Actions</div>
            </div>
        </div>

        <!-- Patient Rows -->
        <div class="card table-row">
            <div class="row between">
                <div class="col-id patient-id">P006</div>
                <div class="col-name">
                    <strong>David Thompson</strong>
                    <p class="blood-type">Blood: O+</p>
                </div>
                <div class="col-age">41 years, Male</div>
                <div class="col-contact">
                    <p class="phone">+1 (555) 678-9012</p>
                    <p class="email">david.thompson@email.com</p>
                </div>
                <div class="col-status"><span class="badge low">Active</span></div>
                <div class="col-doctor">Dr. Wilson</div>
                <div class="col-visit">1/11/2024</div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id patient-id">P003</div>
                <div class="col-name">
                    <strong>Emily Davis</strong>
                    <p class="blood-type">Blood: B+</p>
                </div>
                <div class="col-age">28 years, Female</div>
                <div class="col-contact">
                    <p class="phone">+1 (555) 345-6789</p>
                    <p class="email">emily.davis@email.com</p>
                </div>
                <div class="col-status"><span class="badge medium badge-gray">Discharged</span></div>
                <div class="col-doctor">Dr. Johnson</div>
                <div class="col-visit">1/13/2024</div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id patient-id">P004</div>
                <div class="col-name">
                    <strong>James Wilson</strong>
                    <p class="blood-type">Blood: AB+</p>
                </div>
                <div class="col-age">52 years, Male</div>
                <div class="col-contact">
                    <p class="phone">+1 (555) 456-7890</p>
                    <p class="email">james.wilson@email.com</p>
                </div>
                <div class="col-status"><span class="badge high">Critical</span></div>
                <div class="col-doctor">Dr. Anderson</div>
                <div class="col-visit">1/15/2024</div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id patient-id">P005</div>
                <div class="col-name">
                    <strong>Lisa Martinez</strong>
                    <p class="blood-type">Blood: A-</p>
                </div>
                <div class="col-age">39 years, Female</div>
                <div class="col-contact">
                    <p class="phone">+1 (555) 567-8901</p>
                    <p class="email">lisa.martinez@email.com</p>
                </div>
                <div class="col-status"><span class="badge low">Active</span></div>
                <div class="col-doctor">Dr. Smith</div>
                <div class="col-visit">1/12/2024</div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id patient-id">P002</div>
                <div class="col-name">
                    <strong>Michael Brown</strong>
                    <p class="blood-type">Blood: O-</p>
                </div>
                <div class="col-age">45 years, Male</div>
                <div class="col-contact">
                    <p class="phone">+1 (555) 234-5678</p>
                    <p class="email">michael.brown@email.com</p>
                </div>
                <div class="col-status"><span class="badge medium badge-blue">Admitted</span></div>
                <div class="col-doctor">Dr. Wilson</div>
                <div class="col-visit">1/14/2024</div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>

        <div class="card table-row">
            <div class="row between">
                <div class="col-id patient-id">P001</div>
                <div class="col-name">
                    <strong>Sarah Johnson</strong>
                    <p class="blood-type">Blood: A+</p>
                </div>
                <div class="col-age">34 years, Female</div>
                <div class="col-contact">
                    <p class="phone">+1 (555) 123-4567</p>
                    <p class="email">sarah.johnson@email.com</p>
                </div>
                <div class="col-status"><span class="badge low">Active</span></div>
                <div class="col-doctor">Dr. Smith</div>
                <div class="col-visit">1/15/2024</div>
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