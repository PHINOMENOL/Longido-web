<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    if ($action === 'approve' && $id) {
        $pdo->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?")->execute([$id]);
        set_flash('success', '✅ Review approved — now visible on website');
    } elseif ($action === 'reject' && $id) {
        $pdo->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?")->execute([$id]);
        set_flash('success', 'Review rejected');
    } elseif ($action === 'pending' && $id) {
        $pdo->prepare("UPDATE reviews SET status = 'pending' WHERE id = ?")->execute([$id]);
        set_flash('success', 'Review moved back to pending');
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
        set_flash('success', 'Review deleted');
    }
    
    header('Location: reviews.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

$filter = $_GET['status'] ?? 'all';
$where = ''; $params = [];
if ($filter !== 'all') {
    $where = "WHERE status = ?";
    $params[] = $filter;
}

$reviews = $pdo->prepare("SELECT * FROM reviews $where ORDER BY created_at DESC");
$reviews->execute($params);
$reviews = $reviews->fetchAll();

render_header('Reviews Management', 'reviews');
?>

<div class="filter-tabs">
    <?php
    $filters = ['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'];
    foreach ($filters as $key => $label):
        if ($key === 'all') {
            $count = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
        } else {
            $s = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE status = ?");
            $s->execute([$key]);
            $count = $s->fetchColumn();
        }
    ?>
        <a href="?status=<?= e($key) ?>" class="filter-tab <?= $filter === $key ? 'active' : '' ?>">
            <?= e($label) ?> <span class="filter-count">(<?= $count ?>)</span>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($reviews)): ?>
    <div class="data-card"><p class="empty-state">No reviews found.</p></div>
<?php else: ?>
    <div class="reviews-grid">
        <?php foreach ($reviews as $r): ?>
            <div class="review-card review-<?= e($r['status']) ?>">
                <div class="review-header">
                    <div>
                        <p class="review-author"><?= e($r['name']) ?></p>
                        <?php if ($r['country']): ?>
                            <p class="review-country">📍 <?= e($r['country']) ?></p>
                        <?php endif; ?>
                    </div>
                    <span class="status status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span>
                </div>
                
                <div class="review-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="<?= $i <= $r['rating'] ? 'star-filled' : 'star-empty' ?>">★</span>
                    <?php endfor; ?>
                    <span class="review-rating-text">(<?= $r['rating'] ?>/5)</span>
                </div>
                
                <p class="review-text">"<?= e($r['review_text']) ?>"</p>
                
                <p class="review-date">📅 Submitted <?= date('F j, Y \a\t g:i A', strtotime($r['created_at'])) ?></p>
                
                <div class="review-actions">
                    <?php if ($r['status'] !== 'approved'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button class="btn-success">✅ Approve</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ($r['status'] !== 'rejected'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button class="btn-warning">❌ Reject</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ($r['status'] !== 'pending'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="pending">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button class="btn-neutral">↺ Pending</button>
                        </form>
                    <?php endif; ?>
                    
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this review?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button class="btn-danger-solid">🗑️ Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php render_footer(); ?>
