<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    render_error("Access denied.", 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;

    if ($thread_id < 1) {
        render_error("Invalid thread ID.", 400);
    }

    $query = $db->prepare("SELECT thread_id FROM forum_threads WHERE thread_id = ?");
    $query->execute([$thread_id]);
    $thread = $query->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        render_error("Thread not found.", 404);
    }

    $query = $db->prepare("
        DELETE v FROM forum_posts_votes v
        JOIN forum_posts p ON v.post_id = p.post_id
        WHERE p.thread_id = ?
    ");
    $query->execute([$thread_id]);

    $query = $db->prepare("DELETE FROM forum_posts WHERE thread_id = ?");
    $query->execute([$thread_id]);

    $query = $db->prepare("DELETE FROM forum_threads WHERE thread_id = ?");
    $query->execute([$thread_id]);

    header("Location: ../../admin.php");
    exit();
}
else {
    header("Location ../../admin.php");
    exit();
}