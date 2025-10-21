<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Patients</h2>
        <p>Manage patient records and information</p>
    </header>
    <div class="stack">
        <?php
        $patientsList = isset($patients) && is_array($patients) ? $patients : [];
        $totalPatients = count($patientsList);
        $today = date('Y-m-d');
        $todayCount = 0;
        foreach ($patientsList as $pp) {
            if (!empty($pp['created_at']) && substr($pp['created_at'], 0, 10) === $today) {
                $todayCount++;
            }
        }
        ?>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Patients</div>
                    <div class="kpi-value"><?= $totalPatients ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">New Patients Today</div>
                    <div class="kpi-value"><?= $todayCount ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Admitted Patients</div>
                    <div class="kpi-value">—</div>
                    <div class="kpi-change kpi-negative">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Critical Patients</div>
                    <div class="kpi-value">—</div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
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
        </div>
    </header>
    
    <div class="stack">
        <!-- Table Header (matches patients table schema) -->
        <div class="card table-header">
            <div class="row between">
                <div class="col-id">Patient ID</div>
                <div class="col-name">Name</div>
                <div class="col-age">AGE/GENDER</div>
                <div class="col-contact">CONTACT</div>
                <div class="col-status">Status</div>
                <div class="col-doctor">DOCTOR</div>
                <div class="col-actions">Actions</div>
            </div>
        </div>

        <!-- Patient Rows (from database) -->
        <?php if (!empty($patientsList)): ?>
            <?php foreach ($patientsList as $p): ?>
                <?php
                    $pid = 'P' . str_pad((string) $p['id'], 3, '0', STR_PAD_LEFT);
                    $last = !empty($p['created_at']) ? date('n/j/Y', strtotime($p['created_at'])) : '—';
                    
                    // Calculate age from date of birth
                    $age = '—';
                    if (!empty($p['date_of_birth']) && $p['date_of_birth'] !== '0000-00-00' && $p['date_of_birth'] !== '') {
                        try {
                            // Handle different date formats
                            $dateStr = $p['date_of_birth'];
                            if (strpos($dateStr, '/') !== false) {
                                // Format: MM/DD/YYYY
                                $parts = explode('/', $dateStr);
                                if (count($parts) === 3) {
                                    $dateStr = $parts[2] . '-' . $parts[0] . '-' . $parts[1]; // Convert to YYYY-MM-DD
                                }
                            }
                            
                            $birthDate = new DateTime($dateStr);
                            $today = new DateTime();
                            $ageDiff = $today->diff($birthDate);
                            $age = $ageDiff->y;
                            
                            // If less than 1 year old, show months
                            if ($age == 0 && $ageDiff->m > 0) {
                                $age = $ageDiff->m . ' months';
                            } else if ($age == 0 && $ageDiff->m == 0 && $ageDiff->d > 0) {
                                $age = $ageDiff->d . ' days';
                            }
                        } catch (Exception $e) {
                            $age = '—';
                        }
                    }
                    
                    // Get blood type or default to O+
                    $bloodType = !empty($p['blood_type']) ? $p['blood_type'] : 'O+';
                ?>
        <div class="card table-row">
            <div class="row between">
                        <div class="col-id patient-id"><?= esc($pid) ?></div>
                        <div class="col-name">
                            <div class="patient-info">
                                <div class="patient-avatar">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#3B82F6"/>
                                        <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="#3B82F6"/>
                                    </svg>
                                </div>
                                <div class="patient-details">
                                    <strong><?= esc($p['full_name']) ?></strong>
                                    <p class="blood-type">Blood: <?= esc($bloodType) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-age">
                            <div><?= esc($age) ?><?= (is_numeric($age) && $age > 0) ? ' years' : '' ?></div>
                            <div><?= esc($p['gender']) ?></div>
                        </div>
                        <div class="col-contact">
                            <p class="phone"><?= esc($p['contact']) ?></p>
                            <p class="email"><?= esc($p['email'] ?? 'patient@email.com') ?></p>
                        </div>
                        <div class="col-status">
                            <?php 
                            $statusClass = 'badge-green';
                            $statusText = 'Active';
                            if (isset($p['status'])) {
                                switch($p['status']) {
                                    case 'discharged':
                                        $statusClass = 'badge-gray';
                                        $statusText = 'Discharged';
                                        break;
                                    case 'transferred':
                                        $statusClass = 'badge-yellow';
                                        $statusText = 'Transferred';
                                        break;
                                }
                            }
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            <?php if (isset($p['patient_type'])): ?>
                                <br><small class="text-muted"><?= ucfirst($p['patient_type']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-doctor">—</div>
                <div class="col-actions">
                    <a href="#" class="action-link">View</a>
                    <a href="#" class="action-link">Edit</a>
                    <a href="#" class="action-link action-delete">Delete</a>
                </div>
            </div>
        </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div class="card table-row">
            <div class="row between">
                    <div class="col-name">No patients found.</div>
                </div>
            </div>
        <?php endif; ?>
        </div>
</section>



<?= $this->endSection() ?>