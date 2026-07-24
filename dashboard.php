<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
checkAuthentication();

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

// Fixed ambiguous SQL column definitions by explicitly scoping query parameters
$query = "SELECT p.*, u.name as creator FROM polls p JOIN users u ON p.user_id = u.id WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND p.title LIKE ?";
    $params[] = "%$search%";
}
if ($category !== '') {
    $query .= " AND p.category = ?";
    $params[] = $category;
}
$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$polls = $stmt->fetchAll();

$currentUserId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Hub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <h2>PollSystem Studio</h2>
        <div>
            <span>Welcome, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong></span>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin/dashboard.php" style="margin-left: 15px; color: #ff7d6b;">Admin Panel</a>
            <?php endif; ?>
            <a href="create-poll.html" style="margin-left: 15px;" class="btn">New Poll</a>
            <a href="api/logout.php" style="margin-left: 15px; color: var(--text-main);">Logout</a>
        </div>
    </nav>

    <div class="container">
        <form method="GET" style="display: flex; gap: 10px; margin-bottom: 2rem; background: white; padding: 15px; border-radius: 12px; border: 1px solid var(--border-color);">
            <input type="text" name="search" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>" style="flex: 2; padding: 8px;">
            <input type="text" name="category" placeholder="Category..." value="<?= htmlspecialchars($category) ?>" style="flex: 1; padding: 8px;">
            <button type="submit" class="btn">Filter</button>
            <a href="dashboard.php" style="padding: 8px; text-decoration: none;" class="text-muted">Reset</a>
        </form>

        <h3>Active System Polls</h3>
        <div class="grid">
            <?php if (count($polls) === 0): ?>
                <p>No active polls discovered.</p>
            <?php endif; ?>
            <?php foreach ($polls as $poll): ?>
                <div class="card">
                    <span class="badge"><?= htmlspecialchars($poll['category']) ?></span>
                    <h4 style="margin: 10px 0 5px 0;"><?= htmlspecialchars($poll['title']) ?></h4>
                    <p class="text-muted" style="font-size: 0.9rem;"><?= htmlspecialchars($poll['description']) ?></p>
                    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 15px 0;">
                    <div style="display:flex; justify-content: space-between; align-items: center;">
                        <small class="text-muted">By: <?= htmlspecialchars($poll['creator']) ?></small>
                        <div class="owner-actions">
                            <?php if ((int)$poll['user_id'] === (int)$currentUserId || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
                                <a href="edit-poll.php?id=<?= $poll['id'] ?>" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Edit</a>
                                <button type="button" onclick="deletePoll(<?= $poll['id'] ?>, 'dashboard.php')" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Delete</button>
                            <?php endif; ?>
                            <a href="view-poll.php?id=<?= $poll['id'] ?>" class="btn" style="font-size: 0.85rem;">Interact</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
