<section class="tool-strip">
    <div class="input-group search-box">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input class="form-control js-page-search" type="search" placeholder="Search this page">
    </div>
    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#filterModal">
        <i class="bi bi-funnel"></i> Filters
    </button>
    <button class="btn btn-outline-secondary js-show-spinner" type="button">
        <i class="bi bi-arrow-clockwise"></i> Refresh
    </button>
</section>

<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Filters</h2>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Status</label><select class="form-select"><option>All</option><option>Active</option><option>Pending</option><option>Closed</option></select></div>
                    <div class="col-md-6"><label class="form-label">Department</label><select class="form-select"><option>All</option><option>IT</option><option>Facilities</option><option>Corporate</option></select></div>
                    <div class="col-md-6"><label class="form-label">From</label><input class="form-control" type="date"></div>
                    <div class="col-md-6"><label class="form-label">To</label><input class="form-control" type="date"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Reset</button>
                <button class="btn btn-primary" type="button" data-bs-dismiss="modal">Apply</button>
            </div>
        </div>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlay" aria-hidden="true">
    <div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading</span></div>
</div>
