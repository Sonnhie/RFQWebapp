<div class="dashboard-header mb-4">
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-muted">Overview of your RFQ activities</p>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted">TOTAL RFQs</h6>
                                <h2 class="mb-0" id="totalrfq">48</h2>
                            </div>
                            <i class="bi bi-file-earmark-text fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted">PENDING</h6>
                                <h2 class="mb-0" id="pending">12</h2>
                            </div>
                            <i class="bi bi-hourglass-split fs-3 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted">APPROVED</h6>
                                <h2 class="mb-0" id="approved">32</h2>
                            </div>
                            <i class="bi bi-check-circle fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted">REJECTED</h6>
                                <h2 class="mb-0" id="rejected">4</h2>
                            </div>
                            <i class="bi bi-x-circle fs-3 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chart Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">RFQ Status Overview</h5>
                <p class="text-muted">This chart shows the status of your RFQs over the past 12 months.</p>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="yearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                         <span id="selectedYear"><i class="bi bi-calendar me-2"></i> <?php echo date("Y"); ?></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="yearDropdown" id="yearDropdownMenu">
                    <?php 
                        $currentYear = date("Y");
                        for ($year = $currentYear; $year >= $currentYear - 1; $year--) {
                            $activeClass = ($year == $currentYear) ? "active" : "";
                            echo "<li><a class='dropdown-item year-option $activeClass' data-year='{$year}' href='#'>{$year}</a></li>";
                        }
                    ?>
                    </ul>
                </div>
                <div class="chart-container" data-section= "<?php echo $_SESSION['user']['department']; ?>">
                    <canvas id="rfqChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            Recent RFQs
                            <a href="#" class="btn btn-sm btn-outline-primary" id="viewall" data-page="history">View All</a>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>RFQ #</th>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="rfqTableBody" data-section="<?php echo $_SESSION['user']['department']; ?>">
                                    
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
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" id="newRfqBtn">
                                <i class="bi bi-file-earmark-plus me-2"></i> Create New RFQ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <!-- New RFQ Modal -->
         <div class="modal fade rfq-modal" id="newRfqModal" tabindex="-1" aria-labelledby="newRfqModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
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
                                                        <option value="Piece">Piece</option>
                                                        <option value="Box">Box</option>
                                                        <option value="Meter">Meter</option>
                                                        <option value="Set">Set</option>
                                                        <option value="Gallon">Gallon</option>
                                                        <option value="Sack">Sack</option>
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