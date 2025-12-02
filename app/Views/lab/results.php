<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<style>
/* Lab Results Table Alignment */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem 0.75rem;
    text-align: left;
    vertical-align: middle;
}

.data-table th {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}

.data-table td {
    border-bottom: 1px solid #f1f5f9;
}

.data-table tbody tr:hover {
    background: #f8fafc;
}

/* Status cell alignment */
.lab-status-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    align-items: flex-start;
}

/* Badge styles */
.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 4px;
    text-transform: uppercase;
}

.bg-warning {
    background: #fef3c7;
    color: #92400e;
}

.bg-success {
    background: #d1fae5;
    color: #065f46;
}

.bg-danger {
    background: #fee2e2;
    color: #991b1b;
}

.bg-info {
    background: #dbeafe;
    color: #1e40af;
}

.bg-secondary {
    background: #e2e8f0;
    color: #475569;
}
</style>

<section class="panel lab-section">
    <header class="panel-header lab-header">
        <div class="page-header-content">
            <div>
                <h2 class="page-title">
                    <span>📋</span>
                    Test Results
                </h2>
                <p class="page-subtitle lab-role-description">
                    Laboratory Staff (Manage test requests, enter results)
                </p>
            </div>
        </div>
    </header>
</section>

<!-- Context Section -->
<section class="panel panel-spaced lab-section">
    <div class="lab-context">
        <p class="lab-context-text">Laboratory staff can enter, update, and finalize test results for each patient.</p>
    </div>
</section>

<!-- Statistics Cards -->
<section class="panel panel-spaced">
    <div class="kpi-grid">
        <?php
        $totalResults = count($results ?? []);
        $pendingCount = count($pending_requests ?? []);
        $completedCount = 0;
        $criticalCount = 0;
        
        foreach ($results ?? [] as $result) {
            if (($result['status'] ?? 'pending') === 'completed') $completedCount++;
            if (($result['critical_flag'] ?? 0) == 1) $criticalCount++;
        }
        ?>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Total Results</div>
                <div class="kpi-value"><?= $totalResults ?></div>
                <div class="kpi-change kpi-positive">All results</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Pending Entry</div>
                <div class="kpi-value"><?= $pendingCount ?></div>
                <div class="kpi-change kpi-warning">Awaiting entry</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Completed</div>
                <div class="kpi-value"><?= $completedCount ?></div>
                <div class="kpi-change kpi-positive">Finalized</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-content">
                <div class="kpi-label">Critical Results</div>
                <div class="kpi-value"><?= $criticalCount ?></div>
                <div class="kpi-change kpi-negative">Requires attention</div>
            </div>
        </div>
    </div>
</section>

