<?php
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';
require_once __DIR__.'/include/error-handler.php';

$thread_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($thread_id < 1) {
	render_error("Invalid thread ID", 400);
}
$user_id = $_SESSION['user_id'] ?? null;

$thread = fetchThreadById($thread_id);
if (!$thread) {
	render_error("Thread not found", 404);
}

$posts = fetchPostsByThread($thread_id);

$post_ids = array_column($posts, 'post_id');
$votes_data = fetchPostVotes($post_ids, $user_id);
$images = fetchPostImages($post_ids);

$votes = $votes_data['votes'] ?? [];
$user_votes = $votes_data['user_votes'] ?? [];

foreach ($posts as &$post) {
	$post['votes'] = $votes[$post['post_id']] ?? 0;
	$post['user_vote'] = $user_votes[$post['post_id']] ?? 0;
	$post['images'] = $images[$post['post_id']] ?? '';
}
unset($post);

function comparePosts($post1, $post2) {
	if ($post1['votes'] > $post2['votes']) {
		return -1; 
	}
	if ($post1['votes'] < $post2['votes']) {
		return 1;  
	}
	$time1 = strtotime($post1['updated']);
	$time2 = strtotime($post2['updated']);
	if ($time1 < $time2) {
		return -1; 
	}
	if ($time1 > $time2) {
		return 1;  
	}
	return 0; 
}

// Find and separate the initial post
$initial_post = null;
$other_posts = [];
foreach ($posts as $p) {
    if (isset($thread['initial_post_id']) && $p['post_id'] == $thread['initial_post_id']) {
        $initial_post = $p;
    } else {
        $other_posts[] = $p;
    }
}

usort($other_posts, 'comparePosts');

$display_posts = array_merge($initial_post ? [$initial_post] : [], $other_posts);
?>

<div class="page-container">
    <div class="content-card">
        <div class="content-card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0;"><?= htmlspecialchars($thread['title']) ?></h2>
<?php if (isset($_SESSION['user_id']) && $thread['author_id'] == $_SESSION['user_id'] && !$thread['is_closed']): ?>
                <form method="post" action="actions/thread/close-thread.php" style="margin: 0;">
        <input type="hidden" name="thread_id" value="<?= $thread_id ?>">
                    <button type="submit" class="btn-action btn-outline-primary" onclick="return confirm('Are you sure you want to close this thread? No further replies will be allowed.')">
                        <i class="bi bi-lock"></i> Close Thread
        </button>
    </form>
<?php endif; ?>
        </div>
        <div class="content-card-body">
            <div class="thread-meta thread-meta-gap" style="margin-bottom: 1.5rem;">
                <span class="thread-author">
                    <i class="bi bi-person"></i> <?= htmlspecialchars($thread['author_name']) ?>
                </span>
                <span class="thread-category">
                    <i class="bi bi-folder"></i> <?= htmlspecialchars($thread['category_name']) ?>
                </span>
                <span class="thread-category">
                    <i class="bi bi-clock"></i> <?= htmlspecialchars($thread['created_at']) ?>
                </span>
            </div>
