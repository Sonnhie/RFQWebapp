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
<!--Main Content-->
<div class="card shadow-sm border-0">
    <div class="card-body">
        
        <h5 class="card-title">Notifications</h5>
        <p class="card-text">You will receive notifications here.</p>
        <div id="notification-list" class="list-group" data-section="<?php echo htmlspecialchars($_SESSION['user']['department'])?>" data-role ="<?php echo htmlspecialchars($_SESSION['user']['access_level'])?>">
            <!-- Notifications will be dynamically loaded here -->
        </div>
    </div>
</div>
<!-- <div>
    <button id="send-notification" class="btn btn-primary mt-3">Send Notification</button>
    <button id="clear-notifications" class="btn btn-secondary mt-3">Clear Notifications</button>
</div> -->


<script src="./includes/js/notification.js"></script>