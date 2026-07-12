<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Raise Maintenance Request</h2>
        <span class="status-pill warning">Photo evidence supported</span>
    </div>
    <form class="row g-3" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/user/maintenance" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="col-md-4"><label class="form-label">Asset</label><select class="form-select" name="asset_id"><?php foreach (($assets ?? []) as $asset): ?><option value="<?= (int) $asset['id'] ?>"><?= htmlspecialchars($asset['asset_tag'] . ' - ' . $asset['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label class="form-label">Priority</label><select class="form-select" name="priority"><option>Low</option><option>Medium</option><option>High</option><option>Critical</option></select></div>
        <div class="col-md-4"><label class="form-label">Photo</label><input class="form-control" name="photo" type="file" accept=".jpg,.jpeg,.png,.webp"></div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" required></textarea></div>
        <div class="col-12"><button type="submit" class="btn btn-primary">Submit Request</button></div>
    </form>
</section>
<section class="panel mt-4">
    <h2>Request Timeline</h2>
    <table class="table data-table">
        <thead><tr><th>Asset</th><th>Priority</th><th>Status</th><th>Technician</th><th>Updated</th></tr></thead>
        <tbody>
            <?php foreach (($requests ?? []) as $request): ?>
                <tr>
                    <td><?= htmlspecialchars($request['asset_tag'] . ' - ' . $request['asset_name']) ?></td>
                    <td><?= htmlspecialchars($request['priority']) ?></td>
                    <td><span class="status-pill <?= in_array($request['status'], ['Pending','In Progress','Technician Assigned'], true) ? 'warning' : '' ?>"><?= htmlspecialchars($request['status']) ?></span></td>
                    <td><?= htmlspecialchars($request['technician_name'] ?? 'Unassigned') ?></td>
                    <td><?= htmlspecialchars($request['resolved_at'] ?? $request['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
