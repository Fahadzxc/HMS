<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📊</span>
                    Nurse Reports
                </h2>
                <p class="page-subtitle">
                    View your treatment updates, vital signs, and patient assignments
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
    <form method="GET" action="<?= base_url('nurse/reports') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Report Type</label>
            <select name="type" id="reportType" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                <option value="treatment_updates" <?= ($report_type ?? 'treatment_updates') === 'treatment_updates' ? 'selected' : '' ?>>Treatment Updates</option>
                <option value="vital_signs" <?= ($report_type ?? '') === 'vital_signs' ? 'selected' : '' ?>>Vital Signs</option>
                <option value="patient_assignments" <?= ($report_type ?? '') === 'patient_assignments' ? 'selected' : '' ?>>Patient Assignments</option>
                <option value="all" <?= ($report_type ?? '') === 'all' ? 'selected' : '' ?>>All Reports</option>
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

<!-- Summary Cards -->
<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Summary</h2>
    </header>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Updates</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_updates'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Total Patients</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_patients'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Vital Checks</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['total_vital_checks'] ?? 0) ?></div>
        </div>
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Avg Vitals/Day</div>
            <div style="font-size: 2rem; font-weight: 600; color: #1e293b;"><?= number_format($summary['avg_vitals_per_day'] ?? 0, 2) ?></div>
        </div>
    </div>
</section>

