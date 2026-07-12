<h2>Employee signup</h2>
<p class="muted">Registration always creates an Employee account. Admin can promote roles later.</p>
<form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/signup" class="stack">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <label>Full Name <input class="form-control" name="name" required></label>
    <label>Email <input class="form-control" type="email" name="email" required></label>
    <label>Password <input class="form-control" type="password" name="password" minlength="8" required></label>
    <label>Confirm Password <input class="form-control" type="password" name="password_confirmation" minlength="8" required></label>
    <button class="btn btn-primary w-100">Sign up</button>
</form>
<div class="auth-links"><a href="<?= htmlspecialchars($config['base_url']) ?>/login">Back to login</a></div>
