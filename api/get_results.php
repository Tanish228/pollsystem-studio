<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$poll_id = filter_input(INPUT_GET, 'poll_id', FILTER_VALIDATE_INT);

if (!$poll_id) {
    http_response_code(400);
    echo json_encode(["error" => "Identifier parameter target structural verification omission failure."]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT po.id, po.option_text, COUNT(v.id) as vote_count 
        FROM poll_options po
        LEFT JOIN votes v ON po.id = v.option_id
        WHERE po.poll_id = ?
        GROUP BY po.id
    ");
    $stmt->execute([$poll_id]);
    $results = $stmt->fetchAll();

    $totalVotesStmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) as total FROM votes WHERE poll_id = ?");
    $totalVotesStmt->execute([$poll_id]);
    $totalVotes = $totalVotesStmt->fetch()['total'] ?? 0;

    echo json_encode([
        "success" => true,
        "total_votes" => (int)$totalVotes,
        "metrics" => $results
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}