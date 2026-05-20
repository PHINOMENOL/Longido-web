<?php
// Shared authentication check & layout helpers
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get counts for sidebar badges
$counts = [
    'bookings_new'   => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'new'")->fetchColumn(),
    'reviews_pending'=> $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn(),
    'questions_new'  => $pdo->query("SELECT COUNT(*) FROM questions WHERE status = 'new'")->fetchColumn(),
];

function render_header($page_title, $active = '') {
    global $counts;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?= e($page_title) ?> | Longido Admin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="admin-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-brand">
                    <div class="brand-icon">⛰</div>
                    <div>
                        <h2>LONGIDO</h2>
                        <p>Admin Panel</p>
                    </div>
                </div>
                
                <nav class="sidebar-nav">
                    <a href="dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">
                        <span class="nav-icon">📊</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="bookings.php" class="<?= $active === 'bookings' ? 'active' : '' ?>">
                        <span class="nav-icon">📅</span>
                        <span>Bookings</span>
                        <?php if ($counts['bookings_new']): ?>
                            <span class="badge"><?= $counts['bookings_new'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="reviews.php" class="<?= $active === 'reviews' ? 'active' : '' ?>">
                        <span class="nav-icon">⭐</span>
                        <span>Reviews</span>
                        <?php if ($counts['reviews_pending']): ?>
                            <span class="badge"><?= $counts['reviews_pending'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="questions.php" class="<?= $active === 'questions' ? 'active' : '' ?>">
                        <span class="nav-icon">💬</span>
                        <span>Questions</span>
                        <?php if ($counts['questions_new']): ?>
                            <span class="badge"><?= $counts['questions_new'] ?></span>
                        <?php endif; ?>
                    </a>
                </nav>
                
                <div class="sidebar-footer">
                    <a href="../index.php" target="_blank" class="sidebar-link">🌐 View Website</a>
                    <div class="user-info">
                        <div class="user-avatar"><?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?></div>
                        <div>
                            <p class="user-name"><?= e($_SESSION['admin_name'] ?? 'Admin') ?></p>
                            <a href="logout.php" class="logout-link">Sign out →</a>
                        </div>
                    </div>
                </div>
            </aside>
            
            <!-- Main Content -->
            <main class="main-content">
                <header class="content-header">
                    <h1><?= e($page_title) ?></h1>
                    <div class="header-date"><?= date('l, F j, Y') ?></div>
                </header>
                
                <?php $flash = get_flash(); if ($flash): ?>
                    <div class="admin-flash flash-<?= e($flash['type']) ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>
    <?php
}

function render_footer() { ?>
            </main>
        </div>
    </body>
    </html>
    <?php
}
