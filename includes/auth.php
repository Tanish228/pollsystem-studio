<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuthentication() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html');
        exit;
    }
}

function checkAdmin() {
    checkAuthentication();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }
}