<?php require __DIR__ . '/../partials/kpis.php'; ?>
<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<div class="grid-2">
    <section class="panel">
        <h2>My Quick Actions</h2>
        <div class="actions">
            <a class="btn btn-primary" href="<?= htmlspecialchars($config['base_url']) ?>/user/bookings">Book Resource</a>
            <a class="btn btn-outline-primary" href="<?= htmlspecialchars($config['base_url']) ?>/user/maintenance">Raise Maintenance Request</a>
            <a class="btn btn-outline-primary" href="<?= htmlspecialchars($config['base_url']) ?>/user/transfers">Request Transfer</a>
        </div>
    </section>
    <section class="panel">
        <h2>Recent Activity</h2>
        <?php require __DIR__ . '/../partials/activity.php'; ?>
    </section>
</div>
<div class="grid-2 mt-4">
    <section class="panel"><h2>Asset Status</h2><canvas class="js-chart" data-chart='<?= htmlspecialchars(json_encode(['type' => 'pie', 'rows' => $charts['assetStatus'] ?? []])) ?>' height="120"></canvas></section>
    <section class="panel"><h2>Monthly Bookings</h2><canvas class="js-chart" data-chart='<?= htmlspecialchars(json_encode(['type' => 'line', 'rows' => array_reverse($charts['monthlyBookings'] ?? [])])) ?>' height="120"></canvas></section>
</div>
<?php require __DIR__ . '/../partials/dashboard_extras.php'; ?>
