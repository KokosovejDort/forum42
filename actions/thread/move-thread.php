<?php
session_start();
require_once __DIR__.'/../../include/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;
    $new_category_id = isset($_POST['new_category_id']) ? (int)$_POST['new_category_id'] : 0;

    if ($thread_id < 1 || $new_category_id < 1) {
        die("Invalid thread ID or category ID.");
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
        die("Thread or category not found.");
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