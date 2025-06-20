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
<div class="dashboard-header">
    <h1 class="h3 mb-0">Admin Dashboard</h1>
    <p class="text-muted">Overview of system activity</p>
</div>
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                            <h6 class="card-title text-muted">Pending RFQs</h6>
                            <h2 class="mb-0" id="pendingRfqs">18</h2>
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
                            <h6 class="card-title text-muted">Approved RFQs</h6>
                            <h2 class="mb-0" id="approvedRfqs">220</h2>
                            </div>
                            <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                            <h6 class="card-title text-muted">Rejected RFQs</h6>
                            <h2 class="mb-0" id="rejectedRfqs">45</h2>
                            </div>
                            <i class="bi bi-x-circle-fill fs-3 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                            <h6 class="card-title text-muted">Avg Approval Time</h6>
                            <h2 class="mb-0" id="avgApprovalTime">2.3d</h2>
                            </div>
                            <i class="bi bi-clock-history fs-3 text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<div class="row mb-4">
        <!-- Chart Section -->
         <div class="col-md-8 mb-4">
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
         </div>
    <!-- Status Pie Chart -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                <h6 class="mb-0">RFQ Status Distribution</h6>
                </div>
                <div class="card-body">
                <canvas id="statusChart" style="max-height: 388px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

 <!-- Latest RFQs Table -->
  <div class="row mb-5">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Latest RFQs</h6>
          <button class="btn btn-sm btn-outline-primary">View All</button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead class="table-light">
                <tr>
                  <th>RFQ ID</th>
                  <th>Item</th>
                  <th>Requestor</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>RFQ00123</td>
                  <td>Motor X12</td>
                  <td>John D.</td>
                  <td>2025-06-20</td>
                  <td><span class="badge bg-warning">Pending</span></td>
                  <td><button class="btn btn-sm btn-primary">View</button></td>
                </tr>
                <tr>
                  <td>RFQ00122</td>
                  <td>Cable Y3</td>
                  <td>Maria S.</td>
                  <td>2025-06-19</td>
                  <td><span class="badge bg-success">Approved</span></td>
                  <td><button class="btn btn-sm btn-primary">View</button></td>
                </tr>
                <!-- Add more rows dynamically -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Export Buttons -->
  <div class="row mb-4">
    <div class="col-12 text-end">
      <button class="btn btn-success me-2"><i class="bi bi-file-earmark-excel"></i> Export to Excel</button>
      <button class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> Export to PDF</button>
    </div>
  </div>
</div>

<script>
  // RFQ Submission Line Chart
  const rfqChart = new Chart(document.getElementById('rfqChart'), {
    type: 'line',
    data: {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      datasets: [{
        label: 'RFQs Submitted',
        data: [5, 8, 6, 12, 10, 4, 7],
        borderColor: 'rgba(54, 162, 235, 1)',
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true }
      }
    }
  });

  // RFQ Status Pie Chart
  const statusChart = new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
      labels: ['Pending', 'Approved', 'Rejected'],
      datasets: [{
        label: 'Status',
        data: [18, 220, 45],
        backgroundColor: [
          'rgba(255, 193, 7, 0.7)',
          'rgba(40, 167, 69, 0.7)',
          'rgba(220, 53, 69, 0.7)'
        ],
        borderColor: [
          'rgba(255, 193, 7, 1)',
          'rgba(40, 167, 69, 1)',
          'rgba(220, 53, 69, 1)'
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });
</script>
