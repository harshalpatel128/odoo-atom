<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Resource Booking</h2>
        <span class="status-pill">Overlap check ready</span>
    </div>
    <form class="row g-3" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/user/bookings">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="col-md-4"><label class="form-label">Resource</label><select class="form-select" name="resource_id"><?php foreach (($resources ?? []) as $resource): ?><option value="<?= (int) $resource['id'] ?>"><?= htmlspecialchars($resource['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label class="form-label">Start</label><input class="form-control" name="starts_at" type="datetime-local" required></div>
        <div class="col-md-4"><label class="form-label">End</label><input class="form-control" name="ends_at" type="datetime-local" required></div>
        <div class="col-12"><label class="form-label">Purpose</label><input class="form-control" name="purpose" placeholder="Purpose"></div>
        <div class="col-12"><button type="submit" class="btn btn-primary">Request Booking</button></div>
    </form>
</section>
<section class="panel mt-4">
    <h2>My Bookings</h2>
    <table class="table data-table">
        <thead><tr><th>Resource</th><th>Type</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach (($bookings ?? []) as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['resource_name']) ?></td>
                    <td><?= htmlspecialchars($booking['resource_type']) ?></td>
                    <td><?= htmlspecialchars($booking['starts_at']) ?></td>
                    <td><?= htmlspecialchars($booking['ends_at']) ?></td>
                    <td><span class="status-pill <?= in_array($booking['status'], ['Pending','Rejected','Cancelled'], true) ? 'warning' : '' ?>"><?= htmlspecialchars($booking['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
