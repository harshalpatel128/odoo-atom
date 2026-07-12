<?php $flashError = $_SESSION['error'] ?? null; $flashSuccess = $_SESSION['success'] ?? null; unset($_SESSION['error'], $_SESSION['success']); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'ARMS') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($config['base_url']) ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-brand">
            <span class="brand-mark">A</span>
            <h1>ARMS</h1>
            <p>Enterprise Asset & Resource Management System</p>
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
