<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Asset Registration</h2>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#assetDetailModal"><i class="bi bi-plus-circle"></i> Register</button>
    </div>
    <form class="row g-3 mb-4" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/assets" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="col-md-3"><input class="form-control" name="name" placeholder="Asset Name" required></div>
        <div class="col-md-3"><select class="form-select" name="category_id" required><?php foreach (($categories ?? []) as $category): ?><option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><input class="form-control" name="serial_number" placeholder="Serial Number"></div>
        <div class="col-md-3"><select class="form-select" name="lifecycle_status"><option>Available</option><option>Allocated</option><option>Reserved</option><option>Maintenance</option><option>Lost</option><option>Disposed</option><option>Retired</option></select></div>
        <div class="col-md-3"><input class="form-control" name="purchase_date" type="date"></div>
        <div class="col-md-3"><input class="form-control" name="purchase_cost" placeholder="Purchase Cost"></div>
        <div class="col-md-3"><input class="form-control" name="location" placeholder="Location"></div>
        <div class="col-md-3"><input class="form-control" name="photo" type="file" accept=".jpg,.jpeg,.png,.webp"></div>
        <div class="col-md-3"><select class="form-select" name="department_id"><option value="0">Department</option><?php foreach (($departments ?? []) as $department): ?><option value="<?= (int) $department['id'] ?>"><?= htmlspecialchars($department['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><select class="form-select" name="assigned_user_id"><option value="0">Assigned User</option><?php foreach (($users ?? []) as $person): ?><option value="<?= (int) $person['id'] ?>"><?= htmlspecialchars($person['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><input class="form-control" name="warranty_expiry" type="date"></div>
        <div class="col-md-3"><input class="form-control" name="vendor" placeholder="Vendor"></div>
        <div class="col-md-3"><input class="form-control" name="document" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"></div>
        <div class="col-12"><button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save Asset</button></div>
    </form>
    <table class="table data-table"><thead><tr><th>Tag</th><th>Name</th><th>Category</th><th>Department</th><th>Assigned</th><th>Status</th><th>Condition</th><th>QR</th></tr></thead><tbody><?php foreach (($assets ?? []) as $asset): ?><tr><td><?= htmlspecialchars($asset['asset_tag']) ?></td><td><?= htmlspecialchars($asset['name']) ?></td><td><?= htmlspecialchars($asset['category_name']) ?></td><td><?= htmlspecialchars($asset['department_name'] ?? '-') ?></td><td><?= htmlspecialchars($asset['assigned_name'] ?? '-') ?></td><td><span class="status-pill <?= in_array($asset['lifecycle_status'], ['Reserved','Maintenance','Under Maintenance'], true) ? 'warning' : '' ?>"><?= htmlspecialchars($asset['lifecycle_status']) ?></span></td><td><?= htmlspecialchars($asset['asset_condition']) ?></td><td><?= htmlspecialchars($asset['qr_code'] ?? 'Ready') ?></td></tr><?php endforeach; ?></tbody></table>
</section>
<div class="modal fade" id="assetDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Asset Details</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="timeline">
                    <div><strong>Registered</strong><span>Asset tag AF-0001 generated</span></div>
                    <div><strong>Allocated</strong><span>Assigned to employee with expected return</span></div>
                    <div><strong>Audited</strong><span>Verified during cycle</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
