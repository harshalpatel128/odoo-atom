<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Transfer Requests</h2>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#transferModal"><i class="bi bi-plus-circle"></i> Request Transfer</button>
    </div>
    <table class="table data-table">
        <thead><tr><th>Asset</th><th>From</th><th>To</th><th>Approver</th><th>Status</th><th>Requested</th></tr></thead>
        <tbody>
            <?php foreach (($transfers ?? []) as $transfer): ?>
                <tr>
                    <td><?= htmlspecialchars($transfer['asset_tag'] . ' - ' . $transfer['asset_name']) ?></td>
                    <td><?= htmlspecialchars($transfer['from_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($transfer['to_name']) ?></td>
                    <td><?= htmlspecialchars($transfer['approver_name'] ?? '-') ?></td>
                    <td><span class="status-pill <?= $transfer['status'] === 'Requested' ? 'warning' : '' ?>"><?= htmlspecialchars($transfer['status']) ?></span></td>
                    <td><?= htmlspecialchars($transfer['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Request Asset Transfer</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <form class="row g-3" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/user/transfers">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="col-12"><label class="form-label">Asset</label><select class="form-select" name="asset_id"><?php foreach (($assets ?? []) as $asset): ?><option value="<?= (int) $asset['id'] ?>"><?= htmlspecialchars($asset['asset_tag'] . ' - ' . $asset['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Transfer To</label><select class="form-select" name="to_user_id"><?php foreach (($users ?? []) as $person): ?><option value="<?= (int) $person['id'] ?>"><?= htmlspecialchars($person['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
                    <div class="col-12"><button class="btn btn-primary" type="submit">Submit Request</button></div>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="button" data-bs-dismiss="modal">Submit Request</button></div>
        </div>
    </div>
</div>
