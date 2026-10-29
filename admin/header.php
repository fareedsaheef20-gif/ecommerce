<?php
/**
 * Admin Header / Layout
 * Include at top of every admin page
 */

require_once '../includes/functions.php';
requireAdmin();

$admin_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | Admin' : 'Admin Panel'; ?> - shafnielectro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        .admin-topbar {
            background: white;
            border-bottom: 1px solid #E5E7EB;
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .admin-main { padding: 28px; }
        .admin-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #E5E7EB;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { padding: 12px 16px; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.06em; color: #6B7280; font-weight: 600; background: #F8FAFC; border-bottom: 2px solid #E5E7EB; }
        .admin-table td { padding: 14px 16px; border-bottom: 1px solid #F3F4F6; font-size: 0.88rem; vertical-align: middle; }
        .admin-table tr:hover td { background: #FAFAFE; }
        .admin-table tr:last-child td { border-bottom: none; }
        .nav-section-title { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.35); padding: 16px 20px 6px; font-weight: 600; }
    </style>
</head>
<body style="background:#F0F4FF;">

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Logo -->
    <div style="padding:20px;border-bottom:1px solid rgba(255,255,255,0.08);">
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" style="text-decoration:none;display:flex;align-items:center;gap:8px;">
            <span style="width:30px;height:30px;background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.8rem;color:white;flex-shrink:0;">
                <i class="fas fa-bolt"></i>
            </span>
            <span style="font-family:'Syne',sans-serif;font-weight:800;color:white;font-size:1.15rem;">shafni<span style="color:#00D4FF;">electro</span></span>
        </a>
        <div style="font-size:0.7rem;color:rgba(255,255,255,0.35);margin-top:4px;padding-left:38px;">Admin Panel</div>
    </div>

    <!-- Nav -->
    <nav style="padding:12px 0;overflow-y:auto;height:calc(100vh - 80px);">
        <div class="nav-section-title">Main</div>
        <a href="dashboard.php" class="admin-nav-link <?php echo $admin_page=='dashboard.php'?'active':''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="nav-section-title">Catalog</div>
        <a href="products.php" class="admin-nav-link <?php echo in_array($admin_page,['products.php','add-product.php','edit-product.php'])?'active':''; ?>">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="add-product.php" class="admin-nav-link <?php echo $admin_page=='add-product.php'?'active':''; ?>">
            <i class="fas fa-plus-circle"></i> Add Product
        </a>
        <a href="categories.php" class="admin-nav-link <?php echo $admin_page=='categories.php'?'active':''; ?>">
            <i class="fas fa-tags"></i> Categories
        </a>

        <div class="nav-section-title">Sales</div>
        <a href="orders.php" class="admin-nav-link <?php echo $admin_page=='orders.php'?'active':''; ?>">
            <i class="fas fa-receipt"></i> Orders
        </a>
        <a href="reports.php" class="admin-nav-link <?php echo $admin_page=='reports.php'?'active':''; ?>">
            <i class="fas fa-chart-bar"></i> Sales Report
        </a>

        <div class="nav-section-title">Users</div>
        <a href="users.php" class="admin-nav-link <?php echo $admin_page=='users.php'?'active':''; ?>">
            <i class="fas fa-users"></i> Manage Users
        </a>
        <?php if (isMainAdmin()): ?>
        <a href="admins.php" class="admin-nav-link <?php echo $admin_page=='admins.php'?'active':''; ?>">
            <i class="fas fa-user-shield"></i> Manage Admins
        </a>
        <?php endif; ?>

        <div class="nav-section-title">Store</div>
        <a href="<?php echo SITE_URL; ?>" target="_blank" class="admin-nav-link">
            <i class="fas fa-external-link-alt"></i> View Store
        </a>
        <a href="logout.php" class="admin-nav-link" style="color:rgba(239,68,68,0.8);" onmouseover="this.style.background='rgba(239,68,68,0.15)'" onmouseout="this.style.background=''">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</aside>

<!-- Main Content -->
<div class="admin-content">
    <!-- Top Bar -->
    <div class="admin-topbar">
        <div style="display:flex;align-items:center;gap:16px;">
            <button id="sidebarToggle" style="background:none;border:1px solid #E5E7EB;border-radius:8px;padding:6px 10px;cursor:pointer;font-size:0.9rem;color:#6B7280;display:none;">
                <i class="fas fa-bars"></i>
            </button>
            <h1 style="font-size:1.1rem;font-weight:800;margin:0;color:#1F2937;">
                <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
            </h1>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="<?php echo SITE_URL; ?>/products.php" target="_blank" class="btn btn-outline-primary btn-sm d-none d-md-inline-flex">
                <i class="fas fa-store me-1"></i>View Store
            </a>
            <div style="display:flex;align-items:center;gap:8px;background:#F8FAFC;border:1px solid #E5E7EB;border-radius:10px;padding:8px 12px;">
                <div style="width:30px;height:30px;background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:0.8rem;">
                    <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                </div>
                <div style="display:none;" class="d-md-block">
                    <div style="font-size:0.82rem;font-weight:700;line-height:1.2;"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
                    <div style="font-size:0.72rem;color:#6B7280;"><?php echo ($_SESSION['admin_role'] ?? '') === 'main_admin' ? '⭐ Main Admin' : 'Sub Admin'; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <div style="padding:0 28px;margin-top:16px;">
        <?php showFlash(); ?>
    </div>

    <div class="admin-main">
