
<nav class="sidebar" id="sidebar">
        <div class="sidebar-header p-3">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none">
                <i class="bi bi-file-earmark-text fs-4 me-2" style="color: var(--accent-color);"></i>
                <span class="fs-5 fw-bold">RFQ System</span>
            </a>
        </div>
        
        <div class="sidebar-menu p-2">
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white active" href="#" data-page ="dashboard">
                        <i class="bi bi-speedometer2 me-2" style="color: var(--icon-color);"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" data-page="request">
                        <i class="bi bi-file-earmark-text me-2" style="color: var(--icon-color);"></i>
                        RFQs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" data-page="for_approval">
                        <i class="bi bi-building me-2" style="color: var(--icon-color);"></i>
                        RFQ Management
                    </a>
                </li>
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
                    <a class="nav-link text-white" href="#" data-page="history">
                        <i class="bi bi-list-check me-2" style="color: var(--icon-color);"></i>
                        RFQs History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" data-page="verifier/delivery">
                        <i  class="bi bi-truck me-2" style="color: var(--icon-color);"></i>
                        Delivery Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" data-page="reports">
                        <i class="bi bi-clipboard-data me-2" style="color: var(--icon-color);"></i>
                        Timeline
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" data-page="verifier/notification">
                        <i class="bi bi-bell me-2" style="color: var(--icon-color);"></i>
                        Notification
                    </a>
                </li>
                <li class="nav-item mt-auto">
                    <a class="nav-link text-white" href="#" id="logoutBtn">
                        <i class="bi bi-box-arrow-right me-2" style="color: var(--icon-color);"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>


