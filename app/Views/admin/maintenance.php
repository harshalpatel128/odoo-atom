<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Maintenance Management</h2>
        <span class="status-pill warning">Status updates change asset availability</span>
    </div>
    <table class="table data-table">
        <thead><tr><th>ID</th><th>Asset</th><th>Requester</th><th>Priority</th><th>Technician</th><th>Status</th><th>Cost</th><th>Description</th><th>Update</th></tr></thead>
        <tbody>
            <?php foreach (($requests ?? []) as $request): ?>
                <tr>
                    <td><?= (int) $request['id'] ?></td>
                    <td><?= htmlspecialchars($request['asset_tag'] . ' - ' . $request['asset_name']) ?></td>
                    <td><?= htmlspecialchars($request['requester_name']) ?></td>
                    <td><?= htmlspecialchars($request['priority']) ?></td>
                    <td><?= htmlspecialchars($request['technician_name'] ?? '-') ?></td>
                    <td><span class="status-pill <?= in_array($request['status'], ['Pending','In Progress','Technician Assigned'], true) ? 'warning' : '' ?>"><?= htmlspecialchars($request['status']) ?></span></td>
                    <td><?= htmlspecialchars($request['repair_cost'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($request['description']) ?></td>
                    <td>
                        <form class="row g-1" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/maintenance/update">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="maintenance_request_id" value="<?= (int) $request['id'] ?>">
                            <div class="col-12"><select class="form-select form-select-sm" name="status"><option>Approved</option><option>Rejected</option><option>Technician Assigned</option><option>In Progress</option><option>Completed</option></select></div>
                            <div class="col-12"><select class="form-select form-select-sm" name="technician_user_id"><option value="0">Unassigned</option><?php foreach (($users ?? []) as $person): ?><option value="<?= (int) $person['id'] ?>"><?= htmlspecialchars($person['name']) ?></option><?php endforeach; ?></select></div>
                            <div class="col-5"><input class="form-control form-control-sm" name="repair_cost" placeholder="Cost"></div>
                            <div class="col-7"><input class="form-control form-control-sm" name="notes" placeholder="Repair notes"></div>
                            <div class="col-12"><button class="btn btn-primary btn-sm">Update</button></div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
