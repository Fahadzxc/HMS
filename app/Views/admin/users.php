<?= $this->extend('template') ?>

<?php
// Helper function to format role names for display
function formatRoleName($role) {
    $roleMap = [
        'admin' => 'Administrator',
        'doctor' => 'Doctor',
        'nurse' => 'Nurse',
        'receptionist' => 'Receptionist',
        'pharmacist' => 'Pharmacy',
        'accountant' => 'Accountant',
        'lab' => 'Laboratory',
        'it' => 'IT Staff',
        'staff' => 'Staff',
    ];
    return $roleMap[strtolower($role)] ?? ucfirst($role);
}
?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>User Management</h2>
        <p>Manage system users, doctors, nurses, and staff accounts</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Users</div>
                    <div class="kpi-value"><?= count($users) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Doctors</div>
                    <div class="kpi-value"><?= count(array_filter($users, fn($u) => $u['role'] === 'doctor')) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Nurses</div>
                    <div class="kpi-value"><?= count(array_filter($users, fn($u) => $u['role'] === 'nurse')) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Active Users</div>
                    <div class="kpi-value"><?= count(array_filter($users, fn($u) => $u['status'] === 'active')) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>All Users</h2>
        <div class="row between">
            <input type="text" placeholder="Search users..." class="search-input">
            <a href="#" class="btn-primary" onclick="showAddUserModal()">+ Add New User</a>
        </div>
    </header>
    
    <div class="stack">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="card" style="background: #e8f5e8; border-left: 4px solid #4caf50;">
                <h3 style="color: #2e7d32; margin: 0 0 10px 0;">Success</h3>
                <p style="color: #2e7d32; margin: 0;"><?= session()->getFlashdata('success') ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="card" style="background: #ffebee; border-left: 4px solid #f44336;">
                <h3 style="color: #c62828; margin: 0 0 10px 0;">Error</h3>
                <p style="color: #c62828; margin: 0;"><?= session()->getFlashdata('error') ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="card" style="background: #ffebee; border-left: 4px solid #f44336;">
                <h3 style="color: #c62828; margin: 0 0 10px 0;">Error</h3>
                <p style="color: #c62828; margin: 0;"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Table Header (matches patients table schema) -->
        <div class="card table-header">
            <div class="row between">
                <div class="col-id">User ID</div>
                <div class="col-name">Name</div>
                <div class="col-age">ROLE/STATUS</div>
                <div class="col-contact">CONTACT</div>
                <div class="col-status">Status</div>
                <div class="col-doctor">CREATED</div>
                <div class="col-actions">Actions</div>
            </div>
        </div>

        <!-- User Rows (from database) -->
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <div class="card table-row">
                    <div class="row between">
                        <div class="col-id user-id"><?= $user['id'] ?></div>
                        <div class="col-name">
                            <div class="patient-info">
                                <div class="patient-avatar">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="#3B82F6"/>
                                        <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="#3B82F6"/>
                                    </svg>
                                </div>
                                <div class="patient-details">
                                    <strong><?= htmlspecialchars($user['name']) ?></strong>
                                    <p class="blood-type">Role: <?= formatRoleName($user['role']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-age">
                            <div><?= formatRoleName($user['role']) ?></div>
                            <div><?= ucfirst($user['status']) ?></div>
                        </div>
                        <div class="col-contact">
                            <p class="phone"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div class="col-status">
                            <span class="badge badge-green"><?= strtoupper($user['status']) ?></span>
                        </div>
                        <div class="col-doctor"><?= date('M d, Y', strtotime($user['created_at'])) ?></div>
                        <div class="col-actions">
                            <a href="#" class="action-link" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>); return false;">View</a>
                            <a href="#" class="action-link" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>); return false;">Edit</a>
                            <a href="#" class="action-link" onclick="deleteUser(<?= $user['id'] ?>); return false;">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <p class="text-center">No users found.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Add User Modal -->
