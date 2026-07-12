<section class="panel">
    <h2>Activity Logs</h2>
    <table class="table data-table">
        <thead><tr><th>User</th><th>Action</th><th>Module</th><th>IP</th><th>Time</th></tr></thead>
        <tbody>
        <?php foreach ($activities as $activity): ?>
            <tr>
                <td><?= htmlspecialchars($activity['name'] ?? 'System') ?></td>
                <td><?= htmlspecialchars($activity['action']) ?></td>
                <td><?= htmlspecialchars($activity['module']) ?></td>
                <td><?= htmlspecialchars($activity['ip_address']) ?></td>
                <td><?= htmlspecialchars($activity['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
