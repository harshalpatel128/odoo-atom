<?php
$user = $_SESSION['user'] ?? [];
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
$isAdmin = ($panel ?? '') === 'admin';
$nav = $isAdmin
    ? [
        '/admin/dashboard' => ['Dashboard', 'bi-speedometer2'],
        '/admin/organization' => ['Organization', 'bi-diagram-3'],
        '/admin/assets' => ['Assets', 'bi-box-seam'],
        '/admin/allocations' => ['Allocations', 'bi-arrow-left-right'],
        '/admin/bookings' => ['Bookings', 'bi-calendar2-check'],
        '/admin/maintenance' => ['Maintenance', 'bi-tools'],
        '/admin/audits' => ['Audits', 'bi-clipboard-check'],
        '/admin/reports' => ['Reports', 'bi-bar-chart'],
        '/admin/notifications' => ['Notifications', 'bi-bell'],
        '/admin/logs' => ['Logs', 'bi-journal-text'],
    ]
    : [
        '/user/dashboard' => ['Dashboard', 'bi-speedometer2'],
        '/user/assets' => ['My Assets', 'bi-box-seam'],
        '/user/bookings' => ['Bookings', 'bi-calendar2-check'],
        '/user/maintenance' => ['Maintenance', 'bi-tools'],
        '/user/transfers' => ['Transfers', 'bi-arrow-left-right'],
        '/user/notifications' => ['Notifications', 'bi-bell'],
    ];
$panelBase = $isAdmin ? '/admin' : '/user';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'ARMS') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($config['base_url']) ?>/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="app-frame">
    <aside class="sidebar <?= $isAdmin ? 'sidebar-admin' : 'sidebar-user' ?>">
        <a class="brand" href="<?= htmlspecialchars($config['base_url']) . ($isAdmin ? '/admin/dashboard' : '/user/dashboard') ?>">
            <span class="brand-mark"><?= $isAdmin ? 'AD' : 'U' ?></span>
            <span><strong>AssetFlow</strong><small><?= $isAdmin ? 'Admin Panel' : 'User Panel' ?></small></span>
        </a>
        <nav>
            <?php foreach ($nav as $path => [$label, $icon]): ?>
                <?php $active = str_ends_with($currentPath, $path) || $currentPath === $path; ?>
                <a class="<?= $active ? 'active' : '' ?>" href="<?= htmlspecialchars($config['base_url'] . $path) ?>"><i class="bi <?= htmlspecialchars($icon) ?>"></i><?= htmlspecialchars($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <div class="content">
        <header class="topbar">
            <button class="btn btn-outline-dark btn-sm menu-toggle" type="button" aria-label="Toggle navigation"><i class="bi bi-list"></i></button>
            <div>
                <p class="eyebrow"><?= htmlspecialchars($user['role_name'] ?? '') ?></p>
                <h1><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
            </div>
            <div class="topbar-actions">
                <button class="btn btn-outline-dark btn-sm js-dark-toggle-btn" type="button" title="Toggle dark mode"><i class="bi bi-moon"></i></button>
                <a class="btn btn-outline-dark btn-sm" href="<?= htmlspecialchars($config['base_url'] . $panelBase) ?>/notifications" title="Notifications"><i class="bi bi-bell"></i></a>
                <a class="btn btn-outline-dark btn-sm" href="<?= htmlspecialchars($config['base_url'] . $panelBase) ?>/profile" title="Profile"><i class="bi bi-person"></i></a>
                <a class="btn btn-outline-dark btn-sm" href="<?= htmlspecialchars($config['base_url'] . $panelBase) ?>/settings" title="Settings"><i class="bi bi-gear"></i></a>
                <form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/logout">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                    <button class="btn btn-outline-dark btn-sm" title="Logout"><i class="bi bi-box-arrow-right"></i><span class="btn-text">Logout</span></button>
                </form>
            </div>
        </header>
        <nav class="breadcrumb-wrap" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($config['base_url'] . $panelBase) ?>/dashboard"><?= $isAdmin ? 'Admin' : 'User' ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($title ?? 'Dashboard') ?></li>
            </ol>
        </nav>
        <?php if ($flashSuccess): ?><div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>
        <?php require $viewFile; ?>
        <footer class="app-footer">
            <span>AssetFlow ARMS</span>
            <span>Enterprise asset and resource operations</span>
        </footer>
    </div>
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="appToast" class="toast" role="status" aria-live="polite" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">AssetFlow</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">Workspace refreshed.</div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script src="<?= htmlspecialchars($config['base_url']) ?>/assets/js/app.js"></script>
</body>
</html>
