<h2>Sign in</h2>
<p class="muted">Choose the same sign-in page. The system sends admins to Admin Panel and employees to User Panel.</p>
<form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/login" class="stack">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <label>Email <input class="form-control" type="email" name="email" required></label>
    <label>Password <input class="form-control" type="password" name="password" required></label>
    <label class="form-check"><input class="form-check-input" type="checkbox" name="remember"> <span class="form-check-label">Remember me</span></label>
    <button class="btn btn-primary w-100">Login</button>
</form>
<div class="auth-links">
    <a href="<?= htmlspecialchars($config['base_url']) ?>/forgot-password">Forgot password?</a>
    <a href="<?= htmlspecialchars($config['base_url']) ?>/signup">Create employee account</a>
</div>
