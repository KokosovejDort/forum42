<?php
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!$_SESSION['admin']) {
    render_error("Access denied.", 403);
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if ($user_id < 1) {
    render_error("Invalid user ID.", 400);
}

if ($user_id == $_SESSION['user_id']) {
    render_error("You cannot change your own admin status.", 400);
}

$query = $db->prepare("SELECT * FROM forum_users WHERE user_id = ?");
$query->execute([$user_id]);
if ($query->rowCount() === 0) {
    render_error("User not found.", 404);
}

$user = $query->fetch(PDO::FETCH_ASSOC);

$new_status = $user['admin'] ? 0 : 1;
$query = $db->prepare("UPDATE forum_users SET admin = ? WHERE user_id = ?");
$query->execute([$new_status, $user_id]);

header("Location: ../../admin.php");
exit();
