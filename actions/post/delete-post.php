<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id'])) {
    render_error("You must be logged in to delete posts.", 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;

    if ($post_id < 1 || $thread_id < 1) {
        render_error("Invalid post ID or thread ID.", 400);
    }

    $permissions = getPostPermissions($post_id, $_SESSION['user_id'], $_SESSION['admin'] ?? false);
    if ($permissions['error']) {
        render_error($permissions['error']['message'], $permissions['error']['code']);
    }

    if (!$permissions['can_delete']) {
        render_error("You don't have permission to delete this post.", 403);
    }

    $thread = fetchThreadById($thread_id);

    $query = $db->prepare("DELETE FROM forum_posts_votes WHERE post_id = ?");
    $query->execute([$post_id]);

    $query = $db->prepare("DELETE FROM forum_posts WHERE post_id = ?");
    $query->execute([$post_id]);

    if ($thread && $thread['initial_post_id'] == $post_id) {
        $query = $db->prepare("DELETE FROM forum_threads WHERE thread_id = ?");
        $query->execute([$thread['thread_id']]);
        header("Location: ../../index.php");
        exit();
    } else {
        header("Location: ../../thread.php?id=" . $thread_id);
        exit();
    }
}
else {
    render_error("Method Not Allowed. This endpoint accepts POST only.", 405);
}