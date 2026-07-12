<ul class="activity-list">
    <?php foreach (($activities ?? []) as $activity): ?>
        <li><strong><?= htmlspecialchars($activity['name'] ?? 'System') ?></strong> <?= htmlspecialchars($activity['action']) ?> <span><?= htmlspecialchars($activity['module']) ?></span></li>
    <?php endforeach; ?>
    <?php if (empty($activities)): ?><li>No activity yet.</li><?php endif; ?>
</ul>
