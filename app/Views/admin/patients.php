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
            <a href="#" id="btnOpenAddPatient" class="btn-primary">+ Add Patient</a>
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

        <!-- Patient Rows (from database) -->
        <?php if (!empty($patientsList)): ?>
            <?php foreach ($patientsList as $p): ?>
                <?php
                    $pid = 'P' . str_pad((string) $p['id'], 3, '0', STR_PAD_LEFT);
                    $last = !empty($p['created_at']) ? date('n/j/Y', strtotime($p['created_at'])) : '—';
                ?>
                <div class="card table-row">
                    <div class="row between">
                        <div class="col-id patient-id"><?= esc($pid) ?></div>
                        <div class="col-name">
                            <strong><?= esc($p['full_name']) ?></strong>
                            <p class="blood-type">Address: <?= esc($p['address']) ?></p>
                        </div>
                        <div class="col-age"><?= esc($p['age']) ?> years, <?= esc($p['gender']) ?></div>
                        <div class="col-contact">
                            <p class="phone"><?= esc($p['contact']) ?></p>
                            <p class="email">Concern: <?= esc($p['concern']) ?></p>
                        </div>
                        <div class="col-status"><span class="badge badge-gray">—</span></div>
                        <div class="col-doctor">—</div>
                        <div class="col-visit"><?= esc($last) ?></div>
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

<!-- Add Patient Modal -->
<div id="addPatientModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="addPatientTitle">
        <header class="panel-header modal-header">
            <h2 id="addPatientTitle">Add New Patient</h2>
            <button id="btnCloseModal" class="icon-button" aria-label="Close">×</button>
        </header>
        <form id="addPatientForm" class="modal-body" method="post" action="<?= site_url('admin/patients/create') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="full_name" required>
                    <div class="error" data-error-for="full_name"></div>
                </div>
                <div class="form-field">
                    <label>Age <span class="req">*</span></label>
                    <input type="number" name="age" min="0" max="150" required>
                    <div class="error" data-error-for="age"></div>
                </div>
                <div class="form-field">
                    <label>Gender <span class="req">*</span></label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                    <div class="error" data-error-for="gender"></div>
                </div>
                <div class="form-field">
                    <label>Civil Status <span class="req">*</span></label>
                    <select name="civil_status" required>
                        <option value="">Select Status</option>
                        <option>Single</option>
                        <option>Married</option>
                        <option>Widowed</option>
                        <option>Separated</option>
                    </select>
                    <div class="error" data-error-for="civil_status"></div>
                </div>
                <div class="form-field">
                    <label>Contact Number <span class="req">*</span></label>
                    <input type="text" name="contact" required>
                    <div class="error" data-error-for="contact"></div>
                </div>
                <div class="form-field">
                    <label>Address <span class="req">*</span></label>
                    <select name="address" required>
                        <option value="">Select Address</option>
                        <option>Lagao</option>
                        <option>Bula</option>
                        <option>San Isidro</option>
                        <option>Calumpang</option>
                        <option>Tambler</option>
                        <option>City Heights</option>
                    </select>
                    <div class="error" data-error-for="address"></div>
                </div>
                <div class="form-field form-field--full">
                    <label>Medical Concern <span class="req">*</span></label>
                    <textarea name="concern" rows="4" required></textarea>
                    <div class="error" data-error-for="concern"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" id="btnCancel" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Patient</button>
            </footer>
        </form>
    </div>
    
    <style>
        .modal{position:fixed;inset:0;z-index:1000}
        .modal-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.35)}
        .modal-dialog{position:relative;margin:64px auto;background:#fff;border-radius:8px;max-width:860px;box-shadow:0 10px 30px rgba(0,0,0,.2)}
        .modal-header{padding:20px;border-bottom:1px solid var(--border,#e5e7eb);display:flex;align-items:center;justify-content:space-between}
        .modal-body{padding:20px}
        .modal-footer{display:flex;justify-content:flex-end;gap:12px;margin-top:16px}
        .icon-button{border:none;background:transparent;font-size:22px;cursor:pointer;line-height:1}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .form-field label{display:block;margin-bottom:6px;font-weight:600}
        .form-field input,.form-field select,.form-field textarea{width:100%;padding:10px 12px;border:1px solid var(--border,#e5e7eb);border-radius:6px;background:#fff}
        .form-field--full{grid-column:1/-1}
        .btn-secondary{background:#e5e7eb;color:#111;padding:10px 14px;border-radius:6px;border:1px solid #d1d5db}
        .btn-primary{display:inline-flex;align-items:center;gap:8px}
        .req{color:#ef4444}
        .error{color:#b91c1c;font-size:12px;margin-top:6px}
        @media (max-width: 780px){.modal-dialog{margin:24px;}.form-grid{grid-template-columns:1fr}}
    </style>
</div>

<script>
    (function(){
        const openBtn = document.getElementById('btnOpenAddPatient');
        const modal = document.getElementById('addPatientModal');
        const closeBtn = document.getElementById('btnCloseModal');
        const cancelBtn = document.getElementById('btnCancel');
        const backdrop = document.getElementById('modalBackdrop');
        const form = document.getElementById('addPatientForm');

        function open(){ modal.style.display='block'; modal.setAttribute('aria-hidden','false'); }
        function close(){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); clearErrors(); form.reset(); }
        function setError(name, msg){ const el = modal.querySelector('[data-error-for="'+name+'"]'); if(el){ el.textContent = msg || ''; } }
        function clearErrors(){ modal.querySelectorAll('.error').forEach(e=>e.textContent=''); }

        openBtn.addEventListener('click', function(e){ e.preventDefault(); open(); });
        closeBtn.addEventListener('click', function(){ close(); });
        cancelBtn.addEventListener('click', function(){ close(); });
        backdrop.addEventListener('click', function(){ close(); });
        document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ close(); }});

        form.addEventListener('submit', async function(e){
            e.preventDefault();
            clearErrors();
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            const formData = new FormData(form);

            try {
                const resp = await fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With':'XMLHttpRequest' } });
                if(resp.status === 422){
                    const data = await resp.json();
                    const errs = data.errors || {};
                    Object.keys(errs).forEach(k=> setError(k, errs[k]));
                    submitBtn.disabled = false;
                    return;
                }
                const data = await resp.json();
                if(data.status === 'success'){
                    close();
                    // Optionally refresh table or show message
                    alert('Patient added successfully');
                    location.reload();
                } else {
                    alert('Failed to add patient');
                    submitBtn.disabled = false;
                }
            } catch (err){
                console.error(err);
                alert('Network error');
                submitBtn.disabled = false;
            }
        });
    })();
</script>

<?= $this->endSection() ?>