<h2>Forgot password</h2>
<p class="muted">Enter your email. A reset workflow record can be created from here.</p>
<form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/forgot-password" class="stack">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <label>Email <input class="form-control" type="email" name="email" required></label>
    <button class="btn btn-primary w-100">Send reset link</button>
</form>
<div class="auth-links"><a href="<?= htmlspecialchars($config['base_url']) ?>/login">Back to login</a></div>
