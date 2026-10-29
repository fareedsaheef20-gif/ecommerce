<?php
require_once __DIR__ . '/functions.php';
$categories = getCategories();
$cart_count = isLoggedIn() ? getCartCount($_SESSION['user_id']) : 0;
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME . ' - Premium Electronics'; ?></title>
    <meta name="description" content="<?php echo isset($meta_desc) ? $meta_desc : 'Shop the latest electronics - phones, laptops, headphones and more at ElectroHub.'; ?>">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>

<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark main-nav sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>/index.php">
            <span class="brand-icon"><i class="fas fa-bolt"></i></span>
            <span class="brand-text">Ashrif<span class="brand-accent">mobile</span></span>
        </a>

        <!-- Mobile toggles -->
        <div class="d-flex align-items-center d-lg-none">
            <a href="<?php echo SITE_URL; ?>/cart.php" class="btn btn-cart-mobile me-2 position-relative">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="mainNav">
            <!-- Search Bar -->
            <form class="search-form mx-lg-3 flex-grow-1 my-2 my-lg-0" action="<?php echo SITE_URL; ?>/products.php" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control search-input"
                           placeholder="Search phones, laptops, headphones..."
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <button class="btn btn-search" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <!-- Nav Links -->
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-th-large me-1"></i> Categories
                    </a>
                    <ul class="dropdown-menu dropdown-menu-categories">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/products.php">
                            <i class="fas fa-border-all me-2"></i> All Products
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($categories as $cat): ?>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $cat['slug']; ?>">
                                <i class="<?php echo $cat['icon']; ?> me-2"></i><?php echo htmlspecialchars($cat['name']); ?>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page=='index.php'?'active':''; ?>" href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page=='products.php'?'active':''; ?>" href="<?php echo SITE_URL; ?>/products.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page=='about.php'?'active':''; ?>" href="<?php echo SITE_URL; ?>/about.php">About</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page=='contact.php'?'active':''; ?>" href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>

                <!-- Cart Icon -->
                <li class="nav-item d-none d-lg-block">
                    <a class="nav-link position-relative cart-icon-nav" href="<?php echo SITE_URL; ?>/cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- User Account -->
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'My Account'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>

                        <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                     Account
                    </a>
                    <ul class="dropdown-menu dropdown-menu-categories">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/register.php">
                             Register
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/login.php">
                            Login
                        </a></li>
                    
                </ul>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