<!-- Enter Results Section -->
<?php if (!empty($pending_requests)): ?>
<section class="panel panel-spaced lab-section">
    <header class="panel-header lab-header">
        <h3 class="h5 mb-0 fw-bold">Enter Test Results</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr class="lab-row">
                        <th class="lab-cell">Request ID</th>
                        <th class="lab-cell">Patient</th>
                        <th class="lab-cell">Patient Type</th>
                        <th class="lab-cell">Test Type</th>
                        <th class="lab-cell">Priority</th>
                        <th class="lab-cell">Date Requested</th>
                        <th class="lab-cell">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_requests as $request): ?>
                        <?php
                        $priority = $request['priority'] ?? 'normal';
                        $priorityClass = match($priority) {
                            'low' => 'bg-secondary',
                            'normal' => 'bg-info',
                            'high' => 'bg-warning',
                            'critical' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        ?>
                        <tr class="lab-row">
                            <td class="lab-cell"><strong>#<?= str_pad((string)($request['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></strong></td>
                            <td class="lab-cell"><?= esc($request['patient_name'] ?? 'N/A') ?></td>
                            <td class="lab-cell">
                                <?php
                                // Determine patient type: if admission_id exists = INPATIENT, else = OUTPATIENT
                                $hasAdmission = !empty($request['admission_id']);
                                $patientType = $hasAdmission ? 'inpatient' : 'outpatient';
                                // Fallback to patient_type from patients table if available
                                if (!empty($request['patient_type'])) {
                                    $patientType = strtolower($request['patient_type']);
                                }
                                $patientTypeClass = ($patientType === 'inpatient') ? 'bg-primary' : 'bg-info';
                                ?>
                                <span class="badge <?= $patientTypeClass ?>"><?= ucfirst($patientType) ?></span>
                            </td>
                            <td class="lab-cell"><?= esc($request['test_type'] ?? '—') ?></td>
                            <td class="lab-cell">
                                <span class="badge <?= $priorityClass ?>"><?= ucfirst($priority) ?></span>
                            </td>
                            <td class="lab-cell"><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                            <td class="lab-cell">
                                <?php
                                    $displayTestType = $request['test_type'] ?? '—';
                                    $displayPatient = $request['patient_name'] ?? '—';
                                    $displayDate = !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—';
                                ?>
                                <button type="button"
                                    class="btn btn-sm btn-primary"
                                    data-request-button="<?= (int)$request['id'] ?>"
                                    data-test-type="<?= esc($displayTestType, 'attr') ?>"
                                    data-patient-name="<?= esc($displayPatient, 'attr') ?>"
                                    data-requested-date="<?= esc($displayDate, 'attr') ?>"
                                    onclick="openResultModalFromButton(this)">
                                    Enter Result
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Test Results Table -->
<section class="panel panel-spaced lab-section">
    <header class="panel-header lab-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0 fw-bold">All Test Results</h3>
            <select class="form-select form-select-sm shadow-sm" style="max-width: 150px;" onchange="filterResults(this.value)">
                <option value="">All Results</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="critical">Critical</option>
            </select>
        </div>
    </header>
    
    <div class="stack">
        <div class="table-container" style="overflow-x: auto;">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 90px;">Result ID</th>
                        <th style="width: 150px;">Patient</th>
                        <th style="width: 100px;">Test Type</th>
                        <th style="width: 280px;">Result Summary</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 140px;">Released Date</th>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $result): ?>
                            <?php
                            $status = $result['status'] ?? 'pending';
                            $isCritical = ($result['critical_flag'] ?? 0) == 1;
                            
                            $statusClass = match($status) {
                                'pending' => 'bg-warning',
                                'completed' => 'bg-success',
                                default => 'bg-secondary'
                            };
                            
                            // If critical, override status display
                            if ($isCritical) {
                                $statusClass = 'bg-danger';
                                $statusText = 'Critical';
                            } else {
                                $statusText = ucfirst($status);
                            }
                            ?>
                            <tr>
                                <td><strong>#<?= str_pad((string)($result['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= esc($result['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($result['test_type'] ?? '—') ?></td>
                                <td><?= esc($result['result_summary'] ?? '—') ?></td>
                                <td>
                                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : '—' ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary" onclick="viewResult(<?= $result['id'] ?>)">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #64748b;">No test results found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Enter Result Modal -->
<div class="modal" id="resultModal" style="display: none;">
    <div class="lab-result-modal-dialog">
        <div class="lab-result-modal-card">
            <div class="lab-result-modal-header">
                <div>
                    <h3>Enter Test Result</h3>
                    <p>Review the test details, encode findings, then save the record.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeResultModal()" aria-label="Close">&times;</button>
            </div>

            <div class="lab-result-section">
                <div class="lab-result-section-title">Test Information</div>
                <div class="lab-result-info-grid">
                    <div class="lab-result-info-row">
                        <span>Test Type</span>
                        <strong id="modal_test_type">—</strong>
                    </div>
                    <div class="lab-result-info-row">
                        <span>Patient Name</span>
                        <strong id="modal_patient_name">—</strong>
                    </div>
                    <div class="lab-result-info-row">
                        <span>Request ID</span>
                        <strong id="modal_request_code">—</strong>
                    </div>
                    <div class="lab-result-info-row">
                        <span>Requested Date</span>
                        <strong id="modal_requested_date">—</strong>
                    </div>
                </div>
            </div>

            <form id="resultForm">
                <input type="hidden" name="request_id" id="modal_request_id">
                <div class="lab-result-section">
                    <div class="lab-result-section-title">Result Entry</div>
                    <label for="result_summary" class="lab-result-field-label">Result Summary <span>*</span></label>
                    <textarea class="lab-result-textarea" name="result_summary" id="result_summary" rows="3" required placeholder="Enter concise findings, observed values, or physician notes..."></textarea>

                    <label for="detailed_report" class="lab-result-field-label">Detailed Report (Optional)</label>
                    <textarea class="lab-result-textarea" name="detailed_report" id="detailed_report" rows="4" placeholder="Include method details, reference ranges, or recommendations..."></textarea>

                    <label class="lab-result-checkbox">
                        <input type="checkbox" name="critical_flag" id="critical_flag" value="1">
                        <span>Mark as Critical Result</span>
                    </label>
                </div>

                <div class="lab-result-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeResultModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Result</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterResults(filter) {
    const url = new URL(window.location.href);
    if (filter) {
        url.searchParams.set('filter', filter);
    } else {
        url.searchParams.delete('filter');
    }
    window.location.href = url.toString();
}

