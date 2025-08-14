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
                echo htmlspecialchars($_SESSION['user']['name']);
            ?>
        </h5>
        <div class="d-flex align-items-center">
            <img src="./assets/img/profile.png" alt="User Profile" class="rounded-circle" width="40" height="40">
            <span class="ms-2 fw-semibold">
                <?php
                   if ($_SESSION['user']['access_level'] == 'Admin') {
                        echo 'Administrator';
                    } else {
                        echo 'Client';
                    }
                ?>
            </span>
        </div>
    </div>
</div>
<div class="col-12">
    <div class="row mb-4">
        <h5 class="">Comparison CheckList</h5>
    </div>
    <div class="row mb-4">
          <div class="card shadow-sm p-2">
                <div class="row mb-2 p-2">
                    <h5 class="text-muted fw-bold">Filters</h5>
                </div>  
                <div class="row p-2">
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

                    <div class="col-sm-2 col-6">
                        <label for="show" class="form-label small fw-bold text-muted mb-1">Show:</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-chevron-down text-primary"></i>
                            </span>
                            <select name="" class="form-select" id="showlimit">
                                <option value="10" >10</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <!-- Search Input -->
                    <div class="col-md-3 col-6">
                        <label for="searchInput" class="form-label small fw-bold text-muted mb-1">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search....">
                            <button class="btn btn-primary" type="button" id="searchButton">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
          </div>
    </div>

    <div class="row">
        <table class="table rfq-table table-hover "  data-section="<?php echo $_SESSION['user']['department']; ?>">
            <thead>
                <tr>
                    <th>Request No.</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Department</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="comparisonTable">

            </tbody>
        </table>
        <div>
            <tr>
                <td colspan="10" class="text-center">
                    <nav aria-label="Page navigation example">
                        <ul class="pagination" id="pagination">

                        </ul>
                    </nav>
                </td>
            </tr>
        </div>
    </div>
</div>
        <!--Comparison Table Modal -->
<div class="modal fade" id="comparisonTableModal" tabindex="-1" aria-labelledby="comparisonTableModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="comparisonTableModalLabel">Comparison Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <div class="table-responsive">
                    <table class="table rfq-table table-bordered table-hover" id="comparisonTable">
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
                
                <!-- <button type="button" class="btn btn-danger" id="deletebtn">Delete Comparison</button> -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
            </div>
        </div>

        <!-- Comparison Modal -->
        <div class="modal fade" id="comparisonModal" tabindex="-1" aria-labelledby="comparisonModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form id="comparisonEditForm" method="POST">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="comparisonModalLabel">Supplier Comparison for Items</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="input-group"></div>
                            </div>
                            <div class="row">
                                <table class="table rfq-table table-bordered table-hover" id="comparisonTable">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Supplier</th>
                                            <th>Price</th>
                                            <th>Discount</th>
                                            <th>Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemDiv" data-role="<?php echo $_SESSION['user']['access_level'];?>" data-name="<?php echo htmlspecialchars($_SESSION['user']['name']);?>">

                                    </tbody>
                                </table>                              
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<script src="./includes/js/comparison_checklist.js"></script>