<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <h2>Doctors Directory</h2>
        <p>Review doctor profiles and patient assignments</p>
    </header>
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>All Doctors</span>
                <span><?= count($doctors ?? []) ?> total</span>
            </div>
        </div>

        <?php if (!empty($doctors)): ?>
            <?php foreach ($doctors as $doctor): ?>
                <div class="doctor-card">
                    <div class="doctor-header">
                        <div class="doctor-info">
                            <div class="doctor-avatar">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#3B82F6"/>
                                    <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="#3B82F6"/>
                                </svg>
                            </div>
                            <div class="doctor-details">
                                <h3><?= esc($doctor['name']) ?></h3>
                                <p class="doctor-email"><?= esc($doctor['email']) ?></p>
                                <span class="badge badge-<?= $doctor['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($doctor['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="doctor-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?= $doctor['total_patients'] ?></div>
                                <div class="stat-label">Total Patients</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?= $doctor['todays_appointments'] ?></div>
                                <div class="stat-label">Today's Appointments</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?= $doctor['upcoming_appointments'] ?></div>
                                <div class="stat-label">Upcoming</div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($doctor['recent_appointments'])): ?>
                        <div class="appointments-section">
                            <h4>Recent Patient Appointments</h4>
                            <div class="appointments-list">
                                <?php foreach ($doctor['recent_appointments'] as $appointment): ?>
                                    <div class="appointment-item">
                                        <div class="appointment-patient">
                                            <strong><?= esc($appointment['patient_name']) ?></strong>
                                            <span class="appointment-type"><?= ucfirst($appointment['appointment_type']) ?></span>
                                        </div>
                                        <div class="appointment-details">
                                            <div class="appointment-datetime">
                                                <div class="appointment-date"><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></div>
                                                <div class="appointment-time"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></div>
                                            </div>
                                            <div class="appointment-room">
                                                <span class="room-label">Room:</span>
                                                <span class="room-number"><?= esc($appointment['room_number'] ?? '—') ?></span>
                                            </div>
                                        </div>
                                        <div class="appointment-status">
                                            <span class="badge badge-<?= 
                                                $appointment['status'] === 'confirmed' ? 'success' : 
                                                ($appointment['status'] === 'scheduled' ? 'warning' : 'info') 
                                            ?>">
                                                <?= ucfirst($appointment['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-appointments">
                            <p class="text-muted">No patient appointments assigned</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <div class="text-center text-muted" style="padding: 2rem;">
                    <p>No doctors found.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.doctor-card {
    background: white;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.doctor-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.doctor-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.doctor-avatar {
    flex-shrink: 0;
}

.doctor-details h3 {
    margin: 0 0 0.25rem 0;
    color: #1e293b;
    font-size: 1.25rem;
    font-weight: 600;
}

.doctor-email {
    margin: 0 0 0.5rem 0;
    color: #64748b;
    font-size: 0.875rem;
}

.doctor-stats {
    display: flex;
    gap: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #3b82f6;
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.25rem;
}

.appointments-section {
    padding: 1.5rem;
}

.appointments-section h4 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1rem;
    font-weight: 600;
}

.appointments-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.appointment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 6px;
    border-left: 3px solid #3b82f6;
}

.appointment-patient {
    flex: 1;
}

.appointment-patient strong {
    display: block;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.appointment-type {
    font-size: 0.75rem;
    color: #64748b;
    background: #e2e8f0;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}

.appointment-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin: 0 1rem;
}

.appointment-datetime {
    text-align: center;
}

.appointment-date {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.appointment-time {
    font-size: 0.75rem;
    color: #64748b;
}

.appointment-room {
    text-align: center;
    font-size: 0.75rem;
}

.room-label {
    color: #64748b;
    margin-right: 0.25rem;
}

.room-number {
    color: #1e293b;
    font-weight: 600;
    background: #dbeafe;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}

.appointment-status {
    flex-shrink: 0;
}

.no-appointments {
    padding: 2rem 1.5rem;
    text-align: center;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-success { background-color: #c6f6d5; color: #22543d; }
.badge-warning { background-color: #fef5e7; color: #744210; }
.badge-secondary { background-color: #e2e8f0; color: #4a5568; }
.badge-info { background-color: #bee3f8; color: #2a4365; }

.text-muted {
    color: #64748b;
}

@media (max-width: 768px) {
    .doctor-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .doctor-stats {
        gap: 1rem;
    }
    
    .appointment-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .appointment-details {
        margin: 0;
        width: 100%;
    }
    
    .appointment-datetime {
        margin: 0;
    }
}
</style>
<?= $this->endSection() ?>
