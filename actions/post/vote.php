<?php
session_start();
require_once __DIR__.'/../../include/db.php';
require_once __DIR__.'/../../include/error-handler.php';

if (!isset($_SESSION['user_id'])) {
	render_error("You must be logged in to vote.", 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
	$vote_type = isset($_POST['vote_type']) ? (int)$_POST['vote_type'] : 0;
	if ($post_id < 1 || !in_array($vote_type, [1, -1], true)) {
		render_error("Missing or invalid vote data.", 400);
	}

	$permissions = getPostPermissions($post_id, $_SESSION['user_id'], $_SESSION['admin'] ?? false);
	if ($permissions['error']) {
		render_error($permissions['error']['message'], $permissions['error']['code']);
	}

	if (!$permissions['can_vote']) {
		render_error("You don't have permission to vote on this post.", 403);
	}

	$query = $db->prepare("SELECT * FROM forum_posts_votes WHERE post_id = ? AND author_id = ?");
	$query->execute([$post_id, $_SESSION['user_id']]);
	$existing_vote = $query->fetch(PDO::FETCH_ASSOC);

	if ($existing_vote) {
		if ($existing_vote['vote_type'] == $vote_type) {
			$query = $db->prepare("DELETE FROM forum_posts_votes WHERE post_id = :post AND author_id = :user");
			$query->execute([
				'post' => $post_id,
				'user' => $_SESSION['user_id']
			]);
		} else {
			$query = $db->prepare("UPDATE forum_posts_votes SET vote_type = :vote WHERE post_id = :post AND author_id = :user");
			$query->execute([
				'post' => $post_id,
				'user' => $_SESSION['user_id'],
				'vote' => $vote_type
					
			]);
		}
	} else {
		$query = $db->prepare("INSERT INTO forum_posts_votes (post_id, author_id, vote_type) VALUES (:post, :user, :vote)");
		$query->execute([
			'post' => $post_id,
			'user' => $_SESSION['user_id'],
			'vote' => $vote_type
		]);
	}

	header("Location: ../../thread.php?id=" . ($_POST['thread_id'] ?? ''));
	exit();
}
else {
	render_error("Invalid request method.", 405);
}