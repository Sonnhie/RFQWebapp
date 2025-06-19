<?php
    session_start();
    file_put_contents('debug.log', "Reached file\n", FILE_APPEND);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
?>
<div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Request Check List</h2>
</div>
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body p-3 bg-light">
                <div class="row g-3 align-items-end">
             
                    <!-- Status Filter -->
                    <div class="col-md-3 col-6">
                        <label for="statusFilter" class="form-label small fw-bold text-muted mb-1">STATUS</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-filter-circle text-primary"></i>
                            </span>
                            <select class="form-select border-start-0" id="statusFilter">
                                <option value="" selected>All Statuses</option>
                                <option value="Pending" class="text-warning">Pending</option>
                                <option value="Approved" class="text-success">Approved</option>
                                <option value="Rejected" class="text-danger">Rejected</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Date Range Filter - Enhanced -->
                    <div class="col-md-3 col-6">
                        <label for="fromDateFilter" class="form-label small fw-bold text-muted mb-1">From</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-calendar3 text-primary"></i>
                            </span>
                            <input type="date" class="form-control" id="fromDateFilter" name="from_date">
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <label for="toDateFilter" class="form-label small fw-bold text-muted mb-1">To</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-calendar3 text-primary"></i>
                            </span>
                            <input type="date" class="form-control" id="toDateFilter" name="to_date">
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="col-md-3 col-6">
                        <label for="searchInput" class="form-label small fw-bold text-muted mb-1">SEARCH</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="RFQ #, Title...">
                            <button class="btn btn-primary" type="button" id="searchButton">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                    <!-- Action Buttons Row -->
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger btn-sm me-3">
                                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                                </button>
                                <!-- <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-funnel me-1"></i> Apply Filters
                                </button> -->
                            </div>
                        </div>
                    </div>
            
            </div>
        </div>

        <!-- RFQ Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table rfq-table table-hover">
                        <thead>
                            <tr>
                                <th>RFQ #</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Date Requested</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="requestTableBody" data-section="<?php echo $_SESSION['user']['department']; ?>">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="10" class="text-center">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination" id="pagination">

                                        </ul>
                                    </nav>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>


        <!-- Attachments Modal -->
        <div class="modal fade rfq-modal" id="attachmentRfqModal" data-itemId=""  tabindex="-1" aria-labelledby="attachmentRfqModall" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attachmentRfqModal">
                            <i class="bi bi-images me-2"></i> Attachment Viewer
                        </h5>
                        <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                        <div class="d-flex justify-content-center align-items-center" style="height: 500px;">
                            <img id="attachment_viewer" src="" class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: contain;" />
                        </div>
                        </div>
                    </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

         <!-- items Modal -->
         <div class="modal fade rfq-modal" id="itemsRfqModal" data-itemId=""  tabindex="-1" aria-labelledby="itemsRfqModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="itemsRfqModal">
                            <i class="bi bi-card-checklist me-2"></i> Item List
                        </h5>
                        <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="table-responsive">
                                <table class="table rfq-table table-hover" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Description</th>
                                            <th>Purpose</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>View Item</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                    </tbody>
                                </table>
                            </div>    
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
                </div>     
            </div>
            </div>
        </div>

        <script src="./includes/js/history_module.js"></script>

<?php
    // Include the footer
    include_once '../components/footer.php';
?>