<div id="addUserModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="addUserModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="addUserTitle">
        <header class="panel-header modal-header">
            <h2 id="addUserTitle">Add New User</h2>
            <button type="button" class="close" onclick="closeAddUserModal()">&times;</button>
        </header>
        <form id="addUserForm" class="modal-body" action="<?= base_url('admin/users/create') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field">
                    <label>First Name <span class="req">*</span></label>
                    <input type="text" name="first_name" id="add_first_name" required>
                    <div class="error" data-error-for="first_name"></div>
                </div>
                <div class="form-field">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" id="add_middle_name">
                    <div class="error" data-error-for="middle_name"></div>
                </div>
                <div class="form-field">
                    <label>Last Name <span class="req">*</span></label>
                    <input type="text" name="last_name" id="add_last_name" required>
                    <div class="error" data-error-for="last_name"></div>
                </div>
                <div class="form-field">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" id="add_email" required>
                    <div class="error" data-error-for="email"></div>
                </div>
                <div class="form-field">
                    <label>Password <span class="req">*</span></label>
                    <input type="password" name="password" id="add_password" required minlength="6">
                    <div class="error" data-error-for="password"></div>
                </div>
                <div class="form-field">
                    <label>Phone Number <span class="req">*</span></label>
                    <input type="tel" name="phone" id="add_phone" required>
                    <div class="error" data-error-for="phone"></div>
                </div>
                <div class="form-field">
                    <label>Role <span class="req">*</span></label>
                    <select name="role" id="add_role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Administrator</option>
                        <option value="doctor">Doctor</option>
                        <option value="nurse">Nurse</option>
                        <option value="receptionist">Receptionist</option>
                        <option value="pharmacist">Pharmacy</option>
                        <option value="accountant">Accountant</option>
                        <option value="lab">Laboratory</option>
                        <option value="it">IT Staff</option>
                        <option value="staff">Staff</option>
                    </select>
                    <div class="error" data-error-for="role"></div>
                </div>
                <div class="form-field">
                    <label>Status <span class="req">*</span></label>
                    <select name="status" id="add_status" required>
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="error" data-error-for="status"></div>
                </div>
                <div class="form-field" id="specialization_field" style="display: none;">
                    <label>Specialization <span class="req">*</span></label>
                    <select name="specialization" id="add_specialization">
                        <option value="">Select Specialization</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="Pediatrics">Pediatrics</option>
                        <option value="Internal Medicine">Internal Medicine</option>
                        <option value="Surgery">Surgery</option>
                        <option value="Orthopedics">Orthopedics</option>
                        <option value="Obstetrics and Gynecology">Obstetrics and Gynecology</option>
                        <option value="Dermatology">Dermatology</option>
                        <option value="Neurology">Neurology</option>
                        <option value="Psychiatry">Psychiatry</option>
                        <option value="Emergency Medicine">Emergency Medicine</option>
                        <option value="Anesthesiology">Anesthesiology</option>
                        <option value="Radiology">Radiology</option>
                        <option value="Pathology">Pathology</option>
                        <option value="Ophthalmology">Ophthalmology</option>
                        <option value="ENT (Ear, Nose, Throat)">ENT (Ear, Nose, Throat)</option>
                        <option value="Urology">Urology</option>
                        <option value="Pulmonology">Pulmonology</option>
                        <option value="Gastroenterology">Gastroenterology</option>
                        <option value="Endocrinology">Endocrinology</option>
                        <option value="Oncology">Oncology</option>
                        <option value="General Practice">General Practice</option>
                    </select>
                    <div class="error" data-error-for="specialization"></div>
                </div>
                <!-- License ID Field (for Doctor, Nurse, Lab) -->
                <div class="form-field" id="license_id_field" style="display: none;">
                    <label>License ID <span class="req">*</span></label>
                    <input type="text" name="license_id" id="add_license_id" readonly style="background-color: #f3f4f6; cursor: not-allowed;" placeholder="Auto-generated">
                    <small style="color: #6b7280; font-size: 0.875rem;">Random license ID will be auto-generated</small>
                    <div class="error" data-error-for="license_id"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAddUserModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add User</button>
            </footer>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-backdrop" id="editUserModalBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="editUserTitle">
        <header class="panel-header modal-header">
            <h2 id="editUserTitle">Edit User</h2>
            <button type="button" class="close" onclick="closeEditUserModal()">&times;</button>
        </header>
        <form id="editUserForm" class="modal-body" action="<?= base_url('admin/users/update') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="form-grid">
                <div class="form-field">
                    <label>First Name <span class="req">*</span></label>
                    <input type="text" name="first_name" id="edit_first_name" required>
                    <div class="error" data-error-for="first_name"></div>
                </div>
                <div class="form-field">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" id="edit_middle_name">
                    <div class="error" data-error-for="middle_name"></div>
                </div>
                <div class="form-field">
                    <label>Last Name <span class="req">*</span></label>
                    <input type="text" name="last_name" id="edit_last_name" required>
                    <div class="error" data-error-for="last_name"></div>
                </div>
                <div class="form-field">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" id="edit_email" required>
                    <div class="error" data-error-for="email"></div>
                </div>
                <div class="form-field">
                    <label>Phone Number <span class="req">*</span></label>
                    <input type="tel" name="phone" id="edit_phone" required>
                    <div class="error" data-error-for="phone"></div>
                </div>
                <div class="form-field">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="edit_password" minlength="6">
                    <div class="error" data-error-for="password"></div>
                </div>
                <div class="form-field">
                    <label>Role <span class="req">*</span></label>
                    <select name="role" id="edit_role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Administrator</option>
                        <option value="doctor">Doctor</option>
                        <option value="nurse">Nurse</option>
                        <option value="receptionist">Receptionist</option>
                        <option value="pharmacist">Pharmacy</option>
                        <option value="accountant">Accountant</option>
                        <option value="lab">Laboratory</option>
                        <option value="it">IT Staff</option>
                        <option value="staff">Staff</option>
                    </select>
                    <div class="error" data-error-for="role"></div>
                </div>
                <div class="form-field">
                    <label>Status <span class="req">*</span></label>
                    <select name="status" id="edit_status" required>
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="error" data-error-for="status"></div>
                </div>
                <div class="form-field" id="edit_specialization_field" style="display: none;">
                    <label>Specialization <span class="req">*</span></label>
                    <select name="specialization" id="edit_specialization">
                        <option value="">Select Specialization</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="Pediatrics">Pediatrics</option>
                        <option value="Internal Medicine">Internal Medicine</option>
                        <option value="Surgery">Surgery</option>
                        <option value="Orthopedics">Orthopedics</option>
                        <option value="Obstetrics and Gynecology">Obstetrics and Gynecology</option>
                        <option value="Dermatology">Dermatology</option>
                        <option value="Neurology">Neurology</option>
                        <option value="Psychiatry">Psychiatry</option>
                        <option value="Emergency Medicine">Emergency Medicine</option>
                        <option value="Anesthesiology">Anesthesiology</option>
                        <option value="Radiology">Radiology</option>
                        <option value="Pathology">Pathology</option>
                        <option value="Ophthalmology">Ophthalmology</option>
                        <option value="ENT (Ear, Nose, Throat)">ENT (Ear, Nose, Throat)</option>
                        <option value="Urology">Urology</option>
                        <option value="Pulmonology">Pulmonology</option>
                        <option value="Gastroenterology">Gastroenterology</option>
                        <option value="Endocrinology">Endocrinology</option>
                        <option value="Oncology">Oncology</option>
                        <option value="General Practice">General Practice</option>
                    </select>
                    <div class="error" data-error-for="specialization"></div>
                </div>
                <div class="form-field" id="edit_license_id_field" style="display: none;">
                    <label>License ID</label>
                    <input type="text" name="license_id" id="edit_license_id" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                    <small style="color: #6b7280; font-size: 0.875rem;">License ID cannot be changed</small>
                    <div class="error" data-error-for="license_id"></div>
                </div>
            </div>
            <footer class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditUserModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update User</button>
            </footer>
        </form>
    </div>
