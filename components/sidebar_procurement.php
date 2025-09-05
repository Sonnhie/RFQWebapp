<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$section = $_SESSION['user']['department'] ?? '';
$role = $_SESSION['user']['access_level'] ?? '';
?>

<nav class="sidebar" id="sidebar" data-section="<?= htmlspecialchars($section) ?>" data-role="<?= htmlspecialchars($role) ?>">
        <div class="sidebar-header p-3">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none">
                <i class="bi bi-file-earmark-text fs-4 me-2" style="color: var(--accent-color);"></i>
                <span class="fs-5 fw-bold">RFQ System</span>
            </a>
        </div>
        
        <div class="sidebar-menu p-2">
            <ul class="nav nav-pills flex-column">
                <div class="mt-3">
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="#" data-page ="dashboard">
                            <i class="bi bi-speedometer2 me-2" style="color: var(--icon-color);"></i>
                            Dashboard
                        </a>
                    </li>
                    <div class="mb-3 mt-5 ">
                        <h6 class="text-white text-uppercase">request management</h6>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-page="request">
                                <i class="bi bi-file-earmark-text me-2" style="color: var(--icon-color);"></i>
                                Create Request
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-page="history">
                                <i class="bi bi-list-check me-2" style="color: var(--icon-color);"></i>
                                Request List
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-page="reports">
                                <i class="bi bi-clipboard-data me-2" style="color: var(--icon-color);"></i>
                                Timeline
                            </a>
                        </li>
                    </div>

                    <div class="mb-3 mt-5 ">
                        <h6 class="text-white text-uppercase">approval management</h6>

                        <?php
                            if ($_SESSION['user']['access_level'] == 'Verifier-Approver') {
                        ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="#" data-page="for_sectionapproval">
                                    <i class="bi bi-building me-2" style="color: var(--icon-color);"></i>
                                    RFQ Approval
                                </a>
                            </li>
                        <?php
                            }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-page="for_approval">
                                <i class="bi bi-check2-all me-2" style="color: var(--icon-color);"></i>
                                Verification
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-page="verified">
                                <i class="bi bi-clipboard-check me-2" style="color: var(--icon-color);"></i>
                                Verified Checklist
                            </a>
                        </li>
                    </div>
                    <div class="mb-3 mt-5">
                        <h6 class="text-white text-uppercase">Comparison management</h6>
                        <!-- <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-page="crearte_comparison">
                                <i class="bi bi-arrow-down-up me-2" style="color: var(--icon-color);"></i>
                                Create Comparison
                            </a>
                        </li> -->
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-page="comparison-checklist">
                                <i class="bi bi-card-checklist me-2" style="color: var(--icon-color);"></i>
                                Comparison Checklist
                            </a>
                        </li>
                    </div>
                </div>
                    <div class="mb-3 mt-3 ">
                        <h6 class="text-white text-uppercase">settings</h6>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-page="settings">
                                <i class="bi bi-gear me-2" style="color: var(--icon-color);"></i>
                                Configuration    
                            </a>
                        </li>
                    </div> 
                <!-- <li class="nav-item">
                    <a class="nav-link text-white" href="#" data-page="verifier/delivery">
                        <i  class="bi bi-truck me-2" style="color: var(--icon-color);"></i>
                        Delivery Management
                    </a>
                </li> -->
<!-- 
                <li class="nav-item">
                    <a class="nav-link text-white d-flex align-items-center justify-content-between" href="#" data-page="verifier/notification">
                        <span>
                            <i class="bi bi-bell me-2" style="color: var(--icon-color);"></i>
                            Notification
                        </span>
                        <span class="badge bg-danger ms-2" id="notificationBadge" ></span>
                    </a>
                </li> -->
                <div class="mt-3">
                    <li class="nav-item mt-auto">
                        <a class="nav-link text-white" href="#" id="logoutBtn">
                            <i class="bi bi-box-arrow-right me-2" style="color: var(--icon-color);"></i>
                            Logout
                        </a>
                    </li>
                </div>
            </ul>
        </div>
    </nav>


