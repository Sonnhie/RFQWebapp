<?php
    session_start();
    file_put_contents('debug.log', "Reached file\n", FILE_APPEND);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">RFQ Management</h2>
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
                    <div class="col-md-6 col-9">
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
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RFQ Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table rfq-table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>RFQ #</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Date Requested</th>
                                <th>Section</th>
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

       <!-- Email Supplier Modal -->
        <div class="modal fade" id="emailsupplier" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewdetails" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="emailForm">
                        <div class="modal-header">
                            <h5 class="modal-title">Email Supplier</h5>
                        </div>
                        <div class="modal-body">

                            <!-- Recipients -->
                            <div class="mb-3">
                                <label class="form-label">Recipient Emails</label>
                                <div id="recipients-group">
                                    <div class="input-group mb-2">
                                        <input type="email" name="recipients[]" class="form-control" placeholder="Enter recipient email">
                                        <button class="btn btn-outline-secondary remove-btn" type="button">Remove</button>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-success" id="add-recipient" type="button">+ Add Recipient</button>
                            </div>

                            <!-- CCs -->
                            <div class="mb-3">
                                <label class="form-label">CC Emails</label>
                                <div id="ccs-group">
                                    <div class="input-group mb-2">
                                        <input type="email" name="ccs[]" class="form-control" placeholder="Enter CC email">
                                        <button class="btn btn-outline-secondary remove-btn" type="button">Remove</button>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-success" id="add-cc" type="button">+ Add CC</button>
                            </div>

                            <!-- BCCs -->
                            <div class="mb-3">
                                <label class="form-label">BCC Emails</label>
                                <div id="bccs-group">
                                    <div class="input-group mb-2">
                                        <input type="email" name="bccs[]" class="form-control" placeholder="Enter BCC email">
                                        <button class="btn btn-outline-secondary remove-btn" type="button">Remove</button>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-success" id="add-bcc" type="button">+ Add BCC</button>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="sendemail">Send Email</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Comparison Modal -->
        <div class="modal fade" id="comparisonModal" tabindex="-1" aria-labelledby="comparisonModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form id="comparisonForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="comparisonModalLabel">Supplier Comparison for Items</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <h5 id="controlnumber">....</h4>
                                <h5 id="section"></h4>
                            </div>
                            <div class="row">
                                <div class="mb-3" id="itemDiv"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!--Comparison Table Modal -->
        <div class="modal fade" id="comparisonTableModal" tabindex="-1" aria-labelledby="comparisonTableModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="comparisonTableModalLabel">Comparison Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <div class="table-responsive">
                    <table class="table rfq-table table-hover" id="comparisonTable">
                    <thead>
                        <tr>
                        <th>Item</th>
                        <th>Supplier</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Total Price</th>
                        <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="comparisonModalTableBody">
                        <!-- Dynamic content will be inserted here -->
                    </tbody>
                    </table>
                </div>
                </div>
                <div class="modal-footer">
                <!-- <button type="button" class="btn btn-success" id="approvedbtn">Approve Comparison</button>
                <button type="button" class="btn btn-danger" id="holdbtn">Decline Comparison</button> -->
                
                <button type="button" class="btn btn-danger" id="deletebtn">Delete Comparison</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
            </div>
        </div>


        <script src="./includes/js/approval_module.js"></script>

        <?php
            // Include the footer
            include_once '../components/footer.php';
        ?>