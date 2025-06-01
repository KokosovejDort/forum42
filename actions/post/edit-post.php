<?php
session_start();

require_once __DIR__.'/../../include/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;
        $content = trim($_POST['content'] ?? '');
        $last_updated = $_POST['last_updated'] ?? '';
        $force_save = isset($_POST['force_save']) && $_SESSION['admin'];

        if ($post_id < 1 || $thread_id < 1) {
            die("Invalid post ID or thread ID.");
        }

        if (empty($content)) {
            die("Content cannot be empty.");
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
            die("Post not found.");
        }

        if (!$force_save && $post['updated'] !== $last_updated) {
            $_SESSION['error'] = "The post was modified by someone else while you were editing.";
            header("Location: ../../edit-post.php?id=" . $post_id);
            exit();
        }

        if ($post['is_closed'] && !$_SESSION['admin']) {
            die("Cannot edit posts in closed threads.");
        }

        if ($post['author_id'] != $_SESSION['user_id'] && !$_SESSION['admin']) {
            die("You don't have permission to edit this post.");
        }

        error_log("Updating post ID: " . $post_id . " with content: " . $content);

        if ($force_save) {
            $query = $db->prepare("
                UPDATE forum_posts 
                SET content = ?, updated = NOW()
                WHERE post_id = ?
            ");
            $result = $query->execute([$content, $post_id]);
        } else {
            $query = $db->prepare("
                UPDATE forum_posts 
                SET content = ?, updated = NOW()
                WHERE post_id = ? AND updated = ?
            ");
            $result = $query->execute([$content, $post_id, $last_updated]);
        }
        
        if (!$result) {
            error_log("Update failed: " . print_r($query->errorInfo(), true));
            die("Failed to update post due to a database error.");
        }

        if (!$force_save && $query->rowCount() === 0) {
            $_SESSION['error'] = "The post was modified by someone else while you were editing.";
            header("Location: ../../edit-post.php?id=" . $post_id);
            exit();
        }

        if (isset($_POST['delete_images']) && !empty($_POST['delete_images'])) {
            $query = $db->prepare("
                SELECT image_path 
                FROM post_images 
                WHERE image_id IN (" . implode(',', array_fill(0, count($_POST['delete_images']), '?')) . ")
            ");
            $query->execute($_POST['delete_images']);
            $images_to_delete = $query->fetchAll(PDO::FETCH_COLUMN);

            $query = $db->prepare("
                DELETE FROM post_images 
                WHERE image_id IN (" . implode(',', array_fill(0, count($_POST['delete_images']), '?')) . ")
            ");
            $query->execute($_POST['delete_images']);

            foreach ($images_to_delete as $image_path) {
                $full_path = __DIR__ . '/../../' . $image_path;
                if (file_exists($full_path)) {
                    unlink($full_path);
                }
            }
        }

        if (isset($_FILES['images'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; 

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'size' => $_FILES['images']['size'][$key]
                    ];

                    if (!in_array($file['type'], $allowed_types)) {
                        die("Invalid file type. Only JPG, PNG, and GIF are allowed.");
                    }

                    if ($file['size'] > $max_size) {
                        die("File is too large. Maximum size is 5MB.");
                    }

                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $extension;
                    $upload_path = __DIR__ . '/../../uploads/' . $filename;

                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $query = $db->prepare("
                            INSERT INTO post_images (post_id, image_path)
                            VALUES (?, ?)
                        ");
                        $query->execute([$post_id, 'uploads/' . $filename]);
                    } else {
                        die("Failed to upload image.");
                    }
                }
            }
        }

        header("Location: ../../thread.php?id=" . $thread_id);
        exit();
    } catch (PDOException $e) {
        error_log("Database error in edit-post action: " . $e->getMessage());
        die("A database error occurred. Please try again later.");
    }
} else {
    header("Location: ../../index.php");
    exit();
}