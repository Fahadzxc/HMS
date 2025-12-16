<?= $this->extend('template') ?>

<?php
// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Backup Management</h2>
        <p>Upload and manage system backup files</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Backups</div>
                    <div class="kpi-value"><?= count($backup_files ?? []) ?></div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Size</div>
                    <div class="kpi-value">
                        <?php
                        $totalSize = 0;
                        foreach ($backup_files ?? [] as $file) {
                            $totalSize += $file['size'];
                        }
                        echo formatFileSize($totalSize);
                        ?>
                    </div>
                    <div class="kpi-change kpi-positive">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Upload Backup File</h2>
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

        <form action="<?= base_url('it/backup/upload') ?>" method="post" enctype="multipart/form-data" class="card">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-field" style="grid-column: 1 / -1;">
                    <label>Select Backup File <span class="req">*</span></label>
                    <input type="file" name="backup_file" id="backup_file" accept=".sql,.zip,.gz,.tar,.bak,.db" required>
                    <small style="color: #666; margin-top: 5px; display: block;">
                        Allowed file types: SQL, ZIP, GZ, TAR, BAK, DB (Max size: 100MB)
                    </small>
                    <div class="error" data-error-for="backup_file"></div>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-primary">Upload Backup</button>
            </div>
        </form>
    </div>
</section>

<section class="panel panel-spaced">
    <header class="panel-header">
        <h2>Backup Files</h2>
        <div class="row between">
            <input type="text" placeholder="Search backups..." class="search-input" id="searchInput">
        </div>
    </header>
    
    <div class="stack">
        <!-- Table Header -->
        <div class="card table-header">
            <div class="row between">
                <div class="col-name" style="flex: 2;">File Name</div>
                <div class="col-age">Size</div>
                <div class="col-doctor">Upload Date</div>
                <div class="col-actions">Actions</div>
            </div>
        </div>

        <!-- Backup File Rows -->
        <?php if (!empty($backup_files)): ?>
            <div id="backupFilesTable">
                <?php foreach ($backup_files as $file): ?>
                    <div class="card table-row backup-row">
                        <div class="row between">
                            <div class="col-name" style="flex: 2;">
                                <div class="patient-info">
                                    <div class="patient-avatar">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M14 2V8H20" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <div class="patient-details">
                                        <strong><?= htmlspecialchars($file['name']) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-age">
                                <?= formatFileSize($file['size']) ?>
                            </div>
                            <div class="col-doctor">
                                <?= date('M d, Y H:i', strtotime($file['date'])) ?>
                            </div>
                            <div class="col-actions">
                                <a href="<?= base_url('it/backup/download?file=' . urlencode($file['name'])) ?>" class="action-link">Download</a>
                                <a href="#" class="action-link" onclick="deleteBackup('<?= htmlspecialchars($file['name']) ?>'); return false;">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <p class="text-center">No backup files found. Upload a backup file to get started.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Delete Backup
function deleteBackup(fileName) {
    if (confirm('Are you sure you want to delete this backup file? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('it/backup/delete') ?>';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '<?= csrf_token() ?>';
        csrfToken.value = '<?= csrf_hash() ?>';
        
        const fileNameInput = document.createElement('input');
        fileNameInput.type = 'hidden';
        fileNameInput.name = 'file_name';
        fileNameInput.value = fileName;
        
        form.appendChild(csrfToken);
        form.appendChild(fileNameInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const backupRows = document.querySelectorAll('.backup-row');
            
            backupRows.forEach(row => {
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
