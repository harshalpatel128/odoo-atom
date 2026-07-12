<?php require __DIR__ . '/../partials/kpis.php'; ?>
<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<div class="grid-2">
    <section class="panel">
        <h2>Admin Quick Actions</h2>
        <div class="actions">
            <a class="btn btn-primary" href="<?= htmlspecialchars($config['base_url']) ?>/admin/assets">Register Asset</a>
            <a class="btn btn-outline-primary" href="<?= htmlspecialchars($config['base_url']) ?>/admin/organization">Manage Organization</a>
            <a class="btn btn-outline-primary" href="<?= htmlspecialchars($config['base_url']) ?>/admin/reports">View Reports</a>
        </div>
    </section>
    <section class="panel">
        <h2>Alerts</h2>
        <ul class="activity-list">
            <li>Overdue returns require review</li>
            <li>Pending maintenance approvals</li>
            <li>Audit discrepancies need closure</li>
        </ul>
    </section>
</div>
<div class="grid-2 mt-4">
    <section class="panel"><h2>Asset Status</h2><canvas class="js-chart" data-chart='<?= htmlspecialchars(json_encode(['type' => 'pie', 'rows' => $charts['assetStatus'] ?? []])) ?>' height="120"></canvas></section>
    <section class="panel"><h2>Recent Activities</h2><?php require __DIR__ . '/../partials/activity.php'; ?></section>
</div>
<div class="grid-3 mt-4">
    <section class="panel"><h2>Department Allocation</h2><canvas class="js-chart" data-chart='<?= htmlspecialchars(json_encode(['type' => 'bar', 'rows' => $charts['departmentAllocation'] ?? []])) ?>' height="140"></canvas></section>
    <section class="panel"><h2>Asset Category</h2><canvas class="js-chart" data-chart='<?= htmlspecialchars(json_encode(['type' => 'doughnut', 'rows' => $charts['assetCategory'] ?? []])) ?>' height="140"></canvas></section>
    <section class="panel"><h2>Audit Summary</h2><canvas class="js-chart" data-chart='<?= htmlspecialchars(json_encode(['type' => 'bar', 'rows' => $charts['auditSummary'] ?? []])) ?>' height="140"></canvas></section>
</div>
<?php require __DIR__ . '/../partials/dashboard_extras.php'; ?>
