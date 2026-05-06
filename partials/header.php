<?php
// partials/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelfSpace - Modern Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/library/assets/style.css">
    <link rel="stylesheet" href="/library/assets/animations.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/library/index.php" class="nav-brand">
                <span class="brand-accent">Shelf</span>Space
            </a>
            
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/library/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Browse</a>
                    <a href="/library/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">My Dashboard</a>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="/library/admin.php" class="<?= $current_page == 'admin.php' ? 'active' : '' ?>">Admin Panel</a>
                    <?php endif; ?>
                    
                    <a href="/library/logout.php" class="btn btn-outline">Logout</a>
                <?php else: ?>
                    <a href="/library/login.php" class="<?= $current_page == 'login.php' ? 'active' : '' ?>">Login</a>
                    <a href="/library/register.php" class="btn btn-primary <?= $current_page == 'register.php' ? 'active' : '' ?>">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <main class="main-content">
