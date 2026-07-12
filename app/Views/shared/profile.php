<?php $currentUser = $_SESSION['user'] ?? []; ?>
<div class="grid-2">
    <section class="panel profile-panel">
        <div class="avatar-xl"><?= htmlspecialchars(strtoupper(substr($currentUser['name'] ?? 'U', 0, 1))) ?></div>
        <h2><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></h2>
        <p class="muted"><?= htmlspecialchars($currentUser['email'] ?? 'user@example.com') ?></p>
        <span class="status-pill"><?= htmlspecialchars($currentUser['role_name'] ?? 'Employee') ?></span>
    </section>
    <section class="panel">
        <h2>Profile Details</h2>
        <form class="row g-3" method="post" action="<?= htmlspecialchars($config['base_url'] . (($panel ?? '') === 'admin' ? '/admin' : '/user')) ?>/profile">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="col-md-6"><label class="form-label">Full Name</label><input class="form-control" name="name" required value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" readonly value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Department</label><input class="form-control" readonly value="<?= htmlspecialchars($currentUser['department_name'] ?? 'Unassigned') ?>"></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" placeholder="+91" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>"></div>
            <div class="col-12"><button class="btn btn-primary"><i class="bi bi-save"></i> Save Profile</button></div>
        </form>
    </section>
</div>
