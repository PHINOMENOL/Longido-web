<?php
require_once 'auth.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    if ($action === 'update_status' && $id) {
        $status = $_POST['status'] ?? 'new';
        if (in_array($status, ['new','contacted','confirmed','cancelled'])) {
            $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$status, $id]);
            set_flash('success', 'Booking status updated successfully');
        }
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$id]);
        set_flash('success', 'Booking deleted');
    }
    
    header('Location: bookings.php');
    exit;
}

// Filter
$filter = $_GET['status'] ?? 'all';
$where = '';
$params = [];
if ($filter !== 'all') {
    $where = "WHERE status = ?";
    $params[] = $filter;
}

$bookings = $pdo->prepare("SELECT * FROM bookings $where ORDER BY created_at DESC");
$bookings->execute($params);
$bookings = $bookings->fetchAll();

render_header('Bookings Management', 'bookings');
?>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <?php
    $filters = [
        'all' => 'All',
        'new' => 'New',
        'contacted' => 'Contacted',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled'
    ];
    foreach ($filters as $key => $label):
        $count = $key === 'all' 
            ? $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn()
            : $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = ?");
        if ($key !== 'all') { $count->execute([$key]); $count = $count->fetchColumn(); }
    ?>
        <a href="?status=<?= e($key) ?>" class="filter-tab <?= $filter === $key ? 'active' : '' ?>">
            <?= e($label) ?> <span class="filter-count">(<?= $count ?>)</span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Bookings Table -->
<div class="data-card">
    <?php if (empty($bookings)): ?>
        <p class="empty-state">No bookings found in this category.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Contact</th>
                        <th>Dates</th>
                        <th>Room</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <p class="cell-main"><?= e($b['name']) ?></p>
                                <p class="cell-sub"><?= e($b['guests']) ?> guests</p>
                            </td>
                            <td>
                                <p class="cell-main"><a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></p>
                                <?php if ($b['phone']): ?>
                                    <p class="cell-sub"><?= e($b['phone']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <p class="cell-main"><?= date('M j', strtotime($b['checkin'])) ?> → <?= date('M j, Y', strtotime($b['checkout'])) ?></p>
                                <p class="cell-sub">
                                    <?= (strtotime($b['checkout']) - strtotime($b['checkin'])) / 86400 ?> nights
                                </p>
                            </td>
                            <td><span class="amenity-pill"><?= e($b['room']) ?></span></td>
                            <td><p class="cell-sub"><?= date('M j, g:i A', strtotime($b['created_at'])) ?></p></td>
                            <td><span class="status status-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon" onclick="document.getElementById('msg-<?= $b['id'] ?>').style.display='block'" title="View message">👁️</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-select">
                                            <option value="new" <?= $b['status']==='new'?'selected':'' ?>>New</option>
                                            <option value="contacted" <?= $b['status']==='contacted'?'selected':'' ?>>Contacted</option>
                                            <option value="confirmed" <?= $b['status']==='confirmed'?'selected':'' ?>>Confirmed</option>
                                            <option value="cancelled" <?= $b['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                                        </select>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this booking?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                        <button class="btn-icon btn-danger" title="Delete">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php if (!empty($b['message'])): ?>
                            <tr id="msg-<?= $b['id'] ?>" style="display:none;" class="message-row">
                                <td colspan="7">
                                    <div class="message-box">
                                        <strong>Special Request / Message:</strong>
                                        <p><?= nl2br(e($b['message'])) ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php render_footer(); ?>
