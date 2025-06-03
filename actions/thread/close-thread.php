<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id'])) {
    render_error("You must be logged in to close a thread.", 403);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;

    if ($thread_id < 1) {
        render_error("Invalid thread ID.", 400);
    }

    $query = $db->prepare("
        SELECT author_id
        FROM forum_threads
        WHERE thread_id = ?
    ");
    $query->execute([$thread_id]);
    $thread = $query->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        render_error("Thread not found.", 404);
    }

    if ($thread['author_id'] != $_SESSION['user_id'] && !$_SESSION['admin']) {
        render_error("You don't have permission to close this thread.", 403);
    }

    $query = $db->prepare("
        UPDATE forum_threads
        SET is_closed = 1
        WHERE thread_id = ?
    ");
    $query->execute([$thread_id]);

    header('Location: ../../thread.php?id='.$thread_id);
    exit;
} else {
    header("Location: ../../admin.php");
    exit();
}
