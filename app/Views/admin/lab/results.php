<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <h2>Laboratory Test Results Directory</h2>
        <p>Review released results, audit entries, and monitor critical findings.</p>
    </header>
    <div class="stack">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Test Type</th>
                        <th>Result Summary</th>
                        <th>Released By</th>
                        <th>Date Released</th>
                        <th>Status</th>
                        <th>Critical</th>
                        <th>Audit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?= esc($result['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($result['test_type'] ?? '—') ?></td>
                                <td><?= esc($result['result_summary'] ?? '—') ?></td>
                                <td><?= esc($result['released_by_name'] ?? '—') ?></td>
                                <td><?= !empty($result['released_at']) ? date('M j, Y g:i A', strtotime($result['released_at'])) : '—' ?></td>
                                <td><span class="badge badge-status badge-<?= esc($result['status']) ?>"><?= ucfirst(str_replace('_', ' ', $result['status'] ?? 'draft')) ?></span></td>
                                <td><?= !empty($result['critical_flag']) ? '<span class="badge badge-critical">Yes</span>' : 'No' ?></td>
                                <td>
                                    <details>
                                        <summary class="btn-link">Audit</summary>
                                        <div class="action-menu">
                                            <form action="<?= base_url('admin/lab/results/audit/' . $result['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <label>Status</label>
                                                <select name="status">
                                                    <?php foreach (['released', 'audited', 'rejected'] as $status): ?>
                                                        <option value="<?= $status ?>" <?= ($result['status'] ?? 'released') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label>Notes</label>
                                                <textarea name="notes" rows="3" placeholder="Audit notes..."><?= esc($result['audit_notes'] ?? '') ?></textarea>
                                                <button type="submit" class="btn-secondary">Save</button>
                                            </form>
                                            <?php if (!empty($result['audited_by_name'])): ?>
                                                <small>Last audited by <?= esc($result['audited_by_name']) ?> on <?= !empty($result['audited_at']) ? date('M j, Y g:i A', strtotime($result['audited_at'])) : '—' ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No lab results available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
.table-container { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 0.75rem; border-bottom: 1px solid #e2e8f0; text-align: left; }
.badge { display: inline-block; padding: 0.25rem 0.55rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-status.badge-released { background: #dcfce7; color: #15803d; }
.badge-status.badge-audited { background: #c7d2fe; color: #3730a3; }
.badge-status.badge-rejected { background: #fee2e2; color: #b91c1c; }
.badge-status.badge-draft { background: #f1f5f9; color: #475569; }
.badge-critical { background: #fee2e2; color: #b91c1c; }
.alert { padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid transparent; }
.alert-success { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
details { position: relative; }
details summary { cursor: pointer; }
.action-menu { margin-top: 0.5rem; display: grid; gap: 0.75rem; }
.action-menu form { display: grid; gap: 0.5rem; }
.action-menu textarea, .action-menu select { width: 100%; padding: 0.5rem; border: 1px solid #cbd5f5; border-radius: 0.5rem; font-size: 0.9rem; }
.btn-secondary { background: #e2e8f0; border: none; padding: 0.4rem 0.75rem; border-radius: 0.5rem; cursor: pointer; }
.btn-link { color: #1d4ed8; cursor: pointer; text-decoration: underline; background: none; border: none; padding: 0; font-size: 0.9rem; }
</style>
<?= $this->endSection() ?>
