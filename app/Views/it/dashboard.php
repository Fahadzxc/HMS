<!-- IT Staff dashboard partial (inner content only) -->
<section class="panel">
    <header class="panel-header">
        <h2>IT Dashboard</h2>
        <p>Welcome back, <?= session()->get('name') ?>. Here's your system overview for today.</p>
    </header>
    <div class="stack">
        <div class="actions-grid">
            <div class="action-tile">
                <span>System Status</span>
                <?php
                $status = $overallStatus ?? 'normal';
                $statusColor = '#10b981'; // normal
                $statusText = 'Normal';
                if ($status === 'degraded') {
                    $statusColor = '#f59e0b';
                    $statusText = 'Degraded';
                } elseif ($status === 'down') {
                    $statusColor = '#ef4444';
                    $statusText = 'Down';
                } elseif ($status === 'maintenance') {
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
                <strong><?= $activeUsers ?? 0 ?></strong>
            </div>
            <div class="action-tile">
                <span>Pending Tickets</span>
                <strong><?= $pendingTickets ?? 0 ?></strong>
            </div>
            <div class="action-tile">
                <span>Last Backup</span>
                <strong><?= $lastBackup ?? 'Never' ?></strong>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Quick Actions</h3>
    </header>
    <div class="stack">
        <div class="button-group">
            <a href="/it/system" class="button button-primary">System Status</a>
            <a href="/it/users" class="button button-secondary">User Management</a>
            <a href="/it/backup" class="button button-secondary">Backup System</a>
            <a href="/it/security" class="button button-secondary">Security Logs</a>
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
                        <th>Uptime</th>
                        <th>Performance</th>
                        <th>Last Check</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($systemHealth) && is_array($systemHealth)): ?>
                        <?php foreach ($systemHealth as $component): ?>
                        <tr>
                            <td><?= esc($component['name']) ?></td>
                            <td>
                                <?php
                                $badgeClass = 'badge-success';
                                $statusText = 'Online';
                                if ($component['status'] === 'degraded') {
                                    $badgeClass = 'badge-warning';
                                    $statusText = 'Degraded';
                                } elseif ($component['status'] === 'offline') {
                                    $badgeClass = 'badge-danger';
                                    $statusText = 'Offline';
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                            </td>
                            <?php
                            $uptimeParts = explode(' ', $component['uptime'], 2);
                            $uptimePercent = $uptimeParts[0] ?? 'N/A';
                            $uptimePerformance = $uptimeParts[1] ?? 'N/A';
                            ?>
                            <td><?= esc($uptimePercent) ?></td>
                            <td><?= esc($uptimePerformance) ?></td>
                            <td><?= esc($component['last_check']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Loading system health data...</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Support Tickets</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>User</th>
                        <th>Issue</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>T001234</td>
                        <td>Dr. Santos</td>
                        <td>Cannot access patient records</td>
                        <td><span class="badge badge-danger">High</span></td>
                        <td><span class="badge badge-warning">Open</span></td>
                        <td>1 hour ago</td>
                        <td><a href="#" class="button button-small">Resolve</a></td>
                    </tr>
                    <tr>
                        <td>T001235</td>
                        <td>Nurse Jane</td>
                        <td>Printer not working</td>
                        <td><span class="badge badge-warning">Medium</span></td>
                        <td><span class="badge badge-warning">Open</span></td>
                        <td>2 hours ago</td>
                        <td><a href="#" class="button button-small">Resolve</a></td>
                    </tr>
                    <tr>
                        <td>T001236</td>
                        <td>Reception Maria</td>
                        <td>Slow system performance</td>
                        <td><span class="badge badge-info">Low</span></td>
                        <td><span class="badge badge-success">Resolved</span></td>
                        <td>3 hours ago</td>
                        <td><a href="#" class="button button-small">View</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="panel">
    <header class="panel-header">
        <h3>Security Alerts</h3>
    </header>
    <div class="stack">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Alert ID</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Severity</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>A001234</td>
                        <td>Failed Login</td>
                        <td>Multiple failed login attempts</td>
                        <td><span class="badge badge-warning">Medium</span></td>
                        <td>30 minutes ago</td>
                        <td><span class="badge badge-success">Resolved</span></td>
                    </tr>
                    <tr>
                        <td>A001235</td>
                        <td>Unauthorized Access</td>
                        <td>Attempted access to admin panel</td>
                        <td><span class="badge badge-danger">High</span></td>
                        <td>1 hour ago</td>
                        <td><span class="badge badge-warning">Investigating</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
