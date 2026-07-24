<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAdmin();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPolls = $pdo->query("SELECT COUNT(*) FROM polls")->fetchColumn();
$totalVotes = $pdo->query("SELECT COUNT(DISTINCT user_id, poll_id) FROM votes")->fetchColumn();

$allUsersList = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$allPollsList = $pdo->query("SELECT p.*, u.name as creator FROM polls p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administration Console Engine Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <h2>Admin Hub Interface Management Dashboard</h2>
        <div>
            <a href="../dashboard.php" style="margin-right:15px;">User View Board</a>
            <a href="../api/logout.php" class="btn" style="background:#ef4444;">Terminate Session Link</a>
        </div>
    </nav>

    <div class="container">
        <div class="grid" style="margin-bottom: 2rem;">
            <div class="card" style="background:#eff6ff; text-align:center;">
                <h3>Total Accounts</h3>
                <h2><?= $totalUsers ?></h2>
            </div>
            <div class="card" style="background:#fffbeb; text-align:center;">
                <h3>Total Active Campaigns</h3>
                <h2><?= $totalPolls ?></h2>
            </div>
            <div class="card" style="background:#f0fdf4; text-align:center;">
                <h3>Total Ballot Entries Cast</h3>
                <h2><?= $totalVotes ?></h2>
            </div>
        </div>

        <h3>Global Profile Matrix Register</h3>
        <table border="1" cellpadding="8" style="width:100%; border-collapse:collapse; background:white; margin-bottom:2rem; border-color:var(--border-color);">
            <tr style="background:#f8fafc;">
                <th>ID Token</th><th>User Label Identification Name</th><th>Registered Address Email</th><th>Access Rights Scope Rank</th>
            </tr>
            <?php foreach ($allUsersList as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><strong><?= strtoupper($u['role']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Global Campaign Registry Track List</h3>
        <table border="1" cellpadding="8" style="width:100%; border-collapse:collapse; background:white; border-color:var(--border-color);">
            <tr style="background:#f8fafc;">
                <th>ID</th><th>Campaign Context Query Text Name</th><th>Classification Theme Category</th><th>Owner Account</th><th>Expiry Matrix Ceiling Limit</th>
            </tr>
            <?php foreach ($allPollsList as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= htmlspecialchars($p['category']) ?></td>
                    <td><?= htmlspecialchars($p['creator']) ?></td>
                    <td><?= $p['expiry_date'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>