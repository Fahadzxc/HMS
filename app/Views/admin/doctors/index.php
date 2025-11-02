<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <h2>Doctors Directory</h2>
        <p>Review doctor profiles and assignments</p>
    </header>
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>All Doctors</span>
                <span><?= count($doctors ?? []) ?> total</span>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Branch</th>
                        <th>Contact</th>
                        <th>License #</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($doctors)): ?>
                        <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><?= esc($doctor['name'] ?? 'N/A') ?></td>
                                <td><?= esc($doctor['specialization'] ?? '—') ?></td>
                                <td><?= esc($doctor['department'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge<?= match($doctor['status'] ?? '') {
                                        'active' => '-success',
                                        'on_leave' => '-warning',
                                        'inactive' => '-secondary',
                                        default => '-info'
                                    } ?>">
                                        <?= ucfirst($doctor['status'] ?? 'unknown') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $branchName = '—';
                                        if (!empty($doctor['branch_id']) && !empty($branches)) {
                                            foreach ($branches as $branch) {
                                                if ((int)$branch['id'] === (int)$doctor['branch_id']) {
                                                    $branchName = esc($branch['name']);
                                                    break;
                                                }
                                            }
                                        }
                                        echo $branchName;
                                    ?>
                                </td>
                                <td><?= esc($doctor['contact_number'] ?? '—') ?></td>
                                <td><?= esc($doctor['license_number'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No doctors found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success { background-color: #c6f6d5; color: #22543d; }
.badge-warning { background-color: #fef5e7; color: #744210; }
.badge-secondary { background-color: #e2e8f0; color: #4a5568; }
.badge-info { background-color: #bee3f8; color: #2a4365; }
</style>
<?= $this->endSection() ?>
