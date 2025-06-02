<?php
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__ . '/../../include/error-handler.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    $_SESSION['thread_input_data'] = [
        'title' => $title,
        'category_id' => $category_id,
        'content' => $content
    ];

    if (empty($title) || strlen($title) < 3 || strlen($title) > 50) {
        $_SESSION['thread_error'] = "Title must be between 3 and 50 characters";
        header("Location: ../../create-thread.php");
        exit;
    }

    if ($category_id < 1) {
        $_SESSION['thread_error'] = "Please select a valid category";
        header("Location: ../../create-thread.php");
        exit;
    }

    if (empty($content)) {
        $_SESSION['thread_error'] = "Initial post content is required";
        header("Location: ../../create-thread.php");
        exit;
    }

    $query = $db->prepare("SELECT category_id FROM forum_categories WHERE category_id = ?");
    $query->execute([$category_id]);
    if ($query->rowCount() === 0) {
        $_SESSION['thread_error'] = "Selected category does not exist";
        header("Location: ../../create-thread.php");
        exit;
    }

    $query = $db->prepare("SELECT thread_id FROM forum_threads WHERE title = ?");
    $query->execute([$title]);
    if ($query->rowCount() > 0) {
        $_SESSION['thread_error'] = "A thread with this title already exists. Please choose a different title.";
        header("Location: ../../create-thread.php");
        exit;
    }

    $query = $db->prepare("
        INSERT INTO forum_threads (title, author_id, category_id, created_at, is_closed) 
        VALUES (?, ?, ?, NOW(), 0)
    ");
    
    $query->execute([$title, $_SESSION['user_id'], $category_id]);
    $thread_id = $db->lastInsertId();

    $query = $db->prepare("
        INSERT INTO forum_posts (content, updated, thread_id, author_id)
        VALUES (?, NOW(), ?, ?)
    ");
    $query->execute([$content, $thread_id, $_SESSION['user_id']]);
    $post_id = $db->lastInsertId();

    $query = $db->prepare("
        UPDATE forum_threads 
        SET initial_post_id = ? 
        WHERE thread_id = ?
    ");
    $query->execute([$post_id, $thread_id]);

    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
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
                    render_error("Invalid file type. Only JPG, PNG, and GIF are allowed.");
                }

                if ($file['size'] > $max_size) {
                    render_error("File is too large. Maximum size is 5MB.");
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
                    render_error("Failed to upload image.");
                }
            }
        }
    }

    unset($_SESSION['thread_input_data']);
    header("Location: ../../thread.php?id=" . $thread_id);
    exit;
}
else {
    header("Location: ../../create-thread.php");
    exit;
}