<?php if (($report_type ?? 'treatment_updates') === 'treatment_updates' || ($report_type ?? '') === 'all'): ?>
    <!-- Treatment Updates Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h2>💊 Treatment Updates Report</h2>
                    <p>Treatment updates from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
                </div>
                <button onclick="printReport('treatmentUpdatesReport')" style="padding: 0.5rem 1rem; background: #4299e1; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                    <span>🖨️</span> Print Report
                </button>
            </div>
        </header>
        <div class="table-container" id="treatmentUpdatesReport">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Treatment Notes</th>
                        <th>Medications Given</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($treatment_updates)): ?>
                        <?php foreach ($treatment_updates as $update): ?>
                            <tr>
                                <td><?= !empty($update['created_at']) ? date('M j, Y g:i A', strtotime($update['created_at'])) : '—' ?></td>
                                <td><strong><?= esc($update['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($update['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($update['treatment_notes'] ?? '—') ?></td>
                                <td><?= esc($update['medications_given'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No treatment updates found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'treatment_updates') === 'vital_signs' || ($report_type ?? '') === 'all'): ?>
    <!-- Vital Signs Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h2>📈 Vital Signs Report</h2>
                    <p>Vital signs from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
                </div>
                <button onclick="printReport('vitalSignsReport')" style="padding: 0.5rem 1rem; background: #4299e1; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                    <span>🖨️</span> Print Report
                </button>
            </div>
        </header>
        <div class="table-container" id="vitalSignsReport">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Height (cm)</th>
                        <th>Weight (kg)</th>
                        <th>BMI</th>
                        <th>Blood Pressure</th>
                        <th>Heart Rate</th>
                        <th>Temperature</th>
                        <th>O2 Saturation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($treatment_updates)): ?>
                        <?php 
                        $vitalSigns = array_filter($treatment_updates, function($update) {
                            return !empty($update['height']) || !empty($update['weight']) || 
                                   !empty($update['blood_pressure']) || !empty($update['heart_rate']) ||
                                   !empty($update['temperature']) || !empty($update['oxygen_saturation']);
                        });
                        ?>
                        <?php if (!empty($vitalSigns)): ?>
                            <?php foreach ($vitalSigns as $update): ?>
                                <tr>
                                    <td><?= !empty($update['created_at']) ? date('M j, Y g:i A', strtotime($update['created_at'])) : '—' ?></td>
                                    <td><strong><?= esc($update['patient_name'] ?? 'N/A') ?></strong></td>
                                    <td><?= esc($update['patient_code'] ?? 'N/A') ?></td>
                                    <td><?= !empty($update['height']) ? number_format($update['height'], 1) . ' cm' : '—' ?></td>
                                    <td><?= !empty($update['weight']) ? number_format($update['weight'], 1) . ' kg' : '—' ?></td>
                                    <td><?= !empty($update['bmi']) ? number_format($update['bmi'], 1) : '—' ?></td>
                                    <td><?= esc($update['blood_pressure'] ?? '—') ?></td>
                                    <td><?= !empty($update['heart_rate']) ? $update['heart_rate'] . ' bpm' : '—' ?></td>
                                    <td><?= !empty($update['temperature']) ? $update['temperature'] . ' °C' : '—' ?></td>
                                    <td><?= !empty($update['oxygen_saturation']) ? $update['oxygen_saturation'] . '%' : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">No vital signs found for the selected period.</td>
                            </tr>
                        <?php endif; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No vital signs found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (($report_type ?? 'treatment_updates') === 'patient_assignments' || ($report_type ?? '') === 'all'): ?>
    <!-- Patient Assignments Report -->
    <section class="panel panel-spaced">
        <header class="panel-header">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h2>👥 Patient Assignments Report</h2>
                    <p>Patients assigned to you from <?= date('M j, Y', strtotime($date_from ?? date('Y-m-01'))) ?> to <?= date('M j, Y', strtotime($date_to ?? date('Y-m-d'))) ?></p>
                </div>
                <button onclick="printReport('patientAssignmentsReport')" style="padding: 0.5rem 1rem; background: #4299e1; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                    <span>🖨️</span> Print Report
                </button>
            </div>
        </header>
        <div class="table-container" id="patientAssignmentsReport">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Doctor</th>
                        <th>Appointment Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($patient_assignments)): ?>
                        <?php foreach ($patient_assignments as $assignment): ?>
                            <tr>
                                <td><?= !empty($assignment['appointment_date']) ? date('M j, Y', strtotime($assignment['appointment_date'])) : '—' ?></td>
                                <td><strong><?= esc($assignment['patient_name'] ?? 'N/A') ?></strong></td>
                                <td><?= esc($assignment['patient_code'] ?? 'N/A') ?></td>
                                <td><?= esc($assignment['doctor_name'] ?? 'N/A') ?></td>
                                <td><?= esc($assignment['appointment_type'] ?? 'Consultation') ?></td>
                                <td>
                                    <span class="badge badge-<?= esc($assignment['status'] ?? 'pending') ?>">
                                        <?= ucfirst(esc($assignment['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No patient assignments found for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<script>
function printReport(reportId) {
    const reportElement = document.getElementById(reportId);
    if (!reportElement) {
        alert('Report not found');
        return;
    }

    // Get report title from the header
    const section = reportElement.closest('section');
    const header = section.querySelector('header h2');
    const subtitle = section.querySelector('header p');
    const reportTitle = header ? header.textContent.trim() : 'Report';
    const reportSubtitle = subtitle ? subtitle.textContent.trim() : '';

    // Create print window
    const printWindow = window.open('', '_blank');
    const tableHTML = reportElement.innerHTML;

    // Get current date/time
    const now = new Date();
    const dateStr = now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${reportTitle}</title>
            <style>
                @page {
                    size: A4 landscape;
                    margin: 1cm;
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    color: #000;
                }
                .print-header {
                    text-align: center;
                    margin-bottom: 20px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 10px;
                }
                .print-header h1 {
                    margin: 0;
                    font-size: 24px;
                    color: #000;
                }
                .print-header .subtitle {
                    margin: 5px 0;
                    font-size: 14px;
                    color: #666;
                }
                .print-header .date {
                    margin-top: 5px;
                    font-size: 12px;
                    color: #666;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-size: 11px;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                }
                .badge {
                    display: inline-block;
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-size: 10px;
                    font-weight: 500;
                }
                .badge-info {
                    background-color: #dbeafe;
                    color: #1e40af;
                }
                .badge-pending {
                    background-color: #fef3c7;
                    color: #92400e;
                }
                .badge-completed {
                    background-color: #d1fae5;
                    color: #065f46;
                }
                .badge-cancelled {
                    background-color: #fee2e2;
                    color: #991b1b;
                }
                .badge-active {
                    background-color: #d1fae5;
                    color: #065f46;
                }
                .text-center {
                    text-align: center;
                }
                .text-muted {
                    color: #666;
                }
                .print-footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                    border-top: 1px solid #ccc;
                    padding-top: 10px;
                }
                @media print {
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>Hospital Management System</h1>
                <div class="subtitle">${reportTitle}</div>
                ${reportSubtitle ? `<div class="subtitle">${reportSubtitle}</div>` : ''}
                <div class="date">Printed on ${dateStr} at ${timeStr}</div>
            </div>
            ${tableHTML}
            <div class="print-footer">
                <p>This is a computer-generated report.</p>
            </div>
        </body>
        </html>
    `);

    printWindow.document.close();
    
    // Wait for content to load, then print
    setTimeout(() => {
        printWindow.print();
    }, 250);
}
</script>

<?= $this->endSection() ?>

