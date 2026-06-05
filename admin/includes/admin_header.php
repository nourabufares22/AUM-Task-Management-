<?php
$admin_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — AUM Admin</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <style>
        @media print {
            .admin-sidebar, .admin-topbar, .btn, form { display: none !important; }
            .admin-main { margin-left: 0 !important; }
            .admin-content { padding: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ── Sidebar ── -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-shield-check"></i></div>
        <h6>AUM Maintenance</h6>
        <small>Admin Control Panel</small>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Navigation</div>

        <a href="dashboard.php"
           class="nav-link <?= $admin_page === 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="manage_requests.php"
           class="nav-link <?= $admin_page === 'manage_requests.php' || $admin_page === 'request_details.php' ? 'active' : '' ?>">
            <i class="bi bi-clipboard-list"></i> Manage Requests
        </a>

        <a href="reports.php"
           class="nav-link <?= $admin_page === 'reports.php' ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line"></i> Reports
        </a>

        <div class="nav-label mt-2">Account</div>

        <a href="logout.php" class="nav-link">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>

    <div class="sidebar-footer">
        <i class="bi bi-person-badge me-1"></i>
        Logged in as <strong>Admin</strong>
    </div>
</aside>

<!-- ── Main ── -->
<div class="admin-main">

    <div class="admin-topbar">
        <button class="btn btn-sm btn-outline-secondary d-md-none border-0"
                onclick="openSidebar()">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="topbar-title"><?= htmlspecialchars($page_title) ?></span>
        <div class="topbar-meta">
            <i class="bi bi-calendar3"></i>
            <?= date('D, d M Y') ?>
        </div>
    </div>

    <div class="admin-content">
