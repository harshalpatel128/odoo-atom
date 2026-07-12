<section class="kpi-grid">
    <div class="kpi"><i class="bi bi-boxes"></i><span>Total Assets</span><strong><?= (int) ($counts['total_assets'] ?? 0) ?></strong></div>
    <div class="kpi"><i class="bi bi-check2-circle"></i><span>Assets Available</span><strong><?= (int) $counts['available'] ?></strong></div>
    <div class="kpi"><i class="bi bi-person-check"></i><span>Assets Allocated</span><strong><?= (int) $counts['allocated'] ?></strong></div>
    <div class="kpi"><i class="bi bi-tools"></i><span>Under Maintenance</span><strong><?= (int) ($counts['maintenance_assets'] ?? 0) ?></strong></div>
    <div class="kpi"><i class="bi bi-archive"></i><span>Retired Assets</span><strong><?= (int) ($counts['retired_assets'] ?? 0) ?></strong></div>
    <div class="kpi"><i class="bi bi-exclamation-triangle"></i><span>Lost Assets</span><strong><?= (int) ($counts['lost_assets'] ?? 0) ?></strong></div>
    <div class="kpi"><i class="bi bi-calendar-event"></i><span>Today's Bookings</span><strong><?= (int) ($counts['todays_bookings'] ?? 0) ?></strong></div>
    <div class="kpi"><i class="bi bi-hourglass-split"></i><span>Pending Bookings</span><strong><?= (int) ($counts['pending_bookings'] ?? 0) ?></strong></div>
    <div class="kpi"><i class="bi bi-arrow-left-right"></i><span>Pending Transfers</span><strong><?= (int) $counts['pending_transfers'] ?></strong></div>
    <div class="kpi"><i class="bi bi-wrench-adjustable"></i><span>Pending Maintenance</span><strong><?= (int) ($counts['pending_maintenance'] ?? 0) ?></strong></div>
    <div class="kpi"><i class="bi bi-clock-history"></i><span>Upcoming Returns</span><strong><?= (int) $counts['upcoming_returns'] ?></strong></div>
    <div class="kpi"><i class="bi bi-clipboard-check"></i><span>Pending Audits</span><strong><?= (int) ($counts['pending_audits'] ?? 0) ?></strong></div>
</section>
