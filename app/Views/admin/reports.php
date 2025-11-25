<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Reports
                </h2>
                <p class="page-subtitle">
                    Comprehensive reports for all roles and departments
                    <span class="date-text"> • Date: <?= date('F j, Y') ?></span>
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Report Filters -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Report Filters</h2>
    </header>
    <form method="GET" action="<?= base_url('admin/reports') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Role/Department</label>
            <select name="role" id="roleFilter" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="all" <?= ($selected_role ?? 'all') === 'all' ? 'selected' : '' ?>>All Roles</option>
                <option value="nurse" <?= ($selected_role ?? '') === 'nurse' ? 'selected' : '' ?>>Nurse</option>
                <option value="doctor" <?= ($selected_role ?? '') === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                <option value="laboratory" <?= ($selected_role ?? '') === 'laboratory' ? 'selected' : '' ?>>Laboratory</option>
                <option value="pharmacy" <?= ($selected_role ?? '') === 'pharmacy' ? 'selected' : '' ?>>Pharmacy</option>
                <option value="reception" <?= ($selected_role ?? '') === 'reception' ? 'selected' : '' ?>>Reception</option>
                <option value="accounts" <?= ($selected_role ?? '') === 'accounts' ? 'selected' : '' ?>>Accounts</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date From</label>
            <input type="date" name="date_from" value="<?= esc($date_from ?? date('Y-m-01')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Date To</label>
            <input type="date" name="date_to" value="<?= esc($date_to ?? date('Y-m-d')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
        </div>
        <div style="flex: 0 0 auto;">
            <button type="submit" style="padding: 0.5rem 1.5rem; background: #4299e1; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500;">Apply Filters</button>
        </div>
    </form>
</section>

