<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <h2>Doctors Directory</h2>
        <p>Review doctor profiles and patient assignments</p>
    </header>
    <div class="stack">
        <div class="doctors-header-info">
            <span class="doctors-section-title">All Doctors</span>
            <span class="doctors-count"><?= count($doctors ?? []) ?> total</span>
        </div>

        <?php if (!empty($doctors)): ?>
            <?php foreach ($doctors as $doctor): ?>
                <div class="doctor-card-modern">
                    <div class="doctor-card-header">
                        <div class="doctor-info-section">
                            <div class="doctor-avatar-modern">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#1C3F70"/>
                                    <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="#1C3F70"/>
                                </svg>
                            </div>
                            <div class="doctor-details-modern">
                                <h3 class="doctor-name"><?= esc($doctor['name']) ?></h3>
                                <p class="doctor-email-modern"><?= esc($doctor['email']) ?></p>
                                <span class="badge badge-<?= $doctor['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= strtoupper($doctor['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="doctor-kpis-modern">
                            <div class="kpi-item">
                                <div class="kpi-number"><?= number_format($doctor['total_patients'] ?? 0) ?></div>
                                <div class="kpi-text">Total Patients</div>
                            </div>
                            <div class="kpi-item">
                                <div class="kpi-number"><?= number_format($doctor['todays_appointments'] ?? 0) ?></div>
                                <div class="kpi-text">Today</div>
                            </div>
                            <div class="kpi-item">
                                <div class="kpi-number"><?= number_format($doctor['upcoming_appointments'] ?? 0) ?></div>
                                <div class="kpi-text">Upcoming</div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($doctor['recent_appointments'])): ?>
                        <div class="appointments-section-modern">
                            <h4 class="appointments-title">Recent Patient Appointments</h4>
                            <div class="appointments-grid">
                                <?php foreach ($doctor['recent_appointments'] as $appointment): ?>
                                    <div class="appointment-card">
                                        <div class="appointment-main">
                                            <div class="appointment-patient-info">
                                                <strong class="appointment-patient-name"><?= esc($appointment['patient_name']) ?></strong>
                                                <span class="appointment-type-badge"><?= ucfirst($appointment['appointment_type'] ?? 'Consultation') ?></span>
                                            </div>
                                            <div class="appointment-meta">
                                                <div class="appointment-datetime-modern">
                                                    <span class="appointment-date-modern"><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></span>
                                                    <span class="appointment-time-modern"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></span>
                                                </div>
                                                <div class="appointment-room-modern">
                                                    <span class="room-text">Room: </span>
                                                    <span class="room-value"><?= esc($appointment['room_number'] ?? '—') ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="appointment-status-modern">
                                            <span class="badge badge-<?= 
                                                $appointment['status'] === 'confirmed' ? 'success' : 
                                                ($appointment['status'] === 'scheduled' ? 'warning' : 'info') 
                                            ?>">
                                                <?= strtoupper($appointment['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-appointments-modern">
                            <p class="no-appointments-text">No patient appointments assigned</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p class="empty-state-text">No doctors found.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
