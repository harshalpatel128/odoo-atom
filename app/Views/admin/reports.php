<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <h2>Report Filters</h2>
    <form class="grid-form" method="get">
        <input class="form-control" name="search" placeholder="Search assets, serials, vendors" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <select class="form-select" name="department_id">
            <option value="">All departments</option>
            <?php foreach ($departments as $department): ?><option value="<?= (int) $department['id'] ?>" <?= (string) ($_GET['department_id'] ?? '') === (string) $department['id'] ? 'selected' : '' ?>><?= htmlspecialchars($department['name']) ?></option><?php endforeach; ?>
        </select>
        <select class="form-select" name="category_id">
            <option value="">All categories</option>
            <?php foreach (\App\Models\AssetCategory::all() as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string) ($_GET['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option><?php endforeach; ?>
        </select>
        <select class="form-select" name="status">
            <option value="">All statuses</option>
            <?php foreach (['Available','Allocated','Reserved','Under Maintenance','Lost','Retired','Disposed'] as $status): ?><option value="<?= htmlspecialchars($status) ?>" <?= ($_GET['status'] ?? '') === $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-primary">Apply</button>
        <button class="btn btn-outline-primary" type="button" onclick="window.print()">Print / PDF</button>
    </form>
</section>

<?php
$tables = [
    'Asset Report' => $reports['assets'] ?? [],
    'Allocation Report' => $reports['allocations'] ?? [],
    'Transfer Report' => $reports['transfers'] ?? [],
    'Booking Report' => $reports['bookings'] ?? [],
    'Maintenance Report' => $reports['maintenance'] ?? [],
    'Audit Report' => $reports['audits'] ?? [],
    'Department Report' => $reports['departments'] ?? [],
    'Employee Report' => $reports['employees'] ?? [],
];
?>
<?php foreach ($tables as $heading => $rows): ?>
    <section class="panel mt-4">
        <div class="section-head">
            <h2><?= htmlspecialchars($heading) ?></h2>
            <button class="btn btn-outline-primary btn-sm js-export-csv" type="button">Export CSV</button>
            <button class="btn btn-outline-secondary btn-sm js-export-excel" type="button">Export Excel</button>
        </div>
        <table class="table data-table exportable">
            <thead><tr><?php foreach (array_keys($rows[0] ?? ['empty' => '']) as $column): ?><th><?= htmlspecialchars(str_replace('_', ' ', ucfirst($column))) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
                <?php foreach ($rows as $row): ?><tr><?php foreach ($row as $value): ?><td><?= htmlspecialchars((string) $value) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
                <?php if (!$rows): ?><tr><td>No report data found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
<?php endforeach; ?>

<section class="grid-3 mt-4">
    <?php foreach (($reports['summary'] ?? []) as $heading => $rows): ?>
        <div class="panel">
            <h2><?= htmlspecialchars(ucfirst($heading)) ?></h2>
            <?php foreach ($rows as $row): ?>
                <div class="metric-row"><span><?= htmlspecialchars($row['name']) ?></span><strong><?= htmlspecialchars((string) ($row['assets'] ?? $row['value'] ?? 0)) ?></strong></div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</section>
