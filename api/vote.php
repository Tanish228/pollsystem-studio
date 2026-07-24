<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Identity verification verification target validation failure."]);
    exit;
}

$poll_id = filter_input(INPUT_POST, 'poll_id', FILTER_VALIDATE_INT);
$options = $_POST['options'] ?? [];

if (!$poll_id || empty($options)) {
    http_response_code(400);
    echo json_encode(["error" => "No option parameters discovered inside framework payload execution block."]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT expiry_date FROM polls WHERE id = ?");
    $stmt->execute([$poll_id]);
    $poll = $stmt->fetch();

    if (new DateTime() > new DateTime($poll['expiry_date'])) {
        http_response_code(400);
        echo json_encode(["error" => "This execution route target tracking closed context domain window parameters expired."]);
        exit;
    }

    $checkVote = $pdo->prepare("SELECT id FROM votes WHERE poll_id = ? AND user_id = ?");
    $checkVote->execute([$poll_id, $_SESSION['user_id']]);
    if ($checkVote->fetch()) {
        http_response_code(409);
        echo json_encode(["error" => "Ballot validation failure: Double listing constraint entry found."]);
        exit;
    }

    $pdo->beginTransaction();
    $insertVote = $pdo->prepare("INSERT INTO votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
    foreach ((array)$options as $opt_id) {
        $insertVote->execute([$poll_id, (int)$opt_id, $_SESSION['user_id']]);
    }
    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Your secure system vote was successfully aggregated!"]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["error" => "Fatal stack exception wrapper trace tracking: " . $e->getMessage()]);
}