<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Audit Cycle Management</h2>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#auditModal"><i class="bi bi-plus-circle"></i> New Cycle</button>
    </div>
    <table class="table data-table">
        <thead><tr><th>ID</th><th>Audit</th><th>Scope</th><th>Department</th><th>Dates</th><th>Status</th><th>Assets</th><th>Discrepancies</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach (($audits ?? []) as $audit): ?>
                <tr>
                    <td><?= (int) $audit['id'] ?></td>
                    <td><?= htmlspecialchars($audit['name']) ?></td>
                    <td><?= htmlspecialchars($audit['scope']) ?></td>
                    <td><?= htmlspecialchars($audit['department_name'] ?? 'All') ?></td>
                    <td><?= htmlspecialchars($audit['start_date'] . ' to ' . $audit['end_date']) ?></td>
                    <td><span class="status-pill <?= $audit['status'] !== 'Closed' ? 'warning' : '' ?>"><?= htmlspecialchars($audit['status']) ?></span></td>
                    <td><?= (int) $audit['asset_count'] ?></td>
                    <td><?= (int) $audit['missing_count'] ?> missing, <?= (int) $audit['damaged_count'] ?> damaged</td>
                    <td>
                        <?php if ($audit['status'] !== 'Closed'): ?>
                            <form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/audits/close">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="audit_cycle_id" value="<?= (int) $audit['id'] ?>">
                                <button class="btn btn-outline-primary btn-sm">Close</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section class="panel mt-4">
    <h2>Asset Verification Queue</h2>
    <table class="table data-table">
        <thead><tr><th>ID</th><th>Cycle</th><th>Asset</th><th>Auditor</th><th>Result</th><th>Notes</th><th>Update</th></tr></thead>
        <tbody>
            <?php foreach (($auditAssets ?? []) as $item): ?>
                <tr>
                    <td><?= (int) $item['id'] ?></td>
                    <td><?= htmlspecialchars($item['cycle_name']) ?></td>
                    <td><?= htmlspecialchars($item['asset_tag'] . ' - ' . $item['asset_name']) ?></td>
                    <td><?= htmlspecialchars($item['auditor_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($item['result'] ?? 'Pending') ?></td>
                    <td><?= htmlspecialchars($item['notes'] ?? '-') ?></td>
                    <td>
                        <form class="d-flex gap-1" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/audits/item">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="audit_asset_id" value="<?= (int) $item['id'] ?>">
                            <select class="form-select form-select-sm" name="result"><option>Verified</option><option>Missing</option><option>Damaged</option></select>
                            <input class="form-control form-control-sm" name="notes" placeholder="Notes">
                            <button class="btn btn-primary btn-sm">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<div class="modal fade" id="auditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Create Audit Cycle</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/audits">
                <div class="modal-body row g-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="col-12"><label class="form-label">Cycle Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label">Scope</label><select class="form-select" name="scope"><option>All Assets</option><option>Department</option><option>Location</option></select></div>
                    <div class="col-md-6"><label class="form-label">Auditor</label><select class="form-select" name="auditor_user_id"><option value="0">Unassigned</option><?php foreach (($users ?? []) as $person): ?><option value="<?= (int) $person['id'] ?>"><?= htmlspecialchars($person['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Department</label><select class="form-select" name="department_id"><option value="0">All</option><?php foreach (($departments ?? []) as $department): ?><option value="<?= (int) $department['id'] ?>"><?= htmlspecialchars($department['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Location</label><input class="form-control" name="location"></div>
                    <div class="col-md-6"><label class="form-label">Start</label><input class="form-control" name="start_date" type="date" required></div>
                    <div class="col-md-6"><label class="form-label">End</label><input class="form-control" name="end_date" type="date" required></div>
                </div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Create</button></div>
            </form>
        </div>
    </div>
</div>
