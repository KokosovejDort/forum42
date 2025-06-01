<?php
session_start();
require_once __DIR__.'/../../include/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;

    if ($thread_id < 1) {
        die("Invalid thread ID.");
    }

    $query = $db->prepare("SELECT thread_id FROM forum_threads WHERE thread_id = ?");
    $query->execute([$thread_id]);
    $thread = $query->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        die("Thread not found.");
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