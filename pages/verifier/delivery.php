<?php
    session_start();
?>

<!--Header-->
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

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Deliveries</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDeliveryModal">
    <i class="bi bi-plus-circle me-1"></i> Add Delivery
    </button>
</div>

<div class="card shadow-sm">
      <div class="card-body">
         <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="col-md-3 col-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Enter control number" autocomplete="off">
                    <button class="btn btn-primary" type="button" id="searchButton">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Control Number</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Supplier</th>
                <th>Price</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Delivery Date</th>
                <th>Received Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="DeliveryItemTable">
             
            </tbody>
            <tfoot>
                <tr>
                  <td colspan="10" class="text-center">
                      <nav aria-label="Page navigation">
                          <ul class="pagination" id="pagination"></ul>
                      </nav>
                  </td>
                </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

 <!-- Add Delivery Modal -->
  <div class="modal fade" id="addDeliveryModal" tabindex="-1" aria-labelledby="addDeliveryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content shadow">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="addDeliveryLabel">New Delivery Record</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="#" method="post" id="deliveryForm">
          <div class="modal-body" >
          <div class="row mb-3">
          <div class="col-8">
            <div class="input-group mb-2">
              <span class="input-group-text">RFQ Number</span>
              <input type="text" class="form-control" id="rfqSearchInput" placeholder="Enter RFQ number">
              <button type="button" class="btn btn-outline-primary" id="loadItemsBtn">
              <i class="bi bi-arrow-repeat"></i> Load Items
              </button>
            </div>
            <div id="rfqSuggestionList" class="list-group position-absolute z-3 w-50 " style="max-height: 200px; overflow-y: auto;"></div>
          </div>
          </div>
              <div class="table-responsive">
            <table class="table table-hover" >
                <thead>
            <tr>
                <th>Item Name</th>
                <th>Item Description</th>
                <th>Quantity</th>
                <th>Supplier</th>
                <th>Total Amount</th>
                <th>Delivery Date</th>
            </tr>
                </thead>
                <tbody id="deliveryitems">

                </tbody>
            </table>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Save Delivery</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!--Edit delivery item modal-->
  <div class="modal fade" id="editDeliveryModal" tabindex="-1" aria-labelledby="editDeliveryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content shadow">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="editDeliveryLabel">Edit Delivery Record</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="#" method="post" id="editdeliveryForm">
          <div class="modal-body" >
              <div class="row mb-3">
                  <div class="col-6">
                      <div class="input-group">
                          <span class="input-group-text">Item Name</span>
                          <input type="text" class="form-control" id="item_name" readonly>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="input-group">
                          <span class="input-group-text">Item Quantity</span>
                          <input type="number" class="form-control" id="item_quantity" readonly>
                      </div>
                  </div>
              </div>
              <div class="row mb-3">
                  <div class="col-6">
                      <div class="input-group">
                          <span class="input-group-text">Suppier Name</span>
                          <input type="text" class="form-control" name="supplier_name">
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="input-group">
                          <span class="input-group-text">Total Amount</span>
                          <input type="text" class="form-control" name="item_amount">
                      </div>
                  </div>
              </div>
              <div class="row">
                  <div class="col-8">
                      <div class="input-group">
                          <span class="input-group-text">Delivery Date</span>
                          <input type="date" class="form-control" name="delivery_date">
                      </div>                      
                  </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Save Delivery</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="./includes/js/delivery_module.js"></script>