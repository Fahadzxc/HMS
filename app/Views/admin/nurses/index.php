<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<section class="panel">
    <header class="panel-header">
        <h2>Nurse Directory</h2>
        <p>Review nurse profiles and assignments</p>
    </header>
    <div class="stack">
        <div class="card table-header">
            <div class="row between">
                <span>All Nurses</span>
                <span><?= count($nurses ?? []) ?> total</span>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Shift</th>
                        <th>Ward</th>
                        <th>Status</th>
                        <th>Email</th>
                        <th>License #</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($nurses)): ?>
                        <?php foreach ($nurses as $nurse): ?>
                            <tr>
                                <td><?= esc($nurse['name'] ?? 'N/A') ?></td>
                                <td><?= esc($nurse['department'] ?? '—') ?></td>
                                <td><?= esc(ucfirst($nurse['shift'] ?? '—')) ?></td>
                                <td><?= esc($nurse['ward_assignment'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge<?= match($nurse['status'] ?? '') {
                                        'active' => '-success',
                                        'inactive' => '-secondary',
                                        default => '-info'
                                    } ?>">
                                        <?= ucfirst($nurse['status'] ?? 'unknown') ?>
                                    </span>
                                </td>
                                <td><?= esc($nurse['email'] ?? '—') ?></td>
                                <td><?= esc($nurse['license_number'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No nurses found.</td>
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
.badge-secondary { background-color: #e2e8f0; color: #4a5568; }
.badge-info { background-color: #bee3f8; color: #2a4365; }
</style>
<?= $this->endSection() ?>
