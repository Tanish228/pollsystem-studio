<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuthentication();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

$poll_id = filter_input(INPUT_POST, 'poll_id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$expiry_date = $_POST['expiry_date'] ?? '';
$option_ids = $_POST['option_ids'] ?? [];
$option_texts = $_POST['option_text'] ?? [];

if (!$poll_id || $title === '' || $expiry_date === '') {
    http_response_code(400);
    echo json_encode(["error" => "Title and expiry date are required."]);
    exit;
}

$stmt = $pdo->prepare("SELECT user_id FROM polls WHERE id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();

if (!$poll) {
    http_response_code(404);
    echo json_encode(["error" => "Poll not found."]);
    exit;
}

$isOwner = (int)$poll['user_id'] === (int)$user_id;
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!$isOwner && !$isAdmin) {
    http_response_code(403);
    echo json_encode(["error" => "You can only edit polls you created."]);
    exit;
}

try {
    $pdo->beginTransaction();

    $update = $pdo->prepare("UPDATE polls SET title = ?, description = ?, category = ?, expiry_date = ? WHERE id = ?");
    $update->execute([$title, $description, $category, $expiry_date, $poll_id]);

    if (is_array($option_ids)) {
        $optUpdate = $pdo->prepare("UPDATE poll_options SET option_text = ? WHERE id = ? AND poll_id = ?");
        foreach ($option_ids as $i => $optId) {
            $optId = (int)$optId;
            $text = trim($option_texts[$i] ?? '');
            if ($optId > 0 && $text !== '') {
                $optUpdate->execute([$text, $optId, $poll_id]);
            }
        }
    }

    $pdo->commit();

    header('Location: ../view-poll.php?id=' . $poll_id);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["error" => "Failed to update poll: " . $e->getMessage()]);
}
