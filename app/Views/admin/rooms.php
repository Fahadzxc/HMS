<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<section class="panel">
    <header class="panel-header">
        <h2>Rooms Management</h2>
        <p>View and manage hospital rooms, beds, and occupancy</p>
    </header>
    <div class="stack">
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Rooms</div>
                    <div class="kpi-value"><?= $stats['total_rooms'] ?? 0 ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Available Rooms</div>
                    <div class="kpi-value"><?= $stats['available_rooms'] ?? 0 ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Occupied Rooms</div>
                    <div class="kpi-value"><?= $stats['occupied_rooms'] ?? 0 ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Beds</div>
                    <div class="kpi-value"><?= $stats['total_beds'] ?? 0 ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Available Beds</div>
                    <div class="kpi-value"><?= $stats['available_beds'] ?? 0 ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Occupied Beds</div>
                    <div class="kpi-value"><?= $stats['occupied_beds'] ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($roomsByType)): ?>
    <?php foreach ($roomsByType as $type => $typeRooms): ?>
        <section class="panel panel-spaced">
            <header class="panel-header">
                <h2><?= ucfirst($type) ?> Rooms</h2>
                <p><?= count($typeRooms) ?> room(s)</p>
            </header>
            
            <div class="stack">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Number</th>
                                <th>Floor</th>
                                <th>Specialization</th>
                                <th>Capacity</th>
                                <th>Occupied</th>
                                <th>Available</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Occupancy Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($typeRooms as $room): ?>
                                <?php
                                    $capacity = (int)($room['capacity'] ?? 0);
                                    // Use actual_occupancy from admissions table instead of current_occupancy
                                    $occupancy = (int)($room['actual_occupancy'] ?? $room['current_occupancy'] ?? 0);
                                    $available = $capacity - $occupancy;
                                    $isAvailable = (bool)($room['is_available'] ?? true);
                                    $occupancyRate = $capacity > 0 ? round(($occupancy / $capacity) * 100, 1) : 0;
                                    $hasSpace = $occupancy < $capacity;
                                    $occupiedPatients = !empty($room['occupied_patients']) ? $room['occupied_patients'] : '';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($room['room_number'] ?? '—') ?></strong>
                                        <?php if ($occupancy > 0 && !empty($occupiedPatients)): ?>
                                            <br><small style="color: #6b7280; font-size: 0.75rem;">
                                                Patients: <?= esc($occupiedPatients) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($room['floor'] ?? '—') ?></td>
                                    <td><?= esc($room['specialization'] ?? '—') ?></td>
                                    <td><?= $capacity ?></td>
                                    <td>
                                        <span class="badge badge-<?= $occupancy > 0 ? 'warning' : 'secondary' ?>">
                                            <?= $occupancy ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $available > 0 ? 'success' : 'danger' ?>">
                                            <?= $available ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $roomPrice = isset($room['room_price']) ? (float)$room['room_price'] : 0.00;
                                            if ($roomPrice > 0):
                                        ?>
                                            <strong style="color: #059669;">₱<?= number_format($roomPrice, 2) ?></strong>
                                        <?php else: ?>
                                            <span style="color: #9ca3af;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($occupancy > 0): ?>
                                            <span class="badge badge-warning">Occupied</span>
                                        <?php elseif ($isAvailable && $hasSpace): ?>
                                            <span class="badge badge-success">Available</span>
                                        <?php elseif (!$isAvailable): ?>
                                            <span class="badge badge-danger">Unavailable</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Full</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                                <div style="height: 100%; width: <?= $occupancyRate ?>%; background: <?= $occupancyRate >= 100 ? '#ef4444' : ($occupancyRate >= 80 ? '#f59e0b' : '#10b981') ?>;"></div>
                                            </div>
                                            <span style="font-size: 0.875rem; color: #6b7280; min-width: 45px;"><?= $occupancyRate ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button 
                                            type="button" 
                                            class="btn-toggle-availability" 
                                            data-room-id="<?= $room['id'] ?>"
                                            data-is-available="<?= $isAvailable ? '1' : '0' ?>"
                                            onclick="toggleRoomAvailability(<?= $room['id'] ?>, <?= $isAvailable ? 'true' : 'false' ?>)"
                                            style="padding: 0.375rem 0.75rem; font-size: 0.875rem; border-radius: 0.375rem; border: 1px solid; cursor: pointer; transition: all 0.2s; <?= $isAvailable ? 'background: #fee2e2; color: #991b1b; border-color: #fca5a5;' : 'background: #d1fae5; color: #065f46; border-color: #6ee7b7;' ?>"
                                            title="<?= $isAvailable ? 'Click to mark as Unavailable' : 'Click to mark as Available' ?>">
                                            <i class="fas <?= $isAvailable ? 'fa-toggle-on' : 'fa-toggle-off' ?>" style="margin-right: 0.25rem;"></i>
                                            <?= $isAvailable ? 'Set Unavailable' : 'Set Available' ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
<?php else: ?>
    <section class="panel panel-spaced">
        <div class="stack">
            <div style="text-align: center; padding: 3rem;">
                <p>No rooms found.</p>
            </div>
        </div>
    </section>
<?php endif; ?>

<style>
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge-success {
    background-color: #d1fae5;
    color: #065f46;
}

.badge-warning {
    background-color: #fef3c7;
    color: #92400e;
}

.badge-danger {
    background-color: #fee2e2;
    color: #991b1b;
}

.badge-secondary {
    background-color: #e5e7eb;
    color: #374151;
}

.badge-info {
    background-color: #dbeafe;
    color: #1e40af;
}

.btn-toggle-availability:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

.btn-toggle-availability:active {
    transform: scale(0.95);
}
</style>

<script>
function toggleRoomAvailability(roomId, currentStatus) {
    if (!confirm('Are you sure you want to ' + (currentStatus ? 'mark this room as UNAVAILABLE?' : 'mark this room as AVAILABLE?'))) {
        return;
    }

    const button = document.querySelector(`button[data-room-id="${roomId}"]`);
    if (button) {
        button.disabled = true;
        button.style.opacity = '0.6';
    }

    fetch('<?= site_url('admin/rooms/toggleAvailability') ?>/' + roomId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ room_id: roomId })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload(); // Reload to show updated status
        } else {
            alert('❌ ' + (result.message || 'Failed to update room availability'));
            if (button) {
                button.disabled = false;
                button.style.opacity = '1';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred. Please try again.');
        if (button) {
            button.disabled = false;
            button.style.opacity = '1';
        }
    });
}
</script>

<?= $this->endSection() ?>
