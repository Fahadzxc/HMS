<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<!-- IT System Status page -->
<section class="panel">
    <header class="panel-header">
        <h2>System Status</h2>
        <p>Real-time monitoring of system components and health metrics.</p>
    </header>
    <div class="stack">
        <div class="actions-grid">
            <div class="action-tile">
                <span>System Status</span>
                <?php
                $statusColor = '#10b981'; // normal/online
                $statusText = 'Normal';
                if ($overallStatus === 'degraded') {
                    $statusColor = '#f59e0b';
                    $statusText = 'Degraded';
                } elseif ($overallStatus === 'down') {
                    $statusColor = '#ef4444';
                    $statusText = 'Down';
                } elseif ($overallStatus === 'maintenance') {
                    $statusColor = '#6366f1';
                    $statusText = 'Under Maintenance';
                }
                ?>
                <strong style="color: <?= $statusColor ?>">
                    <?= $statusText ?>
                </strong>
            </div>
            <div class="action-tile">
                <span>Active Users</span>
                <strong><?= $activeUsers ?></strong>
            </div>
            <div class="action-tile">
                <span>Pending Tickets</span>
                <strong><?= $pendingTickets ?></strong>
            </div>
            <div class="action-tile">
                <span>Last Backup</span>
                <strong><?= $lastBackup ?></strong>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>System Health</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Status</th>
                        <th>Uptime Performance</th>
                        <th>Last Check</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($systemHealth as $component): ?>
                    <tr>
                        <td><?= esc($component['name']) ?></td>
                        <td>
                            <?php
                            $badgeClass = 'badge-success';
                            $statusText = 'Normal';
                            if ($component['status'] === 'degraded') {
                                $badgeClass = 'badge-warning';
                                $statusText = 'Degraded';
                            } elseif ($component['status'] === 'offline') {
                                $badgeClass = 'badge-danger';
                                $statusText = 'Down';
                            }
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                        </td>
                        <td><?= esc($component['uptime']) ?></td>
                        <td><?= esc($component['last_check']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>System Information</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <tbody>
                    <tr>
                        <td><strong>PHP Version</strong></td>
                        <td><?= PHP_VERSION ?></td>
                    </tr>
                    <tr>
                        <td><strong>CodeIgniter Version</strong></td>
                        <td><?= \CodeIgniter\CodeIgniter::CI_VERSION ?></td>
                    </tr>
                    <tr>
                        <td><strong>Server Software</strong></td>
                        <td><?= esc($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Database Server</strong></td>
                        <td><?= esc($dbInfo['version'] ?? 'Unknown') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Server Time</strong></td>
                        <td><?= date('Y-m-d H:i:s') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Memory Usage</strong></td>
                        <td><?= number_format(memory_get_usage(true) / 1024 / 1024, 2) ?> MB</td>
                    </tr>
                    <tr>
                        <td><strong>Peak Memory Usage</strong></td>
                        <td><?= number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) ?> MB</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php if (!empty($itStaffStatus)): ?>
<section class="panel">
    <header class="panel-header">
        <h3>IT Staff Status</h3>
        <p>Operational status ng IT staff based sa real-time activity</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Operational Status</th>
                        <th>Current Tasks</th>
                        <th>Task Status</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itStaffStatus as $staff): ?>
                    <tr>
                        <td>
                            <strong><?= esc($staff['name']) ?></strong><br>
                            <small style="color: #6b7280;"><?= esc($staff['email']) ?></small>
                        </td>
                        <td>
                            <?php
                            $opStatus = $staff['operational_status'];
                            $badgeClass = 'badge-secondary';
                            $statusText = 'Offline';
                            
                            if ($opStatus === 'active') {
                                $badgeClass = 'badge-success';
                                $statusText = 'Online / Active';
                            } elseif ($opStatus === 'idle') {
                                $badgeClass = 'badge-info';
                                $statusText = 'Online / Idle';
                            } elseif ($opStatus === 'offline') {
                                $badgeClass = 'badge-secondary';
                                $statusText = 'Offline';
                            }
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                        </td>
                        <td>
                            <strong><?= $staff['current_tasks'] ?></strong> task(s)
                        </td>
                        <td>
                            <?php if (!empty($staff['tasks'])): ?>
                                <?php foreach (array_slice($staff['tasks'], 0, 2) as $task): ?>
                                    <?php
                                    $taskBadgeClass = 'badge-warning';
                                    $taskStatusText = ucfirst(str_replace('_', ' ', $task['status']));
                                    
                                    if ($task['status'] === 'resolved' || $task['status'] === 'closed') {
                                        $taskBadgeClass = 'badge-success';
                                    } elseif ($task['status'] === 'in_progress') {
                                        $taskBadgeClass = 'badge-primary';
                                    } elseif ($task['status'] === 'assigned') {
                                        $taskBadgeClass = 'badge-info';
                                    } elseif ($task['status'] === 'on_hold') {
                                        $taskBadgeClass = 'badge-warning';
                                    } elseif ($task['status'] === 'escalated') {
                                        $taskBadgeClass = 'badge-danger';
                                    }
                                    ?>
                                    <span class="badge <?= $taskBadgeClass ?>" style="margin-bottom: 4px; display: inline-block;">
                                        <?= $taskStatusText ?>
                                    </span><br>
                                <?php endforeach; ?>
                                <?php if (count($staff['tasks']) > 2): ?>
                                    <small>+<?= count($staff['tasks']) - 2 ?> more</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-secondary">Idle</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($staff['last_activity']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($systemTasks)): ?>
