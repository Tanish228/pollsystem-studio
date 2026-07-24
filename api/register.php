<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($name && $email && strlen($password) >= 6) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);
            header('Location: ../login.html');
            exit;
        } catch (PDOException $e) {
            die("Email matches an existing registered user.");
        }
    }
    die("Invalid entry requirements verification failure.");
}