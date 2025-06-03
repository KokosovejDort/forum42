<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $_SESSION['register_input_data'] = [
        'username' => $username,
        'email' => $email
    ];

    if (empty($username) || strlen($username) < 2 || strlen($username) > 30) {
        $_SESSION['register_error'] = "Username must be between 2 and 30 characters";
        header("Location: ../../register.php");
        exit;
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Invalid email format";
        header("Location: ../../register.php");
        exit;
    }

    if (empty($password) || strlen($password) < 6) {
        $_SESSION['register_error'] = "Password must be at least 6 characters";
        header("Location: ../../register.php");
        exit;
    }

    if (strlen($password) > 255) {
        $_SESSION['register_error'] = "Password must be between 6 and 255 characters";
        header("Location: ../../register.php");
        exit;
    }

    if ($password !== $password_confirm) {
        $_SESSION['register_error'] = "Passwords do not match";
        header("Location: ../../register.php");
        exit;
    }

    $query = $db->prepare("SELECT user_id FROM forum_users WHERE username = ? OR email = ?");
    $query->execute([$username, $email]);
    if ($query->rowCount() > 0) {
        $_SESSION['register_error'] = "Username or email already in use";
        header("Location: ../../register.php");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = $db->prepare("
        INSERT INTO forum_users (username, email, password, admin) 
        VALUES (?, ?, ?, 0)
    ");

    try {
        $query->execute([$username, $email, $hashed_password]);
        
        $_SESSION['user_id'] = $db->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['admin'] = 0;
        
        unset($_SESSION['register_input_data']);
        
        header("Location: ../../index.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['register_error'] = "Registration failed: " . $e->getMessage();
        header("Location: ../../register.php");
        exit;
    }
} else {
    header("Location: ../../register.php");
    exit;
}