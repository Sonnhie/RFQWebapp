<?php
    session_start();
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
<div class="mb-4">
    <h4 class="fw-bold">Settings</h4>
    <p class="text-muted">Manage your account settings and preferences.</p>
    <!-- <hr> -->
    <div class="row mt-3">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Exchange Rate</h5>
                    <p class="card-text">Set the current exchange rate for USD to PHP.</p>
                    <form id="pesoexchangeRateForm" method="post">
                        <div class="mb-1">
                            <label for="exchangeRate" class="form-label">Exchange Rate (1 USD to PHP)</label>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-10 ">
                                <div class="input-group">
                                    <span class="input-group-text">PHP</span>
                                    <input type="number" step="0.0000001" min="0.000001" step="0.01" max="50000" name="currency" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-custom" id="update_currency_php">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                    <form id="usdexchangerateform" method="post">
                        <div class="mb-1">
                            <label for="exchangeRate" class="form-label">Exchange Rate (1 PHP to USD)</label>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-10 ">
                                <div class="input-group">
                                    <span class="input-group-text">USD</span>
                                    <input type="number" step="0.0000001" min="0.000001" step="0.0001" max="50000" name="currency" class="form-control" value="">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-custom" id="update_currency_usd">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="./includes/js/settings.js"></script>