<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    render_error("Access denied.", 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name) || strlen($name) > 50) {
        $_SESSION['category_error'] = "Category name cannot be empty and must be less than 50 characters.";
        header("Location: ../../admin.php");
        exit();
    }
    
    // Check if category already exists
    $query = $db->prepare("SELECT category_id FROM forum_categories WHERE name = ?");
    $query->execute([$name]);
    
    if ($query->rowCount() > 0) {
        $_SESSION['category_error'] = "Category already exists.";
        header("Location: ../../admin.php");
        exit();
    }
    
    $query = $db->prepare("INSERT INTO forum_categories (name) VALUES (?)");
    $query->execute([$name]);
    
    header("Location: ../../admin.php");
    exit();
} else {
    header("Location: ../../admin.php");
    exit();
}


