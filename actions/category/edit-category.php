<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    render_error("Access denied.", 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    
    if ($category_id < 1 || empty($name) || strlen($name) > 50) {
        $_SESSION['category_error'] = "Invalid category ID or category name cannot be empty and must be less than 50 characters.";
        header("Location: ../../admin.php");
        exit();
    }

    $query = $db->prepare("SELECT category_id FROM forum_categories WHERE name = ? AND category_id != ?");
    $query->execute([$name, $category_id]);

    if ($query->rowCount() > 0) {
        $_SESSION['category_error'] = "Category name already exists.";
        header("Location: ../../admin.php");
        exit();
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