<section class="panel">
    <header class="panel-header">
        <h3>System Tasks / Tickets</h3>
        <p>Current tasks and tickets sa system</p>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($systemTasks as $task): ?>
                    <tr>
                        <td>#<?= $task['id'] ?></td>
                        <td><?= esc($task['title']) ?></td>
                        <td>
                            <?php
                            $taskBadgeClass = 'badge-warning';
                            $taskStatusText = ucfirst(str_replace('_', ' ', $task['status']));
                            
                            if ($task['status'] === 'resolved' || $task['status'] === 'closed') {
                                $taskBadgeClass = 'badge-success';
                            } elseif ($task['status'] === 'in_progress') {
                                $taskBadgeClass = 'badge-primary';
                            } elseif ($task['status'] === 'assigned') {
                                $taskBadgeClass = 'badge-info';
                            } elseif ($task['status'] === 'on_hold') {
                                $taskBadgeClass = 'badge-warning';
                            } elseif ($task['status'] === 'escalated') {
                                $taskBadgeClass = 'badge-danger';
                            } elseif ($task['status'] === 'pending') {
                                $taskBadgeClass = 'badge-secondary';
                            }
                            ?>
                            <span class="badge <?= $taskBadgeClass ?>"><?= $taskStatusText ?></span>
                        </td>
                        <td>
                            <?php
                            $priorityBadgeClass = 'badge-info';
                            if ($task['priority'] === 'high') {
                                $priorityBadgeClass = 'badge-danger';
                            } elseif ($task['priority'] === 'medium') {
                                $priorityBadgeClass = 'badge-warning';
                            } elseif ($task['priority'] === 'low') {
                                $priorityBadgeClass = 'badge-secondary';
                            }
                            ?>
                            <span class="badge <?= $priorityBadgeClass ?>"><?= ucfirst($task['priority'] ?? 'medium') ?></span>
                        </td>
                        <td>
                            <?php if ($task['assigned_to']): ?>
                                <?php
                                $db = \Config\Database::connect();
                                $assignedUser = $db->table('users')->where('id', $task['assigned_to'])->get()->getRowArray();
                                echo $assignedUser ? esc($assignedUser['name']) : 'Unassigned';
                                ?>
                            <?php else: ?>
                                <span style="color: #9ca3af;">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($task['created_at']): ?>
                                <?php
                                $created = strtotime($task['created_at']);
                                $diff = time() - $created;
                                if ($diff < 3600) {
                                    echo floor($diff / 60) . ' min ago';
                                } elseif ($diff < 86400) {
                                    echo floor($diff / 3600) . ' hour' . (floor($diff / 3600) > 1 ? 's' : '') . ' ago';
                                } else {
                                    echo date('M d, Y', $created);
                                }
                                ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php else: ?>
<section class="panel">
    <header class="panel-header">
        <h3>System Tasks / Tickets</h3>
    </header>
    <div class="stack">
        <p style="color: #6b7280; padding: 20px; text-align: center;">
            No active tasks or tickets. System is running smoothly.
        </p>
    </div>
</section>
<?php endif; ?>

<?= $this->endSection() ?>
