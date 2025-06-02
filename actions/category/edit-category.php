<?php
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    render_error("Access denied.", 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    
    if ($category_id < 1 || empty($name)) {
        render_error("Invalid input.", 400);
    }

    $query = $db->prepare("UPDATE forum_categories SET name = ? WHERE category_id = ?");
    $query->execute([$name, $category_id]);

    header("Location: ../../admin.php");
    exit();
}
else {
    header("Location: ../../admin.php");
    exit();
}




