<?php
    session_start();
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
            <h2 class="mb-0">Request Timeline</h2>
</div>
<div class="card shadow-sm mb-4 border-0">
    <div class="card-body bg-light">
        <div class="row mb-5">
            <div class="col-md-6 col-lg-4">
                <form id="controlNumberForm" class="input-group mb-2">
                    <input type="text" class="form-control" id="controlNumberInput" placeholder="Enter Control Number" aria-label="Control Number" autocomplete="off">
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
                <div id="rfqSuggestionList" class="list-group position-absolute z-3 w-50 " style="max-height: 200px; overflow-y: auto;"></div>
            </div>
        </div>
     
            <!-- Timeline Section -->
            <div id="timelineSection" style="display:none;">
                <ul class="timeline d-flex justify-content-start flex-row overflow-auto p-2">
                    <!-- Timeline items will be dynamically inserted here -->
                </ul>
            </div>
            <!-- Optionally, show a message if no timeline is found -->
            <div id="noTimelineMessage" class="text-muted" style="display:none;">
                No timeline found for this control number.
            </div>

    </div>
</div>

<script src="./includes/js/report_module.js"></script>
