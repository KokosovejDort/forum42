<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    render_error("Access denied.", 403);
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    if ($category_id < 1) {
        render_error("Invalid category ID.", 400);
    }

    $query = $db->prepare("SELECT COUNT(*) FROM forum_threads WHERE category_id = ?");
    $query->execute([$category_id]);
    $thread_count = $query->fetchColumn();

    if ($thread_count > 0) {
        render_error("Cannot delete category with threads. Move or delete the threads first.", 400);
    }

    $query = $db->prepare("DELETE FROM forum_categories WHERE category_id = ?");
    $query->execute([$category_id]);

    header("Location: ../../admin.php");
    exit();
}
else {
    header("Location ../../admin.php");
    exit();
}





