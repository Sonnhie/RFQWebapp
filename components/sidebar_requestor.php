<nav class="sidebar" id="sidebar">
    <div class="sidebar-header p-3">
        <a href="#" class="d-flex align-items-center text-decoration-none sidebar-brand">
            <!-- <i class="bi bi-file-earmark-text sidebar-icon"></i> -->
            <!-- <span class="fs-5 fw-bold"> Request for Material</span> -->
            <span class="fs-5 fw-bold"> Request for Material</span>
        </a>
    </div>

    <ul class="nav nav-pills flex-column h-100">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="dashboard">
                <i class="bi bi-house-fill sidebar-icon"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Request Management -->
        <li class="nav-item sidebar-section-title">Request Management</li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="request">
                <i class="bi bi-pencil-square sidebar-icon"></i>
                <span>Create Request</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="history">
                <i class="bi bi-card-list sidebar-icon"></i>
                <span>Request List</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="reports">
                <i class="bi bi-clock-history sidebar-icon"></i>
                <span>Timeline</span>
            </a>
        </li>

        <!-- Comparison Management -->
        <li class="nav-item sidebar-section-title">Comparison Management</li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-page="comparison-checklist">
                <i class="bi bi-file-check sidebar-icon"></i>
                <span>Comparison Checklist</span>
            </a>
        </li>

        <!-- Logout -->
        <li class="nav-item mt-auto">
            <a class="nav-link logout-link" href="#" id="logoutBtn">
                <i class="bi bi-door-open sidebar-icon"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>