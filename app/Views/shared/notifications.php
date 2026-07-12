<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Notification Center</h2>
        <form method="post" action="<?= htmlspecialchars($config['base_url'] . (($panel ?? '') === 'admin' ? '/admin' : '/user')) ?>/notifications/read">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <button class="btn btn-outline-primary btn-sm" type="submit"><i class="bi bi-check2-all"></i> Mark all read</button>
        </form>
    </div>
    <p class="text-muted"><?= (int) ($unreadCount ?? 0) ?> unread notifications</p>
    <div class="notification-list">
        <?php $notificationBase = $config['base_url'] . (($panel ?? '') === 'admin' ? '/admin' : '/user') . '/notifications'; ?>
        <?php foreach (($notifications ?? []) as $notification): ?>
            <article class="notification <?= empty($notification['read_at']) ? 'unread' : '' ?>">
                <i class="bi bi-bell"></i>
                <div>
                    <strong><?= htmlspecialchars($notification['title']) ?></strong>
                    <p><?= htmlspecialchars($notification['body'] ?? '') ?></p>
                    <?php if (!empty($notification['user_name'])): ?><small><?= htmlspecialchars($notification['user_name']) ?></small><?php endif; ?>
                </div>
                <span><?= htmlspecialchars($notification['created_at']) ?></span>
                <form class="inline-actions" method="post" action="<?= htmlspecialchars($notificationBase . '/read-one') ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="notification_id" value="<?= (int) $notification['id'] ?>">
                    <button class="btn btn-outline-primary btn-sm" title="Mark as read"><i class="bi bi-check2"></i></button>
                </form>
                <form class="inline-actions" method="post" action="<?= htmlspecialchars($notificationBase . '/delete') ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="notification_id" value="<?= (int) $notification['id'] ?>">
                    <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
            </article>
        <?php endforeach; ?>
        <?php if (empty($notifications)): ?><p class="empty-state">No notifications yet.</p><?php endif; ?>
    </div>
</section>
