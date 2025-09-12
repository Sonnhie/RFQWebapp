<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$section = $_SESSION['user']['department'] ?? '';
$role = $_SESSION['user']['access_level'] ?? '';
?>
<nav class="sidebar" id="sidebar" data-section="<?= htmlspecialchars($section) ?>" data-role="<?= htmlspecialchars($role) ?>">
    <!-- Sidebar Header / Brand -->
    <div class="sidebar-header">
        <a href="#" class="d-flex align-items-center sidebar-brand text-decoration-none">
            <i class="bi bi-file-earmark-text sidebar-icon"></i>
            <span class="fs-5 fw-bold">RFM System</span>
        </a>
    </div>

    <!-- Sidebar Menu -->
    <ul class="nav flex-column h-100">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="dashboard">
                <i class="bi bi-house-fill sidebar-icon"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Request Management -->
        <li class="sidebar-section-title">Request Management</li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="for_sectionapproval">
                <i class="bi bi-building sidebar-icon"></i>
                <span>RFQ Approval</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="rfqs-history">
                <i class="bi bi-card-list sidebar-icon"></i>
                <span>RFQs History</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="request-list">
                <i class="bi bi-list-check sidebar-icon"></i>
                <span>Request List</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="timeline">
                <i class="bi bi-clock-history sidebar-icon"></i>
                <span>Timeline</span>
            </a>
        </li>

        <!-- Comparison Management -->
        <li class="sidebar-section-title">Comparison Management</li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="comparison-checklist">
                <i class="bi bi-file-check sidebar-icon"></i>
                <span>Comparison Checklist</span>
            </a>
        </li>

        <!-- Settings -->
        <li class="sidebar-section-title">Settings</li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="settings">
                <i class="bi bi-gear sidebar-icon"></i>
                <span>Configuration</span>
            </a>
        </li>

        <!-- Logout at bottom -->
        <li class="nav-item mt-auto">
            <a class="nav-link logout-link" href="#" id="logoutBtn">
                <i class="bi bi-door-open sidebar-icon"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>