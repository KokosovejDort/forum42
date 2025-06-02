<?php
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    render_error("Access denied.", 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;
    $new_category_id = isset($_POST['new_category_id']) ? (int)$_POST['new_category_id'] : 0;

    if ($thread_id < 1 || $new_category_id < 1) {
        render_error("Invalid thread ID or category ID.", 400);
    }

    $query = $db->prepare("
    SELECT t.thread_id, t.category_id 
    FROM forum_threads t
    JOIN forum_categories c ON c.category_id = ?
    WHERE t.thread_id = ?
    ");
    $query->execute([$new_category_id, $thread_id]);
    $thread = $query->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        render_error("Thread or category not found.", 404);
    }

    if ($thread['category_id'] == $new_category_id) {
        header("Location: ../../admin.php");
        exit();
    }

    $query = $db->prepare("
    UPDATE forum_threads 
    SET category_id = ? 
    WHERE thread_id = ?
    ");
    $query->execute([$new_category_id, $thread_id]);

    header("Location: ../../admin.php");
    exit();
}
else {
    header("Location ../../admin.php");
    exit();
}