<?php require __DIR__ . '/../partials/page_tools.php'; ?>
<section class="panel">
    <div class="section-head">
        <h2>Allocated Assets</h2>
        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#assetAckModal"><i class="bi bi-check2-circle"></i> Acknowledge</button>
    </div>
    <table class="table data-table">
        <thead><tr><th>Asset</th><th>Tag</th><th>Category</th><th>Status</th><th>Condition</th><th>Expected Return</th><th>Action</th></tr></thead>
        <tbody>
            <tr><td>Dell Latitude Laptop</td><td>AF-0001</td><td>Electronics</td><td><span class="status-pill">Allocated</span></td><td>Good</td><td>2026-08-15</td><td><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#assetAckModal">View</button></td></tr>
            <tr><td>Meeting Room Projector</td><td>AF-0002</td><td>Shared Equipment</td><td><span class="status-pill warning">Reserved</span></td><td>Good</td><td>2026-07-18</td><td><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#assetAckModal">View</button></td></tr>
        </tbody>
    </table>
</section>
<div class="modal fade" id="assetAckModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Asset Acknowledgement</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="timeline">
                    <div><strong>Assigned</strong><span>Employee receives asset with condition notes.</span></div>
                    <div><strong>Acknowledged</strong><span>Acceptance confirms custody until return or transfer.</span></div>
                    <div><strong>Return Due</strong><span>Expected return reminders appear before due date.</span></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary" type="button" data-bs-dismiss="modal">Acknowledge</button></div>
        </div>
    </div>
</div>
