<?php
    session_start();
    file_put_contents('debug.log', "Reached file\n", FILE_APPEND);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
?>
<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Welcome, 
            <?php
                if ($_SESSION['user']['access_level'] == 'Admin') {
                    echo 'Administrator';
                } else {
                    echo 'Client';
                }
            ?>
        </h5>
        <div class="d-flex align-items-center">
            <img src="./assets/img/profile.png" alt="User Profile" class="rounded-circle" width="40" height="40">
            <span class="ms-2 fw-semibold">
                <?php
                    echo htmlspecialchars($_SESSION['user']['name']);
                ?>
            </span>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Request for Quotations</h2>
            <button class="btn btn-primary btn-new-rfq" data-bs-toggle="modal" data-bs-target="#newRfqModal">
                <i class="bi bi-plus-circle me-2"></i> Create New RFQ
            </button>
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
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Purpose</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Date Requested</th>
                                <th>Actions</th>
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
        
        <!-- New RFQ Modal -->
        <div class="modal fade rfq-modal" id="newRfqModal" tabindex="-1" aria-labelledby="newRfqModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="newRfqModalLabel">
                            <i class="bi bi-file-earmark-plus me-2"></i> Create New RFQ
                        </h5>
                        <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" method="post" id="create_request" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row mb-3">       
                                <div class="col-md-6">
                                    <label for="requestor" class="form-label">Requestor</label>
                                    <input type="text" class="form-control" name="requestor_name" id="requestor" value="<?php echo $_SESSION['user']['name']; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="section" class="form-label">Requestor Department</label>
                                    <input type="text" class="form-control" name="requestor_section" id="department" value="<?php echo $_SESSION['user']['department']; ?>"  readonly>
                                </div>
                            </div>
                            <!-- <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="purpose" class="form-label">Purpose</label>
                                    <input type="text" class="form-control" name="item_purpose" id="purpose" placeholder="Purchase purpose" required>
                                </div>
                            </div> -->
                            
                            <div class="mb-3">
                                <label class="form-label">Items Requested</label>
                            
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Description</th>
                                                <th>Purpose</th>
                                                <th>Quantity</th>
                                                <th>Unit</th>
                                                <th>Attachment</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                            <tr>
                                                <td><input type="text" class="form-control form-control-sm" name="item_name[]" placeholder="Item name" required></td>
                                                <td><input type="text" class="form-control form-control-sm" name="item_description[]" placeholder="Description" required></td>
                                                <td><input type="text" class="form-control" name="item_purpose[]" id="purpose" placeholder="Purchase purpose" required></td>
                                                <td><input type="number" class="form-control form-control-sm"  name="item_quantity[]" placeholder="Qty" required></td>
                                                <td>
                                                    <select class="form-select form-select-sm" name="item_unit[]">
                                                        <option value="">Select Unit</option>
                                                        <option value="Piece">Piece</option>
                                                        <option value="Box">Box</option>
                                                        <option value="Meter">Meter</option>
                                                        <option value="Set">Set</option>
                                                        <option value="Gallon">Gallon</option>
                                                        <option value="Sack">Sack</option>
                                                        <option value="Pack">Pack</option>
                                                        <option value="Roll">Roll</option>
                                                        <option value="Liter">Liter</option>
                                                        <option value="Milliliter">Milliliter</option>
                                                        <option value="Kilogram">Kilogram</option>
                                                        <option value="Gram">Gram</option>
                                                        <option value="Pound">Pound</option>
                                                        <option value="Ounce">Ounce</option>
                                                        <option value="Can">Can</option>
                                                        <option value="Bottle">Bottle</option>
                                                        <option value="Bag">Bag</option>
                                                        <option value="Carton">Carton</option>
                                                        <option value="Dozen">Dozen</option>
                                                        <option value="Pair">Pair</option>
                                                        <option value="Feet">Feet</option>
                                                        <option value="Inch">Inch</option>
                                                        <option value="Yard">Yard</option>
                                                        <option value="Sheet">Sheet</option>
                                                        <option value="Tube">Tube</option>
                                                        <option value="Bundle">Bundle</option>
                                                        <option value="Ream">Ream</option>
                                                        <option value="Tablet">Tablet</option>
                                                        <option value="Strip">Strip</option>
                                                        <option value="Kit">Kit</option>
                                                        <option value="Case">Case</option>
                                                        <option value="Tray">Tray</option>
                                                    </select>
                                                </td>
                                                <td><input class="form-control" type="file" id="attachment" name="item-attachment[]" required></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-danger" id="removeItemButton" type="button">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addItemButton">
                                    <i class="bi bi-plus-circle me-1"></i> Add Item
                                </button>
                            </div>
                            
                            <!-- <div class="mb-3">
                                <label for="attachment" class="form-label">Attachments</label>
                                <input class="form-control" type="file" id="attachment" name="item-attachment[]" multiple required>
                                <small class="text-muted">Upload specifications, drawings, or other documents</small>
                            </div> -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit RFQ</button>
                        </div>
                        </div>
                    </form>
            </div>
        </div>

        <!-- Edit RFQ Modal -->
        <div class="modal fade rfq-modal" id="editRfqModal" data-itemId=""  tabindex="-1" aria-labelledby="editRfqModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="editrfqModalLabel">
                            <i class="bi bi-file-earmark-plus me-2"></i> Edit RFQ
                        </h5>
                        <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" method="post" id="edit_request" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row mb-3">       
                                <div class="col-md-6">
                                    <label for="item_name" class="form-label">Item Name</label>
                                    <input type="text" class="form-control" name="item_name" id="item_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="requestor" class="form-label">Item Description</label>
                                    <input type="text" class="form-control" name="item_description" id="item_description" required>
                                </div>
                            </div>
                            <div class="row mb-3">       
                                <div class="col-md-12">
                                    <label for="item_purpose" class="form-label">Purpose</label>
                                    <input type="text" class="form-control" name="item_purpose" id="item_purpose" required>
                                </div>
                            </div>
                            <div class="row mb-3">       
                                <div class="col-md-6">
                                    <label for="item_quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" name="item_quantity" id="item_quantity" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="item_unit" class="form-label">Unit</label>
                                    <select class="form-select" name="item_unit" id="item_unit">
                                        <option value="">Select Unit</option>
                                        <option value="Piece">Piece</option>
                                        <option value="Box">Box</option>
                                        <option value="Meter">Meter</option>
                                        <option value="Set">Set</option>
                                        <option value="Gallon">Gallon</option>
                                        <option value="Sack">Sack</option>
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="row mb-3">       
                                <div class="col-md-12">
                                    <label for="item_attachment" class="form-label">Attachment</label>
                                    <input class="form-control" type="file" id="item_attachment" name="item_attachment" required>
                                    <small class="text-muted">Upload specifications, drawings, or other documents</small>
                                </div>
                            </div> -->
 
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit RFQ</button>
                        </div>
                        </div>
                    </form>
            </div>
        </div>

        <!-- Attachments Modal -->
        <div class="modal fade rfq-modal" id="attachmentRfqModal" data-itemId=""  tabindex="-1" aria-labelledby="attachmentRfqModall" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
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

        <script src="./includes/js/request_module.js"></script>

<?php
    // Include the footer
    include_once '../components/footer.php';
?>
