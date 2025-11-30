<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Patient Registration</h2>
        <p>Register new patients and view patient records</p>
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
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">New Patients Today</div>
                    <div class="kpi-value"><?= $todayCount ?></div>
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
        <div class="card table-header">
            <div class="row between">
                <div class="col-id">Patient ID</div>
                <div class="col-name">Name</div>
                <div class="col-age">AGE/GENDER</div>
                <div class="col-contact">CONTACT</div>
                <div class="col-status">Status</div>
                <div class="col-actions">Actions</div>
            </div>
        </div>

        <?php if (!empty($patientsList)): ?>
            <?php foreach ($patientsList as $p): ?>
                <?php
                    $pid = 'P' . str_pad((string) $p['id'], 3, '0', STR_PAD_LEFT);
                    $age = '—';
                    if (!empty($p['date_of_birth']) && $p['date_of_birth'] !== '0000-00-00' && $p['date_of_birth'] !== '') {
                        try {
                            $dateStr = $p['date_of_birth'];
                            if (strpos($dateStr, '/') !== false) {
                                $parts = explode('/', $dateStr);
                                if (count($parts) === 3) {
                                    $dateStr = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                }
                            }
                            $birthDate = new DateTime($dateStr);
                            $today = new DateTime();
                            $ageDiff = $today->diff($birthDate);
                            $age = $ageDiff->y;
                        } catch (Exception $e) {
                            $age = '—';
                        }
                    }
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
                <div class="col-actions">
                    <a href="#" class="action-link" onclick="viewPatient(<?= $p['id'] ?>); return false;">View</a>
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
        <form id="addPatientForm" class="modal-body" method="post" action="<?= site_url('reception/patients/store') ?>">
            <?= csrf_field() ?>
            <style>
                .name-grid{display:grid;grid-template-columns:2fr 1fr 2fr;gap:16px}
                @media (max-width: 768px){.name-grid{grid-template-columns:1fr}}
            </style>
            <div class="name-grid">
                <div class="form-field">
                    <label>First Name <span class="req">*</span></label>
                    <input type="text" name="first_name" required>
                    <div class="error" data-error-for="first_name"></div>
                </div>
                <div class="form-field">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name">
                    <div class="error" data-error-for="middle_name"></div>
                </div>
                <div class="form-field">
                    <label>Last Name <span class="req">*</span></label>
                    <input type="text" name="last_name" required>
                    <div class="error" data-error-for="last_name"></div>
                </div>
                <input type="hidden" name="full_name">
                    <div class="error" data-error-for="full_name"></div>
                </div>

            <div class="form-grid">
                <div class="form-field">
                    <label>Date of Birth <span class="req">*</span></label>
                    <input type="text" name="date_of_birth" id="date_of_birth" placeholder="mm/dd/yyyy" maxlength="10" required>
                    <div class="error" data-error-for="date_of_birth"></div>
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
                    <label>Blood Type</label>
                    <select name="blood_type">
                        <option value="">Select Blood Type</option>
                        <option>A+</option>
                        <option>A-</option>
                        <option>B+</option>
                        <option>B-</option>
                        <option>AB+</option>
                        <option>AB-</option>
                        <option>O+</option>
                        <option>O-</option>
                    </select>
                    <div class="error" data-error-for="blood_type"></div>
                </div>
                <div class="form-field">
                    <label>Contact Number <span class="req">*</span></label>
                    <input type="text" name="contact" placeholder="09XX XXX XXXX" required>
                    <small class="form-help">Philippine mobile number (09XX XXX XXXX)</small>
                    <div class="error" data-error-for="contact"></div>
                </div>
                <div class="form-field">
                    <label>Email Address</label>
                    <input type="email" name="email">
                    <div class="error" data-error-for="email"></div>
                </div>
                <div class="form-field">
                    <label>Province <span class="req">*</span></label>
                    <select id="province" required>
                        <option value="">Select Province</option>
                        <option value="south_cotabato">South Cotabato</option>
                        <option value="sarangani">Sarangani</option>
                    </select>
                    <div class="error" data-error-for="province"></div>
                </div>
                <div class="form-field">
                    <label>City/Municipality <span class="req">*</span></label>
                    <select id="city" required>
                        <option value="">Select City/Municipality</option>
                    </select>
                    <div class="error" data-error-for="city"></div>
                </div>
                <div class="form-field">
                    <label>Barangay <span class="req">*</span></label>
                    <select id="barangay" required>
                        <option value="">Select Barangay</option>
                    </select>
                    <div class="error" data-error-for="barangay"></div>
                </div>
                <input type="hidden" name="address">
                <div class="form-field">
                    <label>Patient Type <span class="req">*</span></label>
					<select name="patient_type" id="patientType" required>
                        <option value="">Select Patient Type</option>
                        <option value="outpatient">Outpatient</option>
                        <option value="inpatient">Inpatient</option>
                    </select>
                    <div class="error" data-error-for="patient_type"></div>
                </div>
                <div class="form-field">
                    <label>Insurance Provider</label>
                    <select name="insurance_provider" id="insuranceProvider">
                        <option value="">Select Insurance Provider</option>
                        <option value="philhealth">PhilHealth</option>
                        <option value="maxicare">Maxicare</option>
                        <option value="medicard">Medicard</option>
                        <option value="intellicare">Intellicare</option>
                        <option value="philcare">PhilCare</option>
                        <option value="insular_healthcare">Insular Healthcare</option>
                        <option value="avega">Avega</option>
                        <option value="pacific_cross">Pacific Cross</option>
                        <option value="none">None / Self-Pay</option>
                    </select>
                    <div class="error" data-error-for="insurance_provider"></div>
                </div>
                <div id="insuranceDetails" style="display: none;">
                    <div class="form-field">
                        <label>Policy Number <small style="color: #6B7280;">(Auto-generated if left blank)</small></label>
                        <input type="text" name="insurance_policy_number" id="insurancePolicyNumber" placeholder="Leave blank to auto-generate">
                        <div class="error" data-error-for="insurance_policy_number"></div>
                    </div>
                    <div class="form-field">
                        <label>Member ID <small style="color: #6B7280;">(Auto-generated if left blank)</small></label>
                        <input type="text" name="insurance_member_id" id="insuranceMemberId" placeholder="Leave blank to auto-generate">
                        <div class="error" data-error-for="insurance_member_id"></div>
                    </div>
                </div>
                <div class="form-field form-field--full">
                    <label>Medical Concern <span class="req">*</span></label>
                    <textarea name="concern" rows="4" required></textarea>
                    <div class="error" data-error-for="concern"></div>
                </div>
            </div>

			<!-- Inpatient section (hidden by default) -->
			<div id="inpatientSection" style="display: none; overflow: hidden;">
				<style>
					#inpatientSection .section-title{margin:16px 0 8px 0;font-weight:700;color:#1f2937}
					#inpatientSection .row{display:flex;flex-wrap:wrap;gap:16px}
					#inpatientSection .col-md-6{flex:1 1 calc(50% - 8px);min-width:260px}
					#inpatientSection .col-md-4{flex:1 1 calc(33.333% - 11px);min-width:220px}
					#inpatientSection .col-md-3{flex:1 1 calc(25% - 12px);min-width:180px}
					@media (max-width:768px){
						#inpatientSection .col-md-6,#inpatientSection .col-md-4,#inpatientSection .col-md-3{flex:1 1 100%}
					}
				</style>

				<div class="section-title">Admission Details</div>
				<div class="row">
					<div class="col-md-6 form-field">
						<label>Admission Date &amp; Time</label>
						<input type="datetime-local" name="admission_datetime">
					</div>
					<div class="col-md-6 form-field">
						<label>Admission Type</label>
						<select name="admission_type">
							<option value="">Select Type</option>
							<option value="emergency">Emergency</option>
							<option value="scheduled">Scheduled</option>
							<option value="transfer">Transfer</option>
						</select>
					</div>
					<div class="col-md-6 form-field">
						<label>Attending Doctor</label>
						<select name="attending_doctor_id" id="attendingDoctor">
							<option value="">Select Doctor</option>
						</select>
					</div>
					<div class="col-md-6 form-field">
						<label>Room / Ward</label>
						<select name="room_id" id="roomSelect">
							<option value="">Select Room / Ward</option>
						</select>
					</div>
					<div class="col-md-6 form-field">
						<label>Bed Number</label>
						<select name="bed_number" id="bedSelect">
							<option value="">Select Bed</option>
						</select>
					</div>
				</div>

                <div class="section-title">Vital Signs</div>
                <div class="row">
                    <div class="col-md-4 form-field">
                        <label>Temperature (°C)</label>
                        <input type="number" step="0.1" name="vs_temperature" placeholder="36.7">
                    </div>
                    <div class="col-md-4 form-field">
                        <label>Blood Pressure</label>
                        <input type="text" name="vs_bp" placeholder="120/80">
                    </div>
                    <div class="col-md-4 form-field">
                        <label>Heart Rate (bpm)</label>
                        <input type="number" name="vs_hr" placeholder="72">
                    </div>
                    <div class="col-md-4 form-field">
                        <label>Oxygen Saturation (%)</label>
                        <input type="number" name="vs_o2" placeholder="98">
                    </div>
                </div>

				<div class="section-title">Emergency Contact</div>
				<div class="row">
					<div class="col-md-6 form-field">
						<label>Contact Person Name</label>
						<input type="text" name="ec_name" placeholder="Full name">
					</div>
					<div class="col-md-6 form-field">
						<label>Contact Number</label>
						<input type="text" name="ec_contact" placeholder="09XX XXX XXXX">
					</div>
					<div class="col-md-6 form-field">
						<label>Relationship to Patient</label>
						<input type="text" name="ec_relationship" placeholder="Parent / Spouse / Sibling">
					</div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" id="btnCancel" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Patient</button>
            </footer>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    (function(){
        const openBtn = document.getElementById('btnOpenAddPatient');
        const modal = document.getElementById('addPatientModal');
        const closeBtn = document.getElementById('btnCloseModal');
        const cancelBtn = document.getElementById('btnCancel');
        const backdrop = document.getElementById('modalBackdrop');
        const form = document.getElementById('addPatientForm');

        function open(){
            modal.style.display='block';
            modal.setAttribute('aria-hidden','false');
            // Default the admission datetime to "now"
            const ad = form.querySelector('input[name="admission_datetime"]');
            if (ad && !ad.value) {
                const now = new Date();
                const pad = n => String(n).padStart(2,'0');
                const v = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
                ad.value = v;
            }
            // Re-filter doctors based on the defaulted datetime
            try { filterDoctorsByAdmission(); } catch(_e) {}
        }
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

            // Compose full_name from first/middle/last before sending
            const firstName = (form.querySelector('[name=\"first_name\"]').value || '').trim();
            const middleName = (form.querySelector('[name=\"middle_name\"]').value || '').trim();
            const lastName = (form.querySelector('[name=\"last_name\"]').value || '').trim();
            const fullName = [firstName, middleName, lastName].filter(Boolean).join(' ');
            const hiddenFullName = form.querySelector('[name=\"full_name\"]');
            if (hiddenFullName) hiddenFullName.value = fullName;

            const formData = new FormData(form);
            // Ensure backend receives expected keys/format
            formData.set('full_name', fullName);
            // Send contact as digits only (remove spaces)
            const contactRaw = (form.querySelector('input[name=\"contact\"]').value || '').replace(/\\D/g, '');
            if (contactRaw) formData.set('contact', contactRaw);
            // Ensure insurance fields are included
            const insuranceProvider = form.querySelector('[name=\"insurance_provider\"]')?.value || '';
            if (insuranceProvider) formData.set('insurance_provider', insuranceProvider);
            const insurancePolicy = form.querySelector('[name=\"insurance_policy_number\"]')?.value || '';
            if (insurancePolicy) formData.set('insurance_policy_number', insurancePolicy);
            const insuranceMember = form.querySelector('[name=\"insurance_member_id\"]')?.value || '';
            if (insuranceMember) formData.set('insurance_member_id', insuranceMember);
            // Compose address from province/city/barangay
            const provinceKey = (document.getElementById('province')?.value || '').trim();
            const cityKey = (document.getElementById('city')?.value || '').trim();
            const barangay = (document.getElementById('barangay')?.value || '').trim();
            const addressHidden = form.querySelector('[name=\"address\"]');
            if (addressHidden) {
                const provinceName = (window.__addressData?.[provinceKey]?.name) || '';
                const cityName = (window.__addressData?.[provinceKey]?.cities?.[cityKey]?.name) || '';
                const addressText = [barangay, cityName || provinceName].filter(Boolean).join(', ');
                addressHidden.value = addressText;
                if (addressText) formData.set('address', addressText);
            }

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
                    alert('Patient registered successfully');
                    location.reload();
                } else {
                    console.error('Server response:', data);
                    alert('Failed to register patient: ' + (data.message || 'Unknown error'));
                    submitBtn.disabled = false;
                }
            } catch (err){
                console.error(err);
                alert('Network error');
                submitBtn.disabled = false;
            }
        });

        // Date formatting
        const dateInput = document.getElementById('date_of_birth');
        if (dateInput) {
            dateInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 8) value = value.substring(0, 8);
                if (value.length >= 2) value = value.substring(0, 2) + '/' + value.substring(2);
                if (value.length >= 5) value = value.substring(0, 5) + '/' + value.substring(5);
                e.target.value = value;
            });
        }

        // Phone formatting
        const contactInput = document.querySelector('input[name="contact"]');
        if (contactInput) {
            contactInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.substring(0, 11);
                if (value.length >= 4) value = value.substring(0, 4) + ' ' + value.substring(4);
                if (value.length >= 8) value = value.substring(0, 8) + ' ' + value.substring(8);
                e.target.value = value;
            });
        }

		// Inpatient section toggle and dynamic dropdowns
		const patientTypeEl = document.getElementById('patientType');
		const inpatientEl = document.getElementById('inpatientSection');
		function showInpatient() {
			if (!inpatientEl) return;
			inpatientEl.style.display = 'block';
			inpatientEl.style.maxHeight = '2000px';
			inpatientEl.style.opacity = '1';
		}
		function hideInpatient() {
			if (!inpatientEl) return;
			inpatientEl.style.maxHeight = '0';
			inpatientEl.style.opacity = '0';
			// Use a small timeout to hide completely after transition
			setTimeout(() => { inpatientEl.style.display = 'none'; }, 250);
		}
		if (patientTypeEl && inpatientEl) {
			// Initialize state
			if (patientTypeEl.value === 'inpatient') showInpatient(); else hideInpatient();
			patientTypeEl.addEventListener('change', function() {
				if (this.value === 'inpatient') {
                    showInpatient();
                    // Ensure admission datetime defaults to now when switching to inpatient
                    const ad = document.querySelector('input[name="admission_datetime"]');
                    if (ad && !ad.value) {
                        const now = new Date();
                        const pad = n => String(n).padStart(2,'0');
                        ad.value = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
                    }
                    loadDoctors();
                    loadRooms();
					// Re-filter shortly after lists load
					setTimeout(() => { try { filterDoctorsByAdmission(); } catch(_e) {} }, 400);
				} else {
					hideInpatient();
				}
			});
		}

		// Populate Attending Doctor and Room/Ward dynamically
		async function loadDoctors() {
			const sel = document.getElementById('attendingDoctor');
			if (!sel || sel.options.length > 1) return; // already loaded or not present
			try {
				const resp = await fetch('<?= base_url('reception/doctors') ?>', { headers: { 'X-Requested-With':'XMLHttpRequest' }});
				const data = await resp.json();
				const doctors = Array.isArray(data?.doctors) ? data.doctors : (Array.isArray(data) ? data : []);
				if (doctors.length === 0) throw new Error('empty');
				doctors.forEach(d => {
					const opt = document.createElement('option');
					opt.value = d.id ?? d.doctor_id ?? '';
					opt.textContent = d.name ?? d.full_name ?? d.doctor_name ?? 'Doctor';
					sel.appendChild(opt);
				});
			} catch (e) {
				// Fallback static options
				['Dr. Santos','Dr. Reyes','Dr. Cruz'].forEach((name, idx) => {
					const opt = document.createElement('option');
					opt.value = 'fallback_' + (idx+1);
					opt.textContent = name;
					sel.appendChild(opt);
				});
			}
		}

		async function loadRooms() {
			const sel = document.getElementById('roomSelect');
			const bedSel = document.getElementById('bedSelect');
			if (!sel || sel.options.length > 1) return;
			try {
				const resp = await fetch('<?= base_url('reception/rooms') ?>?type=inpatient', { headers: { 'X-Requested-With':'XMLHttpRequest' }});
				const data = await resp.json();
				const rooms = Array.isArray(data?.rooms) ? data.rooms : [];
				if (rooms.length === 0) throw new Error('empty');
				rooms.forEach(r => {
					const opt = document.createElement('option');
					opt.value = r.id ?? r.room_id ?? '';
					opt.dataset.capacity = r.capacity ?? '';
					opt.dataset.occupancy = r.current_occupancy ?? '';
					const label = `${r.room_number ?? r.name ?? 'Room'}${r.specialization ? ' - ' + r.specialization : ''}${r.floor ? ' (Floor ' + r.floor + ')' : ''}`;
					opt.textContent = label;
					sel.appendChild(opt);
				});
				if (bedSel) {
					sel.addEventListener('change', populateBeds);
				}
			} catch (e) {
				['Ward A - 101','Ward B - 202','ICU - 3F'].forEach((label, idx) => {
					const opt = document.createElement('option');
					opt.value = 'fallback_room_' + (idx+1);
					opt.textContent = label;
					sel.appendChild(opt);
				});
			}
		}

		function populateBeds() {
			const sel = document.getElementById('roomSelect');
			const bedSel = document.getElementById('bedSelect');
			if (!sel || !bedSel) return;
			const selected = sel.options[sel.selectedIndex];
			const cap = parseInt(selected?.dataset?.capacity || '0', 10);
			bedSel.innerHTML = '<option value=\"\">Select Bed</option>';
			if (!isNaN(cap) && cap > 0) {
				for (let i = 1; i <= cap; i++) {
					const opt = document.createElement('option');
					opt.value = String(i);
					opt.textContent = 'Bed ' + i;
					bedSel.appendChild(opt);
				}
			}
		}

        // Address cascading dropdowns (Province -> City -> Barangay)
        const provinceEl = document.getElementById('province');
        const cityEl = document.getElementById('city');
        const barangayEl = document.getElementById('barangay');
        window.__addressData = {
            south_cotabato: {
                name: 'South Cotabato',
                cities: {
                    gensan: {
                        name: 'General Santos City',
                        barangays: [
                            'Apopong','Baluan','Batomelong','Buayan','Bula','Calumpang','City Heights','Conel',
                            'Dadiangas East','Dadiangas North','Dadiangas South','Dadiangas West','Fatima','Katangawan',
                            'Labangal','Lagao','Mabuhay','Olympog','San Isidro','San Jose','Sinawal','Tambler',
                            'Tinagacan','Upper Labay'
                        ]
                    }
                }
            },
            sarangani: {
                name: 'Sarangani',
                cities: {
                    malapatan: { name: 'Malapatan', barangays: ['Daan Suyan','Kihan','Kinam','Libi','Lun Masla','Lun Padidu','Patag','Poblacion','Sapu Masla','Sapu Padidu','Tuyan','Upper Suyan'] },
                    alabel: { name: 'Alabel', barangays: ['Alegria','Bagacay','Baluntay','Datal Anggas','Domolok','Kawas','Maribulan','Pag-Asa','Paraiso','Poblacion','Spring','Tokawal'] },
                    glan: { name: 'Glan', barangays: ['Baliton','Batulaki','Burias','Cablalan','Congan','Glan Padidu','Gumasa','Ilaya','Kapatan','Lago','Laguimit','Mudan','Pangyan','Poblacion','Rio Del Pilar','San Jose','San Vicente','Taluya','Tango','Tapon'] },
                    kiamba: { name: 'Kiamba', barangays: ['Badtasan','Datu Datu','Gasi','Kapate','Katubao','Kayupo','Kling','Lagundi','Lebe','Lomuyon','Luma','Maligang','Nalus','Poblacion','Salakit','Suli','Tablao','Tamadang','Tambilil','Tuka'] },
                    maasim: { name: 'Maasim', barangays: ['Amsipit','Bales','Colon','Daliao','Kabatiol','Kablacan','Kamanga','Kanalo','Lumasal','Lumatil','Malbang','Nomoh','Pananag','Poblacion','Seven Hills','Tinoto'] }
                }
            }
        };

        function populateCities() {
            cityEl.innerHTML = '<option value=\"\">Select City/Municipality</option>';
            barangayEl.innerHTML = '<option value=\"\">Select Barangay</option>';
            const prov = provinceEl.value;
            if (!prov || !window.__addressData[prov]) return;
            const cities = window.__addressData[prov].cities || {};
            Object.keys(cities).forEach(key => {
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = cities[key].name;
                cityEl.appendChild(opt);
            });
        }

        function populateBarangays() {
            barangayEl.innerHTML = '<option value=\"\">Select Barangay</option>';
            const prov = provinceEl.value;
            const city = cityEl.value;
            if (!prov || !city || !window.__addressData[prov]?.cities?.[city]) return;
            const brgys = window.__addressData[prov].cities[city].barangays || [];
            brgys.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b;
                opt.textContent = b;
                barangayEl.appendChild(opt);
            });
        }

        if (provinceEl && cityEl && barangayEl) {
            provinceEl.addEventListener('change', populateCities);
            cityEl.addEventListener('change', populateBarangays);
        }

        // Admission datetime → filter available doctors based on schedule (debounced, limited)
        const admissionDt = document.querySelector('input[name=\"admission_datetime\"]');
        const doctorSelect = document.getElementById('attendingDoctor');
        let __filterDoctorsTimer = null;
        let __filterInFlight = false;
        function scheduleFilterDoctors() {
            if (!admissionDt) return;
            clearTimeout(__filterDoctorsTimer);
            __filterDoctorsTimer = setTimeout(() => { filterDoctorsByAdmission().catch(()=>{}); }, 500);
        }
        async function filterDoctorsByAdmission() {
            if (__filterInFlight) return;
            __filterInFlight = true;
            if (!doctorSelect) return;
            // Load doctors once and populate (no per-doctor schedule calls to avoid heavy loading)
            try {
                const resp = await fetch('<?= base_url('reception/doctors') ?>', { headers: { 'X-Requested-With':'XMLHttpRequest' }});
                const data = await resp.json();
                const doctors = Array.isArray(data?.doctors) ? data.doctors : [];
                doctorSelect.innerHTML = '<option value=\"\">Select Doctor</option>';
                doctors.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.name;
                    doctorSelect.appendChild(opt);
                });
            } catch(e) {
                doctorSelect.innerHTML = '<option value=\"\">Select Doctor</option>';
            }
            __filterInFlight = false;
        }
		if (admissionDt) {
            admissionDt.addEventListener('change', scheduleFilterDoctors);
            admissionDt.addEventListener('input', scheduleFilterDoctors);
            admissionDt.addEventListener('blur', scheduleFilterDoctors);
        }

        // Patient type handling - removed room selection logic
    })();
    
    // Insurance Provider change handler
    (function() {
        const insuranceProvider = document.getElementById('insuranceProvider');
        const insuranceDetails = document.getElementById('insuranceDetails');
        const policyNumberInput = document.getElementById('insurancePolicyNumber');
        const memberIdInput = document.getElementById('insuranceMemberId');
        
        if (insuranceProvider) {
            insuranceProvider.addEventListener('change', function() {
                const provider = this.value;
                
                // Show/hide insurance details fields
                if (provider && provider !== '' && provider !== 'none') {
                    if (insuranceDetails) insuranceDetails.style.display = 'block';
                    
                    // Auto-generate Policy Number and Member ID if fields are empty
                    if (policyNumberInput && !policyNumberInput.value.trim()) {
                        const year = new Date().getFullYear();
                        const month = String(new Date().getMonth() + 1).padStart(2, '0');
                        const providerCode = provider.substring(0, 3).toUpperCase();
                        const randomNum = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                        policyNumberInput.value = providerCode + '-' + year + month + '-' + randomNum;
                    }
                    
                    if (memberIdInput && !memberIdInput.value.trim()) {
                        const providerCode = provider.substring(0, 2).toUpperCase();
                        const randomNum = Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
                        memberIdInput.value = providerCode + randomNum;
                    }
                } else {
                    if (insuranceDetails) insuranceDetails.style.display = 'none';
                    if (policyNumberInput) policyNumberInput.value = '';
                    if (memberIdInput) memberIdInput.value = '';
                }
            });
            
            // Trigger change event on page load if insurance provider is already selected
            if (insuranceProvider.value && insuranceProvider.value !== '' && insuranceProvider.value !== 'none') {
                insuranceProvider.dispatchEvent(new Event('change'));
            }
        }
    })();
