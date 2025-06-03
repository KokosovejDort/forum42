<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $_SESSION['login_username'] = $username;

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Both username and password are required";
        header("Location: ../../login.php");
        exit;
    }

    $query = $db->prepare("SELECT user_id, username, password, admin FROM forum_users WHERE username = ?");
    $query->execute([$username]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = "Invalid username or password";
        header("Location: ../../login.php");
        exit;
    }

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['admin'] = $user['admin'];

    unset($_SESSION['login_username']);
    header("Location: ../../index.php");
    exit;
}
else {
    header("Location: ../../login.php");
    exit;
}