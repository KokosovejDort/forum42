<?php
session_start();
require_once __DIR__.'/../../include/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($user_id < 0) {
        die("Invalid user ID.");
    }

    if ($user_id == $_SESSION['user_id']) {
        die("You cannot change your own admin status.");
    }

    $query = $db->prepare("SELECT admin FROM forum_users WHERE user_id = ?");
    $query->execute([$user_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("User not found.");
    }

    $new_status = $user['admin'] ? 0 : 1;
    $query = $db->prepare("UPDATE forum_users SET admin = ? WHERE user_id = ?");
    $query->execute([$new_status, $user_id]);

    header("Location: ../../admin.php");
    exit();
} else {
    header("Location: ../../admin.php");
    exit();
}
