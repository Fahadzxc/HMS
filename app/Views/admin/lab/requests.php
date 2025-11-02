<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <h2>Laboratory Test Requests</h2>
        <p>Monitor and manage all laboratory test requests across branches.</p>
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
                        <th>Doctor</th>
                        <th>Test Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date Requested</th>
                        <th>Assigned Staff</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?= esc($request['patient_name'] ?? 'N/A') ?></td>
                                <td><?= esc($request['doctor_name'] ?? 'N/A') ?></td>
                                <td><?= esc($request['test_type'] ?? '—') ?></td>
                                <td><span class="badge badge-priority badge-<?= esc($request['priority']) ?>"><?= ucfirst($request['priority'] ?? 'normal') ?></span></td>
                                <td><span class="badge badge-status badge-<?= esc($request['status']) ?>"><?= ucfirst(str_replace('_', ' ', $request['status'] ?? 'pending')) ?></span></td>
                                <td><?= !empty($request['requested_at']) ? date('M j, Y g:i A', strtotime($request['requested_at'])) : '—' ?></td>
                                <td><?= esc($request['staff_name'] ?? 'Unassigned') ?></td>
                                <td>
                                    <details>
                                        <summary class="btn-link">Actions</summary>
                                        <div class="action-menu">
                                            <form action="<?= base_url('admin/lab/requests/reassign/' . $request['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <label>Reassign Staff</label>
                                                <select name="staff_id">
                                                    <option value="">Select staff</option>
                                                    <?php foreach ($staff as $member): ?>
                                                        <option value="<?= $member['id'] ?>" <?= (!empty($request['assigned_staff_id']) && (int)$request['assigned_staff_id'] === (int)$member['id']) ? 'selected' : '' ?>>
                                                            <?= esc($member['name'] ?? 'Staff') ?> (<?= esc($member['role'] ?? '—') ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn-secondary">Update</button>
                                            </form>
                                            <form action="<?= base_url('admin/lab/requests/change-priority/' . $request['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <label>Change Priority</label>
                                                <select name="priority">
                                                    <?php foreach (['low', 'normal', 'high', 'critical'] as $priority): ?>
                                                        <option value="<?= $priority ?>" <?= ($request['priority'] ?? 'normal') === $priority ? 'selected' : '' ?>><?= ucfirst($priority) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn-secondary">Update</button>
                                            </form>
                                            <form action="<?= base_url('admin/lab/requests/force-complete/' . $request['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <label>Force Complete</label>
                                                <textarea name="reason" rows="3" placeholder="Reason for override" required></textarea>
                                                <button type="submit" class="btn-danger">Force Complete</button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No lab test requests found.</td>
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
.badge-status.badge-pending { background: #e0f2fe; color: #0369a1; }
.badge-status.badge-in_progress { background: #ede9fe; color: #5b21b6; }
.badge-status.badge-completed { background: #dcfce7; color: #15803d; }
.badge-status.badge-cancelled { background: #fee2e2; color: #b91c1c; }
.badge-status.badge-critical { background: #fee2e2; color: #b91c1c; }
.badge-priority.badge-low { background: #f1f5f9; color: #475569; }
.badge-priority.badge-normal { background: #dbeafe; color: #1d4ed8; }
.badge-priority.badge-high { background: #fef3c7; color: #b45309; }
.badge-priority.badge-critical { background: #fee2e2; color: #b91c1c; }
.alert { padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid transparent; }
.alert-success { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
details { position: relative; }
details summary { cursor: pointer; }
.action-menu { margin-top: 0.5rem; display: grid; gap: 0.75rem; }
.action-menu form { display: grid; gap: 0.5rem; }
.action-menu textarea, .action-menu select { width: 100%; padding: 0.5rem; border: 1px solid #cbd5f5; border-radius: 0.5rem; font-size: 0.9rem; }
.btn-secondary { background: #e2e8f0; border: none; padding: 0.4rem 0.75rem; border-radius: 0.5rem; cursor: pointer; }
.btn-danger { background: #dc2626; color: #fff; border: none; padding: 0.4rem 0.75rem; border-radius: 0.5rem; cursor: pointer; }
.btn-link { color: #1d4ed8; cursor: pointer; text-decoration: underline; background: none; border: none; padding: 0; font-size: 0.9rem; }
</style>
<?= $this->endSection() ?>
