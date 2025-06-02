<?php
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';
require_once __DIR__.'/include/error-handler.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = fetchUserProfile($user_id);

if (!$user) {
    render_error("User not found", 404);
}

$stats = fetchUserStats($user_id);

$all_threads = fetchUserRecentThreads($user_id);
$all_posts = fetchUserRecentPosts($user_id);

$threads = array_slice($all_threads, 0, 5);
$posts = array_slice($all_posts, 0, 5);

?>

<div class="page-container">
    <div class="profile-layout">
        <div class="profile-sidebar">
            <div class="content-card">
                <div class="content-card-header">
                <h3>Profile Information</h3>
            </div>
                <div class="content-card-body">
                    <div class="profile-info">
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
            </div>

            <div class="content-card" style="margin-top: 1rem;">
                <div class="content-card-header">
                    <h3>Statistics</h3>
                </div>
                <div class="content-card-body">
                    <div class="profile-info">
                <p><strong>Threads Created:</strong> <?= (int)$stats['thread_count'] ?></p>
                <p><strong>Posts Written:</strong> <?= (int)$stats['post_count'] ?></p>
                <p><strong>Total Upvotes Received:</strong> <?= (int)$stats['total_votes'] ?></p>
                    </div>
            </div>
        </div>
    </div>

        <div class="profile-main">
            <div class="content-card">
                <div class="content-card-header">
                <h3>Your Threads</h3>
            </div>
                <div class="content-card-body">
                <?php if (!empty($threads)): ?>
                        <div class="thread-list">
                        <?php foreach ($threads as $thread): ?>
                                <div class="thread-item">
                                    <div class="thread-item-header">
                                        <div class="thread-item-title">
                                        <a href="thread.php?id=<?= $thread['thread_id'] ?>">
                                            <?= htmlspecialchars($thread['title']) ?>
                                        </a>
                                            <span class="thread-category">in <?= htmlspecialchars($thread['category_name']) ?></span>
                                        </div>
                                        <span class="status-badge"><?= $thread['reply_count'] ?> replies</span>
                                    </div>
                                    <div class="thread-item-meta">
                                        <small>Created: <?= htmlspecialchars(date('M j, Y', strtotime($thread['created_at']))) ?></small>
                                    <?php if ($thread['is_closed']): ?>
                                            <span class="status-badge status-badge-secondary">Closed</span>
                                    <?php endif; ?>
                                    </div>
                                </div>
                        <?php endforeach; ?>
                        </div>
                    <?php if ($stats['thread_count'] > 5): ?>
                            <div class="view-more-link">
                            <a href="index.php">View all threads</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p>You haven't created any threads yet.</p>
                <?php endif; ?>
            </div>
        </div>

            <div class="content-card" style="margin-top: 1rem;">
                <div class="content-card-header">
                <h3>Your Recent Posts</h3>
            </div>
                <div class="content-card-body">
                <?php if (!empty($posts)): ?>
                        <div class="post-list">
                        <?php foreach ($posts as $post): ?>
                                <div class="post-item">
                                    <div class="post-item-header">
                                <a href="thread.php?id=<?= $post['thread_id'] ?>">
                                    <?= htmlspecialchars($post['thread_title']) ?>
                                </a>
                                    </div>
                                    <div class="post-item-content">
                                        <small>
                                        <?= htmlspecialchars(substr($post['content'], 0, 100)) ?>
                                        <?= (strlen($post['content']) > 100) ? '...' : '' ?>
                                    </small>
                                </div>
                                    <div class="post-item-meta">
                                        <small>Posted: <?= htmlspecialchars(date('M j, Y', strtotime($post['updated']))) ?></small>
                                    </div>
                                </div>
                        <?php endforeach; ?>
                        </div>
                    <?php if ($stats['post_count'] > 5): ?>
                            <div class="view-more-link">
                            <a href="index.php">View all threads</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p>You haven't replied to any threads yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>


<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
    <form method="post" action="actions/user-management/delete-profile.php"
          onsubmit="return confirm('Are you sure you want to delete your profile? This action cannot be undone.');"
          style="margin: 0.5rem auto 0 auto; text-align: center; max-width: 600px;">
        <button type="submit" class="btn-action btn-danger" style="width: 100%;">Delete Profile</button>
    </form>
<?php endif; ?>

<?php require_once __DIR__.'/include/footer.php'; ?>