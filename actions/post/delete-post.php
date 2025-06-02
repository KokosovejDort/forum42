<?php
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id'])) {
    render_error("You must be logged in to delete posts.", 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;

    if ($post_id < 1 || $thread_id < 1) {
        render_error("Invalid post ID or thread ID.", 400);
    }

    $query = $db->prepare("
        SELECT p.*, t.is_closed 
        FROM forum_posts p
        JOIN forum_threads t ON p.thread_id = t.thread_id
        WHERE p.post_id = ?
    ");
    $query->execute([$post_id]);
    $post = $query->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        render_error("Post not found.", 404);
    }

    if ($post['is_closed'] && !$_SESSION['admin']) {
        render_error("Cannot delete posts in closed threads.", 400);
    }

    if ($post['author_id'] != $_SESSION['user_id'] && !$_SESSION['admin']) {
        render_error("You don't have permission to delete this post.", 403);
    }

    $query = $db->prepare("DELETE FROM forum_posts_votes WHERE post_id = ?");
    $query->execute([$post_id]);

    $query = $db->prepare("DELETE FROM forum_posts WHERE post_id = ?");
    $query->execute([$post_id]);
    header("Location: ../../thread.php?id=" . $thread_id);
    exit();
}
else {
    header("Location: ../../index.php");
    exit();
}