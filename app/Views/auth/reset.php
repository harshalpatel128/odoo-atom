<h2>Reset password</h2>
<p class="muted">Enter a new password for your AssetFlow account.</p>
<form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/reset-password" class="stack">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
    <label>New Password <input class="form-control" type="password" name="password" minlength="8" required></label>
    <label>Confirm Password <input class="form-control" type="password" name="password_confirmation" minlength="8" required></label>
    <button class="btn btn-primary w-100">Reset password</button>
</form>
<div class="auth-links"><a href="<?= htmlspecialchars($config['base_url']) ?>/login">Back to login</a></div>
