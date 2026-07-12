<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Resource Booking Calendar</h2>
        <div class="btn-group" role="group" aria-label="Calendar view"><button class="btn btn-outline-secondary btn-sm js-calendar-view active" type="button" data-view="week">Week</button><button class="btn btn-outline-secondary btn-sm js-calendar-view" type="button" data-view="month">Month</button></div>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#bookingModal"><i class="bi bi-plus-circle"></i> New Booking</button>
    </div>
    <div class="calendar-board js-booking-calendar" data-bookings='<?= htmlspecialchars(json_encode($bookings ?? []), ENT_QUOTES, 'UTF-8') ?>'>
        <?php foreach (array_slice(($bookings ?? []), 0, 6) as $booking): ?>
            <div><strong><?= htmlspecialchars(date('M d H:i', strtotime($booking['starts_at']))) ?></strong><span><?= htmlspecialchars($booking['resource_name']) ?></span><small><?= htmlspecialchars($booking['status']) ?></small></div>
        <?php endforeach; ?>
    </div>
</section>
<section class="panel mt-4">
    <h2>Booking Directory</h2>
    <table class="table data-table">
        <thead><tr><th>ID</th><th>Resource</th><th>Type</th><th>User</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach (($bookings ?? []) as $booking): ?>
                <tr>
                    <td><?= (int) $booking['id'] ?></td>
                    <td><?= htmlspecialchars($booking['resource_name']) ?></td>
                    <td><?= htmlspecialchars($booking['resource_type']) ?></td>
                    <td><?= htmlspecialchars($booking['user_name']) ?></td>
                    <td><?= htmlspecialchars($booking['starts_at']) ?></td>
                    <td><?= htmlspecialchars($booking['ends_at']) ?></td>
                    <td><span class="status-pill <?= in_array($booking['status'], ['Pending','Cancelled','Rejected'], true) ? 'warning' : '' ?>"><?= htmlspecialchars($booking['status']) ?></span></td>
                    <td>
                        <?php foreach (['Approved', 'Rejected', 'Cancelled', 'Completed'] as $status): ?>
                            <form class="d-inline" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/bookings/decision">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                                <button class="btn btn-outline-secondary btn-sm"><?= htmlspecialchars($status) ?></button>
                            </form>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Create Booking</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/bookings">
                <div class="modal-body row g-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="col-md-6"><label class="form-label">Resource</label><select class="form-select" name="resource_id"><?php foreach (($resources ?? []) as $resource): ?><option value="<?= (int) $resource['id'] ?>"><?= htmlspecialchars($resource['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">User</label><select class="form-select" name="user_id"><?php foreach (($users ?? []) as $person): ?><option value="<?= (int) $person['id'] ?>"><?= htmlspecialchars($person['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Start</label><input class="form-control" name="starts_at" type="datetime-local" required></div>
                    <div class="col-md-6"><label class="form-label">End</label><input class="form-control" name="ends_at" type="datetime-local" required></div>
                    <div class="col-12"><label class="form-label">Purpose</label><textarea class="form-control" name="purpose" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Booking</button></div>
            </form>
        </div>
    </div>
</div>
