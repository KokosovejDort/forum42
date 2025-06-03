<?php
define('APP_INIT', true);
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__ . '/../../include/error-handler.php';

if (!isset($_SESSION['user_id'])) {
	render_error("You must be logged in to reply.", 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;
	$content = trim($_POST['content'] ?? '');

	if ($thread_id < 1 || empty($content)) {
		render_error("Missing thread or empty content.");
	}

	$query = $db->prepare("SELECT * FROM forum_threads WHERE thread_id = ?");
	$query->execute([$thread_id]);
	$thread = $query->fetch(PDO::FETCH_ASSOC);
	if (!$thread || $thread['is_closed']) {
		render_error("Thread not found or closed.");
	}
	$query = $db->prepare("
        INSERT INTO forum_posts (content, updated, thread_id, author_id)
        VALUES (?, NOW(), ?, ?)
    ");
	$query->execute([
		$content,
		$thread_id,
		$_SESSION['user_id']
	]);

	$post_id = $db->lastInsertId();

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

	header("Location: ../../thread.php?id=" . $thread_id);
	exit();
}
else {
	render_error("Method Not Allowed. This endpoint accepts POST only.", 405);
}