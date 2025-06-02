<?php
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    render_error("Access denied.", 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        render_error("Category name cannot be empty.", 400);
    }
    
    $query = $db->prepare("INSERT INTO forum_categories (name) VALUES (?)");
    $query->execute([$name]);
    
    header("Location: ../../admin.php");
    exit();
} else {
    header("Location: ../../admin.php");
    exit();
}


