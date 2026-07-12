<section class="kpi-grid">
    <div class="kpi"><span>Total Assets</span><strong><?= (int) ($counts['total_assets'] ?? 0) ?></strong></div>
    <div class="kpi"><span>Assets Available</span><strong><?= (int) $counts['available'] ?></strong></div>
    <div class="kpi"><span>Assets Allocated</span><strong><?= (int) $counts['allocated'] ?></strong></div>
    <div class="kpi"><span>Under Maintenance</span><strong><?= (int) ($counts['maintenance_assets'] ?? 0) ?></strong></div>
    <div class="kpi"><span>Retired Assets</span><strong><?= (int) ($counts['retired_assets'] ?? 0) ?></strong></div>
    <div class="kpi"><span>Lost Assets</span><strong><?= (int) ($counts['lost_assets'] ?? 0) ?></strong></div>
    <div class="kpi"><span>Today's Bookings</span><strong><?= (int) ($counts['todays_bookings'] ?? 0) ?></strong></div>
    <div class="kpi"><span>Pending Bookings</span><strong><?= (int) ($counts['pending_bookings'] ?? 0) ?></strong></div>
    <div class="kpi"><span>Pending Transfers</span><strong><?= (int) $counts['pending_transfers'] ?></strong></div>
    <div class="kpi"><span>Pending Maintenance</span><strong><?= (int) ($counts['pending_maintenance'] ?? 0) ?></strong></div>
    <div class="kpi"><span>Upcoming Returns</span><strong><?= (int) $counts['upcoming_returns'] ?></strong></div>
    <div class="kpi"><span>Pending Audits</span><strong><?= (int) ($counts['pending_audits'] ?? 0) ?></strong></div>
</section>
