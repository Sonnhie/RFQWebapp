<nav class="sidebar d-flex flex-column" id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header p-3 mb-3 border-bottom">
        <a href="#" class="d-flex align-items-center text-decoration-none sidebar-brand">
            <i class="bi bi-file-earmark-text sidebar-icon me-2"></i>
            <span class="fs-5 fw-bold"> Request for Material</span>
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

            <!-- User Management -->
            <li class="sidebar-section-title">User Management</li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="admin/user_management">
                    <i class="bi bi-person-fill-gear sidebar-icon"></i>
                    <span>User Accounts</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="admin/Activity_logs">
                    <i class="bi bi-list-columns-reverse sidebar-icon"></i>
                    <span>Activity Logs</span>
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