<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head"><h2>Assigned Audit Assets</h2></div>
    <table class="table data-table">
        <thead><tr><th>Cycle</th><th>Asset</th><th>Status</th><th>Verification</th></tr></thead>
        <tbody><?php foreach (($auditAssets ?? []) as $item): ?><tr>
            <td><?= htmlspecialchars($item['cycle_name']) ?></td><td><?= htmlspecialchars($item['asset_tag'] . ' - ' . $item['asset_name']) ?></td>
            <td><span class="status-pill <?= $item['cycle_status'] !== 'Open' ? 'warning' : '' ?>"><?= htmlspecialchars($item['cycle_status']) ?></span></td>
            <td><?php if ($item['cycle_status'] === 'Open'): ?><form class="d-flex gap-1" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/user/audits/item">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="audit_asset_id" value="<?= (int) $item['id'] ?>">
                <select class="form-select form-select-sm" name="result"><option>Verified</option><option>Missing</option><option>Damaged</option></select><input class="form-control form-control-sm" name="notes" value="<?= htmlspecialchars($item['notes'] ?? '') ?>" placeholder="Notes"><button class="btn btn-primary btn-sm">Save</button>
            </form><?php else: ?><?= htmlspecialchars($item['result'] ?? 'Closed') ?><?php endif; ?></td>
        </tr><?php endforeach; ?><?php if (empty($auditAssets)): ?><tr><td colspan="4">No audit tasks are assigned to you.</td></tr><?php endif; ?></tbody>
    </table>
</section>
