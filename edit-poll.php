<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
checkAuthentication();

$poll_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if (!$poll_id) {
    die("Invalid poll id.");
}

$pollStmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
$pollStmt->execute([$poll_id]);
$poll = $pollStmt->fetch();

if (!$poll) die("Poll not found.");

$isOwner = (int)$poll['user_id'] === (int)$user_id;
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!$isOwner && !$isAdmin) {
    header('Location: dashboard.php');
    exit;
}

$optStmt = $pdo->prepare("SELECT * FROM poll_options WHERE poll_id = ?");
$optStmt->execute([$poll_id]);
$options = $optStmt->fetchAll();

$expiryValue = (new DateTime($poll['expiry_date']))->format('Y-m-d\TH:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Poll - <?= htmlspecialchars($poll['title']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 650px;">
        <div class="card">
            <h2>Edit poll</h2>
            <form action="api/update_poll.php" method="POST">
                <input type="hidden" name="poll_id" value="<?= $poll['id'] ?>">

                <div style="margin-bottom:1rem;">
                    <label>Poll title</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($poll['title']) ?>" style="width:100%; padding:8px; margin-top:5px;">
                </div>

                <div style="margin-bottom:1rem;">
                    <label>Description</label>
                    <textarea name="description" rows="3" style="width:100%; padding:8px; margin-top:5px;"><?= htmlspecialchars($poll['description']) ?></textarea>
                </div>

                <div style="margin-bottom:1rem;">
                    <label>Category</label>
                    <input type="text" name="category" required value="<?= htmlspecialchars($poll['category']) ?>" style="width:100%; padding:8px; margin-top:5px;">
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label>Expiry date</label>
                    <input type="datetime-local" name="expiry_date" required value="<?= $expiryValue ?>" style="width:100%; padding:8px; margin-top:5px;">
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label>Options</label>
                    <?php foreach ($options as $opt): ?>
                        <div style="margin-top: 8px;">
                            <input type="hidden" name="option_ids[]" value="<?= $opt['id'] ?>">
                            <input type="text" name="option_text[]" required value="<?= htmlspecialchars($opt['option_text']) ?>" style="width:100%; padding:8px;">
                        </div>
                    <?php endforeach; ?>
                    <p class="text-muted" style="font-size: 0.8rem; margin-top: 8px;">Adding or removing options isn't supported here yet — only editing existing option text.</p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <a href="view-poll.php?id=<?= $poll['id'] ?>" style="text-decoration:none;" class="text-muted">Cancel</a>
                    <button type="submit" class="btn">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
