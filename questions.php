<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    if ($action === 'mark_read' && $id) {
        $pdo->prepare("UPDATE questions SET status = 'read' WHERE id = ?")->execute([$id]);
        set_flash('success', 'Marked as read');
    } elseif ($action === 'mark_answered' && $id) {
        $pdo->prepare("UPDATE questions SET status = 'answered' WHERE id = ?")->execute([$id]);
        set_flash('success', 'Marked as answered');
    } elseif ($action === 'mark_new' && $id) {
        $pdo->prepare("UPDATE questions SET status = 'new' WHERE id = ?")->execute([$id]);
        set_flash('success', 'Marked as new');
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM questions WHERE id = ?")->execute([$id]);
        set_flash('success', 'Message deleted');
    }
    
    header('Location: questions.php');
    exit;
}

$filter = $_GET['status'] ?? 'all';
$where = ''; $params = [];
if ($filter !== 'all') {
    $where = "WHERE status = ?";
    $params[] = $filter;
}

$questions = $pdo->prepare("SELECT * FROM questions $where ORDER BY created_at DESC");
$questions->execute($params);
$questions = $questions->fetchAll();

render_header('Questions & Messages', 'questions');
?>

<div class="filter-tabs">
    <?php
    $filters = ['all'=>'All','new'=>'New','read'=>'Read','answered'=>'Answered'];
    foreach ($filters as $key => $label):
        if ($key === 'all') {
            $count = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
        } else {
            $s = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE status = ?");
            $s->execute([$key]);
            $count = $s->fetchColumn();
        }
    ?>
        <a href="?status=<?= e($key) ?>" class="filter-tab <?= $filter === $key ? 'active' : '' ?>">
            <?= e($label) ?> <span class="filter-count">(<?= $count ?>)</span>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($questions)): ?>
    <div class="data-card"><p class="empty-state">No questions or messages yet.</p></div>
<?php else: ?>
    <div class="questions-list">
        <?php foreach ($questions as $q): ?>
            <div class="question-card status-<?= e($q['status']) ?>">
                <div class="question-header">
                    <div class="question-author">
                        <div class="q-avatar"><?= strtoupper(substr($q['name'], 0, 1)) ?></div>
                        <div>
                            <p class="q-name"><?= e($q['name']) ?></p>
                            <p class="q-email">
                                <a href="mailto:<?= e($q['email']) ?>"><?= e($q['email']) ?></a>
                                · <?= date('M j, Y · g:i A', strtotime($q['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                    <span class="status status-<?= e($q['status']) ?>"><?= e($q['status']) ?></span>
                </div>
                
                <?php if ($q['subject']): ?>
                    <p class="q-subject"><strong>Subject:</strong> <?= e($q['subject']) ?></p>
                <?php endif; ?>
                
                <div class="q-message">
                    <?= nl2br(e($q['message'])) ?>
                </div>
                
                <div class="question-actions">
                    <a href="mailto:<?= e($q['email']) ?>?subject=Re: <?= e($q['subject'] ?: 'Your inquiry') ?>" class="btn-reply">
                        ✉️ Reply via Email
                    </a>
                    
                    <?php if ($q['status'] !== 'read'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <button class="btn-neutral">👁 Mark as Read</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ($q['status'] !== 'answered'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="mark_answered">
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <button class="btn-success">✅ Mark as Answered</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ($q['status'] !== 'new'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="mark_new">
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <button class="btn-warning">↺ Mark as New</button>
                        </form>
                    <?php endif; ?>
                    
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $q['id'] ?>">
                        <button class="btn-danger-solid">🗑️ Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php render_footer(); ?>
