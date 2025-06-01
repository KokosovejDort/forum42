<?php
session_start();
require_once __DIR__.'/../../include/db.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to close a thread.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;

    if ($thread_id < 1) {
        die("Invalid thread ID.");
    }

    $query = $db->prepare("
        SELECT author_id
        FROM forum_threads
        WHERE thread_id = ?
    ");
    $query->execute([$thread_id]);
    $thread = $query->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        die("Thread not found.");
    }

    if ($thread['author_id'] != $_SESSION['user_id'] && !$_SESSION['admin']) {
        die("You don't have permission to close this thread.");
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
    header("Location: ../../index.php");
    exit();
}