<h3>Posts:</h3>
            <?php foreach ($display_posts as $idx => $post): ?>
	<?php
                $is_first = ($idx === 0);
	$upvoted = $post['user_vote'] == 1;
	$downvoted = $post['user_vote'] == -1;
	?>
                <div class="post-row<?= $is_first ? ' initial-post' : '' ?>">
                    <div class="vote-col">
				<?php if (isset($_SESSION['user_id'])): ?>
					<?php if (!$thread['is_closed'] || $_SESSION['admin']): ?>
                                <form method="post" action="actions/post/vote.php" style="margin-bottom: 0.5rem;">
                                    <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                                    <input type="hidden" name="vote_type" value="1">
                                    <input type="hidden" name="thread_id" value="<?= $thread_id ?>">
                                    <button type="submit" class="vote-btn<?= $upvoted ? ' active-up' : '' ?>">
                                        <i class="bi bi-chevron-up"></i>
                                    </button>
                                </form>
                                <div class="vote-count" style="color: <?= $post['votes'] > 0 ? '#198754' : ($post['votes'] < 0 ? '#dc3545' : '#888') ?>;">
                                    <?= $post['votes'] ?>
                                </div>
                                <form method="post" action="actions/post/vote.php" style="margin-top: 0.5rem;">
                                    <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                                    <input type="hidden" name="vote_type" value="-1">
                                    <input type="hidden" name="thread_id" value="<?= $thread_id ?>">
                                    <button type="submit" class="vote-btn<?= $downvoted ? ' active-down' : '' ?>">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </form>
					<?php else: ?>
                                <div style="margin-bottom: 0.5rem;"><i class="bi bi-chevron-up"></i></div>
                                <div class="vote-count" style="color: <?= $post['votes'] > 0 ? '#198754' : ($post['votes'] < 0 ? '#dc3545' : '#888') ?>;">
							<?= $post['votes'] ?>
						</div>
                                <div style="margin-top: 0.5rem;"><i class="bi bi-chevron-down"></i></div>
					<?php endif; ?>
				<?php else: ?>
                            <div style="margin-bottom: 0.5rem;"><i class="bi bi-chevron-up"></i></div>
                            <div class="vote-count" style="color: <?= $post['votes'] > 0 ? '#198754' : ($post['votes'] < 0 ? '#dc3545' : '#888') ?>;">
						<?= $post['votes'] ?>
					</div>
                            <div style="margin-top: 0.5rem;"><i class="bi bi-chevron-down"></i></div>
				<?php endif; ?>
			</div>
                    <div class="post-content-col">
				<p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
				<?php if (!empty($post['images'])): ?>
                            <div style="margin-top: 0.5rem;">
						<?php foreach (explode(',', $post['images']) as $image): ?>
							<a href="<?= htmlspecialchars($image) ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($image) ?>" alt="Post image" class="post-image-thumb">
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<small>
					Posted by <?= htmlspecialchars($post['author_name'] ?: "Deleted user") ?>,
					Updated: <?= htmlspecialchars($post['updated']) ?>
				</small>
				<?php if (isset($_SESSION['user_id']) && ($post['author_id'] == $_SESSION['user_id'] || $_SESSION['admin']) && (!$thread['is_closed'] || $_SESSION['admin'])): ?>
                            <div class="button-row" style="margin-top: 1rem;">
                                <a href="edit-post.php?id=<?= $post['post_id'] ?>" class="btn-action btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form method="post" action="actions/post/delete-post.php" style="display:inline-block;">
							<input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
							<input type="hidden" name="thread_id" value="<?= $thread_id ?>">
                                    <button type="submit" class="btn-action btn-outline-danger" onclick="return confirm('Are you sure you want to delete this post?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
						</form>
					</div>
				<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>
<?php if (isset($_SESSION['user_id']) && !$thread['is_closed']): ?>
                <h3 style="margin-top: 2rem;">Add your reply</h3>
	<form action="actions/post/create-post.php" method="post" enctype="multipart/form-data">
		<div class="form-group">
                        <textarea name="content" class="form-input min-textarea" rows="5" required></textarea>
			<input type="hidden" name="thread_id" value="<?= $thread_id ?>">
                        <div class="form-padding">
			<label for="images">Attach Images (optional):</label>
                            <input type="file" class="form-input" id="images" name="images[]" multiple accept="image/*">
                            <small style="color: #666;">Maximum file size: 5MB per image. Allowed formats: JPG, PNG, GIF. You can select multiple images.</small>
			</div>
		</div>
                    <button type="submit" class="btn-action btn-primary" style="margin-top: 1rem;">
                        <i class="bi bi-reply"></i> Submit Reply
                    </button>
	</form>
<?php elseif($thread['is_closed']): ?>
                <div class="status-badge" style="background: #adb5bd; margin-top: 2rem;">
                    <i class="bi bi-lock"></i> This thread is closed.
                </div>
<?php else: ?>
                <div class="status-badge" style="background: #0d6efd; margin-top: 2rem;">
                    Please <a href="login.php" class="inline-block" style="color: #fff; text-decoration: underline;">login</a> to reply.
                </div>
<?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/include/footer.php'; ?>
