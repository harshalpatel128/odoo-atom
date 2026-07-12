<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Allocation Workflow</h2>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#allocationModal"><i class="bi bi-plus-circle"></i> Allocate Asset</button>
    </div>
    <table class="table data-table">
        <thead><tr><th>ID</th><th>Asset</th><th>Employee</th><th>Department</th><th>Expected</th><th>Returned</th><th>Condition</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach (($allocations ?? []) as $allocation): ?>
                <tr>
                    <td><?= (int) $allocation['id'] ?></td>
                    <td><?= htmlspecialchars($allocation['asset_tag'] . ' - ' . $allocation['asset_name']) ?></td>
                    <td><?= htmlspecialchars($allocation['employee_name']) ?></td>
                    <td><?= htmlspecialchars($allocation['department_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($allocation['expected_return_date'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($allocation['returned_at'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(($allocation['condition_before'] ?? '-') . ' -> ' . ($allocation['condition_after'] ?? '-')) ?></td>
                    <td><span class="status-pill <?= $allocation['status'] === 'Allocated' ? 'warning' : '' ?>"><?= htmlspecialchars($allocation['status']) ?></span></td>
                    <td>
                        <?php if ($allocation['status'] === 'Allocated'): ?>
                            <form class="d-inline-flex gap-1" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/allocations/return">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="allocation_id" value="<?= (int) $allocation['id'] ?>">
                                <select class="form-select form-select-sm" name="condition_after" aria-label="Return condition"><option>Good</option><option>New</option><option>Fair</option><option>Poor</option><option>Damaged</option></select>
                                <input class="form-control form-control-sm" name="return_condition_notes" placeholder="Notes">
                                <button class="btn btn-outline-primary btn-sm">Return</button>
                            </form>
                            <form class="d-inline" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/allocations/cancel">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="allocation_id" value="<?= (int) $allocation['id'] ?>">
                                <button class="btn btn-outline-danger btn-sm">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section class="panel mt-4">
    <h2>Transfer Approvals</h2>
    <table class="table data-table">
        <thead><tr><th>ID</th><th>Asset</th><th>From</th><th>To</th><th>Requested By</th><th>Status</th><th>Notes</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach (($transfers ?? []) as $transfer): ?>
                <tr>
                    <td><?= (int) $transfer['id'] ?></td>
                    <td><?= htmlspecialchars($transfer['asset_tag'] . ' - ' . $transfer['asset_name']) ?></td>
                    <td><?= htmlspecialchars($transfer['from_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($transfer['to_name']) ?></td>
                    <td><?= htmlspecialchars($transfer['requester_name']) ?></td>
                    <td><span class="status-pill <?= $transfer['status'] === 'Requested' ? 'warning' : '' ?>"><?= htmlspecialchars($transfer['status']) ?></span></td>
                    <td><?= htmlspecialchars($transfer['notes'] ?? '-') ?></td>
                    <td>
                        <?php if ($transfer['status'] === 'Requested'): ?>
                            <?php foreach (['Approved', 'Rejected'] as $decision): ?>
                                <form class="d-inline" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/transfers/decision">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="transfer_id" value="<?= (int) $transfer['id'] ?>">
                                    <input type="hidden" name="status" value="<?= htmlspecialchars($decision) ?>">
                                    <button class="btn btn-outline-<?= $decision === 'Approved' ? 'primary' : 'danger' ?> btn-sm"><?= htmlspecialchars($decision) ?></button>
                                </form>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<div class="modal fade" id="allocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Allocate Asset</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/allocations">
                <div class="modal-body row g-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="col-md-6"><label class="form-label">Asset</label><select class="form-select" name="asset_id"><?php foreach (($assets ?? []) as $asset): ?><option value="<?= (int) $asset['id'] ?>"><?= htmlspecialchars($asset['asset_tag'] . ' - ' . $asset['name'] . ' (' . $asset['lifecycle_status'] . ')') ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Employee</label><select class="form-select" name="employee_id"><?php foreach (($users ?? []) as $person): ?><option value="<?= (int) $person['id'] ?>"><?= htmlspecialchars($person['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Department</label><select class="form-select" name="department_id"><option value="0">Unassigned</option><?php foreach (($departments ?? []) as $department): ?><option value="<?= (int) $department['id'] ?>"><?= htmlspecialchars($department['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Expected Return</label><input class="form-control" name="expected_return_date" type="date"></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Allocation</button></div>
            </form>
        </div>
    </div>
</div>
