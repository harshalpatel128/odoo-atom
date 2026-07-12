<div class="grid-3 mt-4">
    <section class="panel">
        <h2>Recent Bookings</h2>
        <ul class="activity-list">
            <?php foreach (($latest['bookings'] ?? []) as $booking): ?>
                <li><strong><?= htmlspecialchars($booking['resource_name']) ?></strong> <span><?= htmlspecialchars($booking['status'] . ' - ' . $booking['starts_at']) ?></span></li>
            <?php endforeach; ?>
            <?php if (empty($latest['bookings'])): ?><li>No bookings found.</li><?php endif; ?>
        </ul>
    </section>
    <section class="panel">
        <h2>Latest Maintenance</h2>
        <ul class="activity-list">
            <?php foreach (($latest['maintenance'] ?? []) as $request): ?>
                <li><strong><?= htmlspecialchars($request['asset_tag']) ?></strong> <span><?= htmlspecialchars($request['status'] . ' - ' . $request['asset_name']) ?></span></li>
            <?php endforeach; ?>
            <?php if (empty($latest['maintenance'])): ?><li>No maintenance requests found.</li><?php endif; ?>
        </ul>
    </section>
    <section class="panel">
        <h2>Latest Transfers</h2>
        <ul class="activity-list">
            <?php foreach (($latest['transfers'] ?? []) as $transfer): ?>
                <li><strong><?= htmlspecialchars($transfer['asset_tag']) ?></strong> <span><?= htmlspecialchars($transfer['status'] . ' - ' . $transfer['asset_name']) ?></span></li>
            <?php endforeach; ?>
            <?php if (empty($latest['transfers'])): ?><li>No transfers found.</li><?php endif; ?>
        </ul>
    </section>
</div>
