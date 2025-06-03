<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$query = $db->prepare('DELETE FROM forum_users WHERE user_id = ?');
$query->execute([$user_id]);

session_unset();
session_destroy();

header('Location: ../../index.php');
exit; 