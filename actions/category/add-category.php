<?php
session_start();
require_once __DIR__.'/../../include/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        die("Category name cannot be empty.");
    }
    
    $query = $db->prepare("INSERT INTO forum_categories (name) VALUES (?)");
    $query->execute([$name]);
    
    header("Location: ../../admin.php");
    exit();
} else {
    header("Location: ../../admin.php");
    exit();
}


