<?php
require_once 'auth.php';

// Stats
$stats = [
    'total_bookings'    => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'new_bookings'      => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'new'")->fetchColumn(),
    'total_reviews'     => $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
    'pending_reviews'   => $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn(),
    'total_questions'   => $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn(),
    'new_questions'     => $pdo->query("SELECT COUNT(*) FROM questions WHERE status = 'new'")->fetchColumn(),
    'avg_rating'        => round($pdo->query("SELECT AVG(rating) FROM reviews WHERE status='approved'")->fetchColumn() ?? 0, 1),
];

// Recent items
$recent_bookings  = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_reviews   = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_questions = $pdo->query("SELECT * FROM questions ORDER BY created_at DESC LIMIT 5")->fetchAll();

render_header('Dashboard', 'dashboard');
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card stat-amber">
        <div class="stat-icon">📅</div>
        <div class="stat-info">
            <p class="stat-label">Total Bookings</p>
            <p class="stat-number"><?= $stats['total_bookings'] ?></p>
            <p class="stat-sub"><?= $stats['new_bookings'] ?> new pending</p>
        </div>
    </div>
    
    <div class="stat-card stat-green">
        <div class="stat-icon">⭐</div>
        <div class="stat-info">
            <p class="stat-label">Average Rating</p>
            <p class="stat-number"><?= $stats['avg_rating'] ?>/5</p>
            <p class="stat-sub"><?= $stats['total_reviews'] ?> total reviews</p>
        </div>
    </div>
    
    <div class="stat-card stat-blue">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <p class="stat-label">Pending Reviews</p>
            <p class="stat-number"><?= $stats['pending_reviews'] ?></p>
            <p class="stat-sub">Awaiting approval</p>
        </div>
    </div>
    
    <div class="stat-card stat-purple">
        <div class="stat-icon">💬</div>
        <div class="stat-info">
            <p class="stat-label">Questions</p>
            <p class="stat-number"><?= $stats['total_questions'] ?></p>
            <p class="stat-sub"><?= $stats['new_questions'] ?> unanswered</p>
        </div>
    </div>
</div>

<!-- Three Column Layout -->
<div class="dashboard-columns">
    <!-- Recent Bookings -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2>📅 Recent Bookings</h2>
            <a href="bookings.php" class="view-all">View all →</a>
        </div>
        <div class="card-body">
            <?php if (empty($recent_bookings)): ?>
                <p class="empty-state">No bookings yet</p>
            <?php else: ?>
                <?php foreach ($recent_bookings as $b): ?>
                    <div class="recent-item">
                        <div class="recent-main">
                            <p class="recent-title"><?= e($b['name']) ?></p>
                            <p class="recent-meta">
                                <?= e($b['room']) ?> · <?= e($b['guests']) ?> guests
                            </p>
                            <p class="recent-date">
                                <?= date('M j, Y', strtotime($b['checkin'])) ?> → 
                                <?= date('M j, Y', strtotime($b['checkout'])) ?>
                            </p>
                        </div>
                        <span class="status status-<?= e($b['status']) ?>"><?= e($b['status']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Reviews -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2>⭐ Recent Reviews</h2>
            <a href="reviews.php" class="view-all">View all →</a>
        </div>
        <div class="card-body">
            <?php if (empty($recent_reviews)): ?>
                <p class="empty-state">No reviews yet</p>
            <?php else: ?>
                <?php foreach ($recent_reviews as $r): ?>
                    <div class="recent-item">
                        <div class="recent-main">
                            <p class="recent-title">
                                <?= e($r['name']) ?>
                                <span class="inline-stars">
                                    <?php for ($i=0; $i<$r['rating']; $i++) echo '★'; ?>
                                </span>
                            </p>
                            <p class="recent-meta"><?= e(substr($r['review_text'], 0, 70)) ?>...</p>
                            <p class="recent-date"><?= date('M j, Y', strtotime($r['created_at'])) ?></p>
                        </div>
                        <span class="status status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Questions -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2>💬 Recent Questions</h2>
            <a href="questions.php" class="view-all">View all →</a>
        </div>
        <div class="card-body">
            <?php if (empty($recent_questions)): ?>
                <p class="empty-state">No questions yet</p>
            <?php else: ?>
                <?php foreach ($recent_questions as $q): ?>
                    <div class="recent-item">
                        <div class="recent-main">
                            <p class="recent-title"><?= e($q['name']) ?></p>
                            <p class="recent-meta"><?= e($q['subject'] ?: substr($q['message'], 0, 60)) ?></p>
                            <p class="recent-date"><?= date('M j, Y g:i A', strtotime($q['created_at'])) ?></p>
                        </div>
                        <span class="status status-<?= e($q['status']) ?>"><?= e($q['status']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php render_footer(); ?>