</div>

<script>
// Add User Modal
function showAddUserModal() {
    const modal = document.getElementById('addUserModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    const form = document.getElementById('addUserForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
    // Reset license ID field visibility
    toggleLicenseIdField();
}

// License ID and Specialization field toggle based on role
function toggleLicenseIdField() {
    const roleSelect = document.getElementById('add_role');
    const licenseField = document.getElementById('license_id_field');
    const licenseInput = document.getElementById('add_license_id');
    const specializationField = document.getElementById('specialization_field');
    const specializationSelect = document.getElementById('add_specialization');
    
    if (!roleSelect) return;
    
    const selectedRole = roleSelect.value;
    const isDoctor = selectedRole === 'doctor';
    const isNurse = selectedRole === 'nurse';
    const isLab = selectedRole === 'lab';
    const requiresLicense = isDoctor || isNurse || isLab;
    
    // Show/hide License ID field for Doctor, Nurse, and Lab
    if (licenseField && licenseInput) {
        if (requiresLicense) {
            licenseField.style.display = 'block';
            licenseInput.setAttribute('required', 'required');
            // Auto-generate random license ID (7 digits)
            const randomLicenseId = generateRandomLicenseId();
            licenseInput.value = randomLicenseId;
        } else {
            licenseField.style.display = 'none';
            licenseInput.removeAttribute('required');
            licenseInput.value = ''; // Clear value when hidden
        }
    }
    
    // Show/hide Specialization field only for Doctor
    if (specializationField && specializationSelect) {
        if (isDoctor) {
            specializationField.style.display = 'block';
            specializationSelect.setAttribute('required', 'required');
        } else {
            specializationField.style.display = 'none';
            specializationSelect.removeAttribute('required');
            specializationSelect.value = ''; // Clear value when hidden
        }
    }
}

// Generate random 7-digit license ID
function generateRandomLicenseId() {
    // Generate random 7-digit number (1000000 to 9999999)
    const min = 1000000;
    const max = 9999999;
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

// Edit User Modal
function editUser(user) {
    const modal = document.getElementById('editUserModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    
    // Fill form with user data
    document.getElementById('edit_user_id').value = user.id;
    
    // Split name into parts if available, otherwise parse from full name
    if (user.first_name) {
        document.getElementById('edit_first_name').value = user.first_name || '';
        document.getElementById('edit_middle_name').value = user.middle_name || '';
        document.getElementById('edit_last_name').value = user.last_name || '';
    } else if (user.name) {
        // Parse full name into parts
        const nameParts = user.name.trim().split(/\s+/);
        if (nameParts.length >= 2) {
            document.getElementById('edit_first_name').value = nameParts[0] || '';
            document.getElementById('edit_middle_name').value = nameParts.length > 2 ? nameParts.slice(1, -1).join(' ') : '';
            document.getElementById('edit_last_name').value = nameParts[nameParts.length - 1] || '';
        } else {
            document.getElementById('edit_first_name').value = user.name || '';
            document.getElementById('edit_middle_name').value = '';
            document.getElementById('edit_last_name').value = '';
        }
    }
    
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_role').value = user.role || '';
    document.getElementById('edit_status').value = user.status || '';
    document.getElementById('edit_specialization').value = user.specialization || '';
    document.getElementById('edit_license_id').value = user.license_id || '';
    
    // Toggle fields based on role
    toggleEditLicenseIdField();
}

// Toggle License ID and Specialization for Edit modal
function toggleEditLicenseIdField() {
    const roleSelect = document.getElementById('edit_role');
    const licenseField = document.getElementById('edit_license_id_field');
    const specializationField = document.getElementById('edit_specialization_field');
    const specializationSelect = document.getElementById('edit_specialization');
    
    if (!roleSelect) return;
    
    const selectedRole = roleSelect.value;
    const isDoctor = selectedRole === 'doctor';
    const isNurse = selectedRole === 'nurse';
    const isLab = selectedRole === 'lab';
    const requiresLicense = isDoctor || isNurse || isLab;
    
    // Show/hide License ID field
    if (licenseField) {
        if (requiresLicense) {
            licenseField.style.display = 'block';
        } else {
            licenseField.style.display = 'none';
        }
    }
    
    // Show/hide Specialization field only for Doctor
    if (specializationField && specializationSelect) {
        if (isDoctor) {
            specializationField.style.display = 'block';
            specializationSelect.setAttribute('required', 'required');
        } else {
            specializationField.style.display = 'none';
            specializationSelect.removeAttribute('required');
        }
    }
}

function closeEditUserModal() {
    const modal = document.getElementById('editUserModal');
    const form = document.getElementById('editUserForm');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    form.reset();
}

// Delete User
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('admin/users/delete') ?>';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '<?= csrf_token() ?>';
        csrfToken.value = '<?= csrf_hash() ?>';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        form.appendChild(csrfToken);
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Add backdrop click handlers
document.addEventListener('DOMContentLoaded', function() {
    // Add User Modal
    const addUserBackdrop = document.getElementById('addUserModalBackdrop');
    if (addUserBackdrop) {
        addUserBackdrop.addEventListener('click', closeAddUserModal);
    }
    
    // Edit User Modal
    const editUserBackdrop = document.getElementById('editUserModalBackdrop');
    if (editUserBackdrop) {
        editUserBackdrop.addEventListener('click', closeEditUserModal);
    }
    
    // Role change handler for License ID and Specialization fields (Add form)
    const addRoleSelect = document.getElementById('add_role');
    if (addRoleSelect) {
        addRoleSelect.addEventListener('change', toggleLicenseIdField);
    }
    
    // Role change handler for Edit form
    const editRoleSelect = document.getElementById('edit_role');
    if (editRoleSelect) {
        editRoleSelect.addEventListener('change', toggleEditLicenseIdField);
    }
    
    // Form validation
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            // Re-check field requirements before submit
            toggleLicenseIdField();
            
            // Basic frontend validation
            const role = document.getElementById('add_role').value;
            const specializationSelect = document.getElementById('add_specialization');
            
            // Validate specialization for Doctor
            if (role === 'doctor' && (!specializationSelect || !specializationSelect.value.trim())) {
                e.preventDefault();
                alert('Specialization is required for Doctor role.');
                if (specializationSelect) specializationSelect.focus();
                return false;
            }
            
            // License ID is auto-generated, so validation is handled by required attribute
        });
    }
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const userRows = document.querySelectorAll('.table-row');
            
            userRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?= $this->endSection() ?>