<?php if (($selected_role ?? 'all') === 'all' || ($selected_role ?? '') === 'nurse'): ?>
    <!-- Nurse Reports -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>👩‍⚕️ Nurse Reports</h2>
        </header>
        <?php if (!empty($nurse_reports) && is_array($nurse_reports)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Updates</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($nurse_reports['summary']['total_updates'] ?? 0) ?></div>
                </div>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Patients</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($nurse_reports['summary']['total_patients'] ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Patient</th>
                            <th>Height (cm)</th>
                            <th>Weight (kg)</th>
                            <th>BMI</th>
                            <th>Blood Pressure</th>
                            <th>Heart Rate</th>
                            <th>Temperature</th>
                            <th>Treatment Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($nurse_reports['treatment_updates'])): ?>
                            <?php foreach (array_slice($nurse_reports['treatment_updates'], 0, 50) as $update): ?>
                                <tr>
                                    <td><?= !empty($update['created_at']) ? date('M j, Y g:i A', strtotime($update['created_at'])) : '—' ?></td>
                                    <td><?= esc($update['patient_name'] ?? 'N/A') ?></td>
                                    <td><?= !empty($update['height']) ? number_format((float)$update['height'], 1) . ' cm' : '—' ?></td>
                                    <td><?= !empty($update['weight']) ? number_format((float)$update['weight'], 1) . ' kg' : '—' ?></td>
                                    <td><?= !empty($update['bmi']) ? number_format((float)$update['bmi'], 1) : '—' ?></td>
                                    <td><?= esc($update['blood_pressure'] ?? '—') ?></td>
                                    <td><?= !empty($update['heart_rate']) ? $update['heart_rate'] . ' bpm' : '—' ?></td>
                                    <td><?= !empty($update['temperature']) ? $update['temperature'] . ' °C' : '—' ?></td>
                                    <td><?= esc($update['notes'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">No treatment updates found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if (($selected_role ?? 'all') === 'all' || ($selected_role ?? '') === 'doctor'): ?>
    <!-- Doctor Reports -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>👨‍⚕️ Doctor Reports</h2>
        </header>
        <?php if (!empty($doctor_reports) && is_array($doctor_reports)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Appointments</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($doctor_reports['summary']['total_appointments'] ?? 0) ?></div>
                </div>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Prescriptions</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($doctor_reports['summary']['total_prescriptions'] ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $combined = [];
                        if (!empty($doctor_reports['appointments'])) {
                            foreach ($doctor_reports['appointments'] as $apt) {
                                $combined[] = ['type' => 'Appointment', 'date' => $apt['appointment_date'], 'patient' => $apt['patient_name'], 'doctor' => $apt['doctor_name'], 'status' => $apt['status'] ?? 'pending'];
                            }
                        }
                        if (!empty($doctor_reports['prescriptions'])) {
                            foreach ($doctor_reports['prescriptions'] as $rx) {
                                $combined[] = ['type' => 'Prescription', 'date' => $rx['created_at'], 'patient' => $rx['patient_name'], 'doctor' => $rx['doctor_name'], 'status' => $rx['status'] ?? 'pending'];
                            }
                        }
                        usort($combined, function($a, $b) {
                            return strtotime($b['date'] ?? '') - strtotime($a['date'] ?? '');
                        });
                        ?>
                        <?php if (!empty($combined)): ?>
                            <?php foreach (array_slice($combined, 0, 50) as $item): ?>
                                <tr>
                                    <td><?= !empty($item['date']) ? date('M j, Y g:i A', strtotime($item['date'])) : '—' ?></td>
                                    <td><?= esc($item['patient'] ?? 'N/A') ?></td>
                                    <td><?= esc($item['doctor'] ?? 'N/A') ?></td>
                                    <td><span class="badge badge-info"><?= esc($item['type']) ?></span></td>
                                    <td><span class="badge badge-<?= esc($item['status'] ?? 'pending') ?>"><?= ucfirst(esc($item['status'] ?? 'pending')) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No doctor reports found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if (($selected_role ?? 'all') === 'all' || ($selected_role ?? '') === 'laboratory'): ?>
    <!-- Laboratory Reports -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>🔬 Laboratory Reports</h2>
        </header>
        <?php if (!empty($lab_reports) && is_array($lab_reports)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Requests</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($lab_reports['summary']['total_requests'] ?? 0) ?></div>
                </div>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Results</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($lab_reports['summary']['total_results'] ?? 0) ?></div>
                </div>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Critical Results</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #dc2626;"><?= number_format($lab_reports['summary']['critical_count'] ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Test Type</th>
                            <th>Status</th>
                            <th>Result Summary</th>
                            <th>Critical</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lab_reports['test_results'])): ?>
                            <?php foreach (array_slice($lab_reports['test_results'], 0, 50) as $result): ?>
                                <tr>
                                    <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : '—' ?></td>
                                    <td><?= esc($result['patient_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($result['test_type'] ?? 'N/A') ?></td>
                                    <td><span class="badge badge-success">Completed</span></td>
                                    <td><?= esc($result['result_summary'] ?? '—') ?></td>
                                    <td>
                                        <?php if (!empty($result['critical_flag']) && $result['critical_flag'] == 1): ?>
                                            <span class="badge badge-danger">🔴 Critical</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No laboratory results found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if (($selected_role ?? 'all') === 'all' || ($selected_role ?? '') === 'pharmacy'): ?>
    <!-- Pharmacy Reports -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>💊 Pharmacy Reports</h2>
        </header>
        <?php if (!empty($pharmacy_reports) && is_array($pharmacy_reports)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Dispensed</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($pharmacy_reports['summary']['total_dispensed'] ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pharmacy_reports['dispensed_prescriptions'])): ?>
                            <?php foreach (array_slice($pharmacy_reports['dispensed_prescriptions'], 0, 50) as $rx): ?>
                                <tr>
                                    <td><?= !empty($rx['updated_at']) ? date('M j, Y g:i A', strtotime($rx['updated_at'])) : '—' ?></td>
                                    <td><?= esc($rx['patient_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($rx['doctor_name'] ?? 'N/A') ?></td>
                                    <td><span class="badge badge-success"><?= ucfirst(esc($rx['status'] ?? 'dispensed')) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No pharmacy reports found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if (($selected_role ?? 'all') === 'all' || ($selected_role ?? '') === 'reception'): ?>
    <!-- Reception Reports -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>📋 Reception Reports</h2>
        </header>
        <?php if (!empty($reception_reports) && is_array($reception_reports)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">New Patients</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($reception_reports['summary']['total_new_patients'] ?? 0) ?></div>
                </div>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Appointments</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($reception_reports['summary']['total_appointments'] ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient Name</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $combined = [];
                        if (!empty($reception_reports['new_patients'])) {
                            foreach ($reception_reports['new_patients'] as $pt) {
                                $combined[] = ['type' => 'New Patient', 'date' => $pt['created_at'], 'name' => $pt['full_name'], 'status' => 'Registered'];
                            }
                        }
                        if (!empty($reception_reports['appointments'])) {
                            foreach ($reception_reports['appointments'] as $apt) {
                                $combined[] = ['type' => 'Appointment', 'date' => $apt['appointment_date'], 'name' => $apt['patient_name'], 'status' => $apt['status'] ?? 'pending'];
                            }
                        }
                        usort($combined, function($a, $b) {
                            return strtotime($b['date'] ?? '') - strtotime($a['date'] ?? '');
                        });
                        ?>
                        <?php if (!empty($combined)): ?>
                            <?php foreach (array_slice($combined, 0, 50) as $item): ?>
                                <tr>
                                    <td><?= !empty($item['date']) ? date('M j, Y g:i A', strtotime($item['date'])) : '—' ?></td>
                                    <td><?= esc($item['name'] ?? 'N/A') ?></td>
                                    <td><span class="badge badge-info"><?= esc($item['type']) ?></span></td>
                                    <td><span class="badge badge-<?= esc($item['status'] ?? 'pending') ?>"><?= ucfirst(esc($item['status'] ?? 'pending')) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No reception reports found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if (($selected_role ?? 'all') === 'all' || ($selected_role ?? '') === 'accounts'): ?>
    <!-- Accounts Reports -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <h2>💰 Accounts Reports</h2>
        </header>
        <?php if (!empty($accounts_reports) && is_array($accounts_reports)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Revenue</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #10b981;">₱<?= number_format($accounts_reports['summary']['total_revenue'] ?? 0, 2) ?></div>
                </div>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Bills</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($accounts_reports['summary']['total_bills'] ?? 0) ?></div>
                </div>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Payments</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #1e293b;"><?= number_format($accounts_reports['summary']['total_payments'] ?? 0) ?></div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Bill Number</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts_reports['payments'])): ?>
                            <?php foreach (array_slice($accounts_reports['payments'], 0, 50) as $payment): ?>
                                <tr>
                                    <td><?= !empty($payment['payment_date']) ? date('M j, Y g:i A', strtotime($payment['payment_date'])) : '—' ?></td>
                                    <td><?= esc($payment['patient_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($payment['bill_number'] ?? 'N/A') ?></td>
                                    <td><strong>₱<?= number_format($payment['amount'] ?? 0, 2) ?></strong></td>
                                    <td><span class="badge badge-info"><?= ucfirst(esc($payment['payment_method'] ?? 'cash')) ?></span></td>
                                    <td><span class="badge badge-success"><?= ucfirst(esc($payment['status'] ?? 'completed')) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No accounts reports found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?= $this->endSection() ?>

