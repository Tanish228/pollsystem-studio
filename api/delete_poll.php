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

if (!$poll_id) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid poll id."]);
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
    echo json_encode(["error" => "You can only delete polls you created."]);
    exit;
}

try {
    // poll_options and votes are removed automatically via ON DELETE CASCADE
    $del = $pdo->prepare("DELETE FROM polls WHERE id = ?");
    $del->execute([$poll_id]);

    echo json_encode(["success" => true, "message" => "Poll deleted."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to delete poll: " . $e->getMessage()]);
}
