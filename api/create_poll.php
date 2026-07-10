<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
checkAuthentication();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $question_type = $_POST['question_type'] ?? 'single';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $options = $_POST['options'] ?? [];

if (!empty($title) && !empty($expiry_date) && count($options) >= 2) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO polls (user_id, title, description, category, question_type, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $category, $question_type, $expiry_date]);
            $poll_id = $pdo->lastInsertId();

            $optStmt = $pdo->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
            foreach ($options as $optionText) {
                $optionText = trim($optionText);
                if ($optionText !== '') {
                    $optStmt->execute([$poll_id, $optionText]);
                }
            }

            $pdo->commit();

            header('Location: ../dashboard.php');
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            echo json_encode(["error" => "Failed to create poll: " . $e->getMessage()]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Please provide a title, expiry date, and at least 2 options."]);
        exit;
    }
}