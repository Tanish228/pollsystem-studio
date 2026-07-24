<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
checkAuthentication();

$poll_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if (!$poll_id) {
    die("Error parsing selection parameter payload.");
}

$pollStmt = $pdo->prepare("SELECT p.*, u.name as creator FROM polls p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$pollStmt->execute([$poll_id]);
$poll = $pollStmt->fetch();

if (!$poll) die("Poll data matrix could not be resolved.");

$optStmt = $pdo->prepare("SELECT * FROM poll_options WHERE poll_id = ?");
$optStmt->execute([$poll_id]);
$options = $optStmt->fetchAll();

$voteCheck = $pdo->prepare("SELECT option_id FROM votes WHERE poll_id = ? AND user_id = ?");
$voteCheck->execute([$poll_id, $user_id]);
$userVotes = $voteCheck->fetchAll(PDO::FETCH_COLUMN);
$hasVoted = count($userVotes) > 0;

$isExpired = new DateTime() > new DateTime($poll['expiry_date']);
$isOwner = (int)$poll['user_id'] === (int)$user_id;
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Poll Interface - <?= htmlspecialchars($poll['title']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    <div class="container" style="max-width:650px;">
        <div class="card">
            <div style="display:flex; justify-content: space-between; align-items: flex-start;">
                <span class="badge"><?= htmlspecialchars($poll['category']) ?></span>
                <?php if ($isOwner || $isAdmin): ?>
                    <div class="owner-actions">
                        <a href="edit-poll.php?id=<?= $poll['id'] ?>" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Edit</a>
                        <button type="button" onclick="deletePoll(<?= $poll['id'] ?>, 'dashboard.php')" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Delete</button>
                    </div>
                <?php endif; ?>
            </div>
            <h2 style="margin-top: 15px;"><?= htmlspecialchars($poll['title']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($poll['description']) ?></p>
            <p class="text-muted" style="font-size: 0.85rem;">Created by: <?= htmlspecialchars($poll['creator']) ?> | Closes: <?= htmlspecialchars($poll['expiry_date']) ?></p>
            <hr style="border:0; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <?php if (!$hasVoted && !$isExpired): ?>
                <form id="voting-form">
                    <input type="hidden" name="poll_id" value="<?= $poll['id'] ?>">
                    <?php foreach ($options as $opt): ?>
                        <div style="background: var(--bg-primary); padding: 12px; border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 10px;">
                            <label style="display: flex; align-items: center; cursor: pointer; width: 100%;">
                                <input type="<?= $poll['question_type'] === 'multiple' ? 'checkbox' : 'radio' ?>" 
                                       name="options[]" value="<?= $opt['id'] ?>" style="margin-right: 12px; transform: scale(1.2);">
                                <?= htmlspecialchars($opt['option_text']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn" style="margin-top:15px; width:100%; padding: 12px;">Cast Secure Ballot Entry</button>
                </form>
            <?php else: ?>
                <div id="results-container">
                    <h3 style="margin-bottom: 1.5rem;">Current Standing Results <?php if($isExpired) echo "(Closed/Expired)"; ?></h3>
                    <?php foreach ($options as $opt): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <div style="display:flex; justify-content: space-between; font-weight: 500;">
                                <span><?= htmlspecialchars($opt['option_text']) ?> <?= in_array($opt['id'], $userVotes) ? '<strong style="color:var(--accent);">(Your Selection)</strong>' : '' ?></span>
                                <span id="count-label-<?= $opt['id'] ?>" class="text-muted">0 votes (0%)</span>
                            </div>
                            <div class="progress-bar-container">
                                <div id="progress-fill-<?= $opt['id'] ?>" class="progress-bar-fill"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <p class="text-muted" style="text-align: center; font-size: 0.85rem; margin-top: 20px;">Live-updating display sync module automated loop running.</p>
                </div>
            <?php endif; ?>
            <p style="margin-top: 25px; text-align: center;"><a href="dashboard.php" style="color: var(--accent); text-decoration: none;">← Return back toward Main Dashboard Hub</a></p>
        </div>
    </div>
</body>
</html>
