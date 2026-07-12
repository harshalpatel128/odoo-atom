<div class="grid-2">
    <section class="panel">
        <h2>Interface</h2>
        <div class="setting-row">
            <div><strong>Dark Mode</strong><p class="muted">Switch to a darker workspace theme.</p></div>
            <div class="form-check form-switch"><input class="form-check-input js-dark-toggle" type="checkbox" role="switch"></div>
        </div>
        <div class="setting-row">
            <div><strong>Compact Tables</strong><p class="muted">Reduce row height for dense lists.</p></div>
            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch"></div>
        </div>
    </section>
    <section class="panel">
        <h2>Account Security</h2>
        <form class="row g-3" method="post" action="<?= htmlspecialchars($config['base_url'] . (($panel ?? '') === 'admin' ? '/admin' : '/user')) ?>/settings/password">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="col-12"><label class="form-label">Current Password</label><input class="form-control" name="current_password" type="password" required></div>
            <div class="col-md-6"><label class="form-label">New Password</label><input class="form-control" name="new_password" type="password" minlength="8" required></div>
            <div class="col-md-6"><label class="form-label">Confirm Password</label><input class="form-control" name="confirm_password" type="password" minlength="8" required></div>
            <div class="col-12"><button class="btn btn-primary"><i class="bi bi-shield-check"></i> Update Security</button></div>
        </form>
    </section>
</div>