const modalTestType = document.getElementById('modal_test_type');
const modalPatientName = document.getElementById('modal_patient_name');
const modalRequestCode = document.getElementById('modal_request_code');
const modalRequestedDate = document.getElementById('modal_requested_date');
const resultSummaryField = document.getElementById('result_summary');
const detailedReportField = document.getElementById('detailed_report');
const criticalCheckbox = document.getElementById('critical_flag');

function openResultModalFromButton(buttonEl) {
    if (!buttonEl) return;
    const requestId = parseInt(buttonEl.getAttribute('data-request-button'), 10);
    const testType = buttonEl.getAttribute('data-test-type') || null;
    const patientName = buttonEl.getAttribute('data-patient-name') || null;
    const requestedDate = buttonEl.getAttribute('data-requested-date') || null;
    openResultModal(requestId, testType, patientName, requestedDate);
}

function openResultModal(requestId, testType = null, patientName = null, requestedDate = null) {
    if (!requestId) return;
    const modal = document.getElementById('resultModal');
    if (!modal) return;
    
    let localTestType = testType;
    let localPatientName = patientName;
    let localRequestedDate = requestedDate;
    
    if (!localTestType || !localPatientName || !localRequestedDate) {
        const fallbackButton = document.querySelector(`[data-request-button="${requestId}"]`);
        if (fallbackButton) {
            localTestType = fallbackButton.getAttribute('data-test-type') || localTestType;
            localPatientName = fallbackButton.getAttribute('data-patient-name') || localPatientName;
            localRequestedDate = fallbackButton.getAttribute('data-requested-date') || localRequestedDate;
        }
    }
    
    // Populate info display
    if (modalTestType) modalTestType.textContent = localTestType || '—';
    if (modalPatientName) modalPatientName.textContent = localPatientName || '—';
    if (modalRequestCode) modalRequestCode.textContent = `#${String(requestId).padStart(6, '0')}`;
    if (modalRequestedDate) modalRequestedDate.textContent = localRequestedDate || '—';
    
    // Set request ID
    document.getElementById('modal_request_id').value = requestId;
    
    // Reset form
    const form = document.getElementById('resultForm');
    if (form) form.reset();
    document.getElementById('modal_request_id').value = requestId;
    if (resultSummaryField) {
        resultSummaryField.value = '';
        autoResizeTextarea(resultSummaryField);
    }
    if (detailedReportField) {
        detailedReportField.value = '';
        autoResizeTextarea(detailedReportField);
    }
    if (criticalCheckbox) criticalCheckbox.checked = false;
    
    // Update request status to in_progress
    fetch('<?= base_url('lab/updateRequestStatus') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'request_id=' + requestId + '&status=in_progress'
    }).catch(err => console.error('Error updating status:', err));
    
    // Show modal using vanilla JavaScript
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeResultModal() {
    const modal = document.getElementById('resultModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function viewResult(resultId) {
    alert('View result #' + resultId);
}

// Handle form submission with AJAX
function autoResizeTextarea(textarea) {
    if (!textarea) return;
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 400) + 'px';
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resultForm');
    [resultSummaryField, detailedReportField].forEach(field => {
        if (field) {
            field.addEventListener('input', () => autoResizeTextarea(field));
            autoResizeTextarea(field);
        }
    });
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            const summaryValue = resultSummaryField ? resultSummaryField.value.trim() : '';
            const isCritical = criticalCheckbox ? criticalCheckbox.checked : false;
            if (!summaryValue) {
                alert('Result Summary is required.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }
            if (isCritical && summaryValue.length < 20) {
                alert('Critical results require at least 20 characters in the summary.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch('<?= base_url('lab/results/save') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message || 'Test result saved successfully!');
                    // Close modal
                    closeResultModal();
                    // Reload page without URL parameters to prevent modal from reopening
                    window.location.href = '<?= base_url('lab/results') ?>';
                } else {
                    alert(result.message || 'Error saving result. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
    
    // Auto-open modal if request_id is in URL
    <?php if (!empty($open_request_id)): ?>
    setTimeout(function() {
        openResultModal(<?= $open_request_id ?>);
    }, 500);
    <?php endif; ?>
    
    // Close modal when clicking outside
    const modal = document.getElementById('resultModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeResultModal();
            }
        });
    }
});
</script>

<?= $this->endSection() ?>

