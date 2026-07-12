<?php $flashError = $_SESSION['error'] ?? null; $flashSuccess = $_SESSION['success'] ?? null; unset($_SESSION['error'], $_SESSION['success']); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'ARMS') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($config['base_url']) ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-brand">
            <span class="brand-mark"><i class="bi bi-layers"></i></span>
            <h1>AssetFlow</h1>
            <p>Enterprise Asset & Resource Management System</p>
            <div class="auth-points">
                <span><i class="bi bi-shield-check"></i> Governed assets</span>
                <span><i class="bi bi-graph-up-arrow"></i> Live operations</span>
                <span><i class="bi bi-building-check"></i> ERP-grade control</span>
            </div>
        </section>
        <section class="auth-card">
            <?php if ($flashError): ?><div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>
            <?php if ($flashSuccess): ?><div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
            <?php if (!empty($_SESSION['reset_link'])): ?><div class="alert alert-info small">Local reset link: <a href="<?= htmlspecialchars($_SESSION['reset_link']) ?>">open reset form</a></div><?php unset($_SESSION['reset_link']); endif; ?>
            <?php require $viewFile; ?>
        </section>
    </main>
</body>
</html>