</script>

<!-- Patient View Modal -->
<div id="patientViewModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="patientViewModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="patientViewTitle" style="max-width: 800px; max-height: 90vh; display: flex; flex-direction: column;">
        <header class="panel-header modal-header">
            <h2 id="patientViewTitle">Patient Information</h2>
            <button type="button" class="close" onclick="closePatientViewModal()">&times;</button>
        </header>
        <div class="modal-body" id="patientViewContent" style="max-height: 70vh; overflow-y: auto;">
            <div style="text-align: center; padding: 2rem;">
                <p>Loading patient information...</p>
            </div>
        </div>
        <footer class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePatientViewModal()">Close</button>
        </footer>
    </div>
</div>

<script>
function viewPatient(patientId) {
    const modal = document.getElementById('patientViewModal');
    const content = document.getElementById('patientViewContent');
    const title = document.getElementById('patientViewTitle');
    
    // Show modal and loading state
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    content.innerHTML = '<div style="text-align: center; padding: 2rem;"><p>Loading patient information...</p></div>';
    
    // Fetch patient details
    fetch(`<?= site_url('reception/patients/show') ?>/${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.patient) {
                displayPatientInfo(data.patient, data.assigned_nurse, data.latest_vitals, data.vital_signs_history);
            } else {
                content.innerHTML = '<div style="text-align: center; padding: 2rem; color: #ef4444;"><p>Error: ' + (data.message || 'Failed to load patient information') + '</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div style="text-align: center; padding: 2rem; color: #ef4444;"><p>Error loading patient information</p></div>';
        });
}

function displayPatientInfo(patient, assignedNurse, latestVitals, vitalSignsHistory) {
    const content = document.getElementById('patientViewContent');
    const pid = 'P' + String(patient.id).padStart(3, '0');
    
    // Calculate age
    let age = '—';
    let dateOfBirth = '—';
    if (patient.date_of_birth && patient.date_of_birth !== '0000-00-00' && patient.date_of_birth !== '') {
        try {
            let dateStr = patient.date_of_birth;
            if (dateStr.includes('/')) {
                const parts = dateStr.split('/');
                if (parts.length === 3) {
                    dateStr = parts[2] + '-' + parts[0] + '-' + parts[1];
                }
            }
            const birthDate = new Date(dateStr);
            const today = new Date();
            const ageDiff = today - birthDate;
            const years = Math.floor(ageDiff / (365.25 * 24 * 60 * 60 * 1000));
            age = years > 0 ? years + ' years' : 'Less than 1 year';
            
            // Format date for display
            dateOfBirth = birthDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        } catch (e) {
            dateOfBirth = patient.date_of_birth;
        }
    }
    
    // Format registration date
    let registrationDate = '—';
    if (patient.created_at) {
        try {
            const regDate = new Date(patient.created_at);
            registrationDate = regDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            registrationDate = patient.created_at;
        }
    }
    
    // Format contact number
    let contactFormatted = patient.contact || '—';
    if (contactFormatted.length === 11 && contactFormatted.startsWith('09')) {
        contactFormatted = contactFormatted.substring(0, 4) + ' ' + contactFormatted.substring(4, 7) + ' ' + contactFormatted.substring(7);
    }
    
    const html = `
        <div class="patient-view-container">
            <div class="patient-view-header">
                <div class="patient-avatar-large">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#3B82F6"/>
                        <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="#3B82F6"/>
                    </svg>
                </div>
                <div class="patient-view-title">
                    <h3>${patient.full_name || '—'}</h3>
                    <p class="patient-id-display">Patient ID: ${pid}</p>
                </div>
            </div>
            
            <div class="patient-view-grid">
                <div class="patient-view-section">
                    <h4>Personal Information</h4>
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value">${patient.full_name || '—'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date of Birth:</span>
                        <span class="info-value">${dateOfBirth}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Age:</span>
                        <span class="info-value">${age}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gender:</span>
                        <span class="info-value">${patient.gender || '—'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Blood Type:</span>
                        <span class="info-value">${patient.blood_type || 'Not specified'}</span>
                    </div>
                </div>
                
                <div class="patient-view-section">
                    <h4>Contact Information</h4>
                    <div class="info-row">
                        <span class="info-label">Contact Number:</span>
                        <span class="info-value">${contactFormatted}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email Address:</span>
                        <span class="info-value">${patient.email || 'Not provided'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span class="info-value">${patient.address || '—'}</span>
                    </div>
                </div>
                
                <div class="patient-view-section">
                    <h4>Medical Information</h4>
                    <div class="info-row">
                        <span class="info-label">Patient Type:</span>
                        <span class="info-value"><span class="badge badge-${patient.patient_type === 'inpatient' ? 'blue' : 'green'}">${patient.patient_type ? patient.patient_type.charAt(0).toUpperCase() + patient.patient_type.slice(1) : '—'}</span></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value"><span class="badge badge-green">${patient.status ? patient.status.charAt(0).toUpperCase() + patient.status.slice(1) : 'Active'}</span></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Medical Concern:</span>
                        <span class="info-value" style="display: block; margin-top: 0.5rem;">${patient.concern || 'Not specified'}</span>
                    </div>
                </div>
                
                <div class="patient-view-section">
                    <h4>Registration Information</h4>
                    <div class="info-row">
                        <span class="info-label">Registration Date:</span>
                        <span class="info-value">${registrationDate}</span>
                    </div>
                    ${patient.room_number ? `
                    <div class="info-row">
                        <span class="info-label">Room Number:</span>
                        <span class="info-value">${patient.room_number}</span>
                    </div>
                    ` : ''}
                    ${patient.admission_date ? `
                    <div class="info-row">
                        <span class="info-label">Admission Date:</span>
                        <span class="info-value">${patient.admission_date}</span>
                    </div>
                    ` : ''}
                    <div class="info-row">
                        <span class="info-label">Assigned Nurse:</span>
                        <span class="info-value"><strong>${assignedNurse || 'Not assigned'}</strong></span>
                    </div>
                </div>
            </div>
            
            ${latestVitals || (vitalSignsHistory && vitalSignsHistory.length > 0) ? `
            <div class="patient-view-section patient-view-section-full">
                <h4>Latest Vital Signs</h4>
                ${latestVitals ? `
                <div class="vitals-display">
                    <div class="vital-item-display">
                        <span class="vital-label">Time:</span>
                        <span class="vital-value">${latestVitals.time || '—'}</span>
                    </div>
                    <div class="vital-item-display">
                        <span class="vital-label">Blood Pressure:</span>
                        <span class="vital-value">${latestVitals.blood_pressure || '—'}</span>
                    </div>
                    <div class="vital-item-display">
                        <span class="vital-label">Heart Rate:</span>
                        <span class="vital-value">${latestVitals.heart_rate || '—'}</span>
                    </div>
                    <div class="vital-item-display">
                        <span class="vital-label">Temperature:</span>
                        <span class="vital-value">${latestVitals.temperature || '—'}</span>
                    </div>
                    <div class="vital-item-display">
                        <span class="vital-label">Oxygen Saturation:</span>
                        <span class="vital-value">${latestVitals.oxygen_saturation || '—'}</span>
                    </div>
                    ${latestVitals.recorded_at ? `
                    <div class="vital-item-display">
                        <span class="vital-label">Recorded:</span>
                        <span class="vital-value" style="font-size: 0.8rem; color: #64748b;">${new Date(latestVitals.recorded_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                    </div>
                    ` : ''}
                </div>
                ` : '<p style="color: #64748b; text-align: center;">No vital signs recorded yet</p>'}
                
                ${vitalSignsHistory && vitalSignsHistory.length > 0 ? `
                <div style="margin-top: 1.5rem;">
                    <h5 style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #475569; font-weight: 600;">Recent Vital Signs History</h5>
                    <div class="vitals-history-table">
                        <div class="vitals-history-header">
                            <div>Time</div>
                            <div>BP</div>
                            <div>HR</div>
                            <div>Temp</div>
                            <div>O2 Sat</div>
                            <div>Nurse</div>
                        </div>
                        ${vitalSignsHistory.map(vital => `
                            <div class="vitals-history-row">
                                <div>${vital.time || '—'}</div>
                                <div>${vital.blood_pressure || '—'}</div>
                                <div>${vital.heart_rate || '—'}</div>
                                <div>${vital.temperature || '—'}</div>
                                <div>${vital.oxygen_saturation || '—'}</div>
                                <div><strong>${vital.nurse_name || '—'}</strong></div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            </div>
            ` : ''}
        </div>
    `;
    
    content.innerHTML = html;
}

function closePatientViewModal() {
    const modal = document.getElementById('patientViewModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

// Close modal on backdrop click
document.addEventListener('DOMContentLoaded', function() {
    const backdrop = document.getElementById('patientViewModalBackdrop');
    if (backdrop) {
        backdrop.addEventListener('click', closePatientViewModal);
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('patientViewModal');
            if (modal && modal.style.display === 'block') {
                closePatientViewModal();
            }
        }
    });
});
</script>

<?= $this->endSection() ?>

