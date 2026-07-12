<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#departments" type="button">Departments</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#categories" type="button">Categories</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#employees" type="button">Employees</button></li>
    </ul>
    <div class="tab-content pt-4">
        <div class="tab-pane fade show active" id="departments">
            <form class="row g-3 mb-4" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/departments">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-3"><input class="form-control" name="name" placeholder="Department Name" required></div>
                <div class="col-md-3"><select class="form-select" name="head_user_id"><option value="0">Department Head</option><?php foreach ($users as $person): ?><option value="<?= (int) $person['id'] ?>"><?= htmlspecialchars($person['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><select class="form-select" name="parent_id"><option value="0">Parent Department</option><?php foreach (($departments ?? []) as $department): ?><option value="<?= (int) $department['id'] ?>"><?= htmlspecialchars($department['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="col-12"><button class="btn btn-primary" type="button"><i class="bi bi-plus-circle"></i> Add Department</button></div>
            </form>
            <table class="table data-table"><thead><tr><th>Name</th><th>Head</th><th>Parent</th><th>Status</th></tr></thead><tbody><?php foreach (($departments ?? []) as $department): ?><tr><td><?= htmlspecialchars($department['name']) ?></td><td><?= htmlspecialchars($department['head_name'] ?? 'Unassigned') ?></td><td><?= htmlspecialchars($department['parent_name'] ?? '-') ?></td><td><?= htmlspecialchars($department['status']) ?></td></tr><?php endforeach; ?></tbody></table>
        </div>
        <div class="tab-pane fade" id="categories">
            <form class="row g-3 mb-4" method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/categories">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-3"><input class="form-control" name="name" placeholder="Category Name" required></div>
                <div class="col-md-3"><input class="form-control" name="description" placeholder="Description"></div>
                <div class="col-md-3"><input class="form-control" name="custom_fields_json" placeholder='{"warranty_period":"text"}'></div>
                <div class="col-md-3"><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="col-12"><button class="btn btn-primary" type="button"><i class="bi bi-plus-circle"></i> Add Category</button></div>
            </form>
            <table class="table data-table"><thead><tr><th>Category</th><th>Description</th><th>Status</th><th>Custom Fields</th></tr></thead><tbody><?php foreach (($categories ?? []) as $category): ?><tr><td><?= htmlspecialchars($category['name']) ?></td><td><?= htmlspecialchars($category['description'] ?? '-') ?></td><td><?= htmlspecialchars($category['status']) ?></td><td><?= htmlspecialchars($category['custom_fields_json'] ?? '-') ?></td></tr><?php endforeach; ?></tbody></table>
        </div>
        <div class="tab-pane fade" id="employees">
            <table class="table data-table">
                <thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Role</th><th>Status</th><th>Promote</th></tr></thead>
                <tbody>
                <?php foreach ($users as $employee): ?>
                    <tr>
                        <td><?= htmlspecialchars($employee['name']) ?></td>
                        <td><?= htmlspecialchars($employee['email']) ?></td>
                        <td><?= htmlspecialchars($employee['department_name'] ?? 'Unassigned') ?></td>
                        <td><?= htmlspecialchars($employee['role_name']) ?></td>
                        <td><?= htmlspecialchars($employee['status']) ?></td>
                        <td>
                            <?php if ($employee['email'] !== 'admin@arms.local'): ?>
                            <form method="post" action="<?= htmlspecialchars($config['base_url']) ?>/admin/users/promote" class="d-flex gap-2">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="user_id" value="<?= (int) $employee['id'] ?>">
                                <select name="role" class="form-select form-select-sm">
                                    <option value="employee">Employee</option>
                                    <option value="department_head">Department Head</option>
                                    <option value="asset_manager">Asset Manager</option>
                                </select>
                                <button class="btn btn-sm btn-primary">Save</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
