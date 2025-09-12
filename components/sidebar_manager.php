<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$section = $_SESSION['user']['department'] ?? '';
$role = $_SESSION['user']['access_level'] ?? '';
?>

<nav class="sidebar d-flex flex-column" id="sidebar" data-section="<?= htmlspecialchars($section) ?>" data-role="<?= htmlspecialchars($role) ?>">
    <!-- Sidebar Header -->
    <div class="sidebar-header p-3 mb-3 border-bottom">
        <a href="#" class="d-flex align-items-center text-decoration-none sidebar-brand">
            <i class="bi bi-file-earmark-text sidebar-icon me-2"></i>
            <span class="fs-5 fw-bold">RFQ System</span>
        </a>
    </div>

    <!-- Sidebar Menu -->
    <ul class="nav nav-pills flex-column flex-grow-1 justify-content-between">
        <div>
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="dashboard">
                    <i class="bi bi-speedometer2 sidebar-icon"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Request Management -->
            <li class="sidebar-section-title">Request Management</li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="history">
                    <i class="bi bi-list-check sidebar-icon"></i>
                    <span>Request List</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="reports">
                    <i class="bi bi-clipboard-data sidebar-icon"></i>
                    <span>Timeline</span>
                </a>
            </li>

            <!-- Approval Management -->
            <li class="sidebar-section-title">Approval Management</li>
            <?php if ($_SESSION['user']['access_level'] == 'Verifier-Approver' || $_SESSION['user']['access_level'] == 'Manager'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-page="for_sectionapproval">
                        <i class="bi bi-building sidebar-icon"></i>
                        <span>RFM Approval</span>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="for_approval">
                    <i class="bi bi-check2-all sidebar-icon"></i>
                    <span>Verification</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="verified">
                    <i class="bi bi-clipboard-check sidebar-icon"></i>
                    <span>Verified Checklist</span>
                </a>
            </li>

            <!-- Comparison Management -->
            <li class="sidebar-section-title">Comparison Management</li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="comparison-checklist">
                    <i class="bi bi-card-checklist sidebar-icon"></i>
                    <span>Comparison Checklist</span>
                </a>
            </li>

        </div>

        <!-- Logout pinned at bottom -->
        <li class="nav-item mt-auto">
            <a class="nav-link logout-link" href="#" id="logoutBtn">
                <i class="bi bi-box-arrow-right sidebar-icon"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>