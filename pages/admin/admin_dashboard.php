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
            <p class="text-muted">admin dashboard</p>
        </div>