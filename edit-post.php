<?php
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';
require_once __DIR__.'/include/error-handler.php';

if (!isset($_SESSION['user_id'])) {
    render_error("You must be logged in to edit posts.", 401);
}

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id < 1) {
    render_error("Invalid post ID.", 400);
}

$permissions = getPostPermissions($post_id, $_SESSION['user_id'], $_SESSION['admin'] ?? false);
if ($permissions['error']) {
    render_error($permissions['error']['message'], $permissions['error']['code']);
}

if (!$permissions['can_edit']) {
    render_error("You don't have permission to edit this post.", 403);
}

$post = fetchPostById($post_id);
if (!$post) {
    render_error("Post not found.", 404);
}

$thread = fetchThreadById($post['thread_id']);
if (!$thread) {
    render_error("Thread not found.", 404);
}

if ($post['is_closed'] && !($_SESSION['admin'] ?? false)) {
    $_SESSION['error'] = "Cannot edit posts in closed threads.";
    header("Location: thread.php?id=" . $post['thread_id']);
    exit();
}

$image_query = $db->prepare("SELECT image_id, image_path FROM post_images WHERE post_id = ?");
$image_query->execute([$post_id]);
$images = $image_query->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-container">
    <div class="content-card">
        <div class="content-card-header">
            <h2>Edit Post</h2>
        </div>
        <div class="content-card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="status-badge error-badge">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']); 
                    ?>
                </div>
            <?php endif; ?>
            <form method="post" action="actions/post/edit-post.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="content">Content:</label>
                    <textarea class="form-input min-textarea" id="content" name="content" rows="5" required><?= htmlspecialchars($post['content']) ?></textarea>
                </div>
                <?php if (!empty($images)): ?>
                    <div class="form-group">
                        <label>Current Images:</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                            <?php foreach ($images as $image): ?>
                                <div class="image-thumb">
                                    <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Post image" class="image-thumb-img">
                                    <div>
                                        <input type="checkbox" name="delete_images[]" value="<?= $image['image_id'] ?>">
                                        <label>Delete this image</label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="images" class="form-padding">New Images (optional):</label>
                    <input type="file" class="form-input form-padding" id="images" name="images[]" multiple accept="image/*">
                    <small style="color: #666;" class="form-padding">Maximum file size: 5MB per image. Allowed formats: JPG, PNG, GIF. You can select multiple images.</small>
                </div>
                <input type="hidden" name="post_id" value="<?= $post_id ?>">
                <input type="hidden" name="thread_id" value="<?= $post['thread_id'] ?>">
                <input type="hidden" name="last_updated" value="<?= $post['updated'] ?>">
                <?php if ($_SESSION['admin']): ?>
                    <div class="form-group">
                        <input type="checkbox" id="force_save" name="force_save">
                        <label for="force_save">Force save changes (admin only)</label>
                    </div>
                <?php endif; ?>
                <?php 
                    $thread_id = $post['thread_id'];
                ?>
                <div class="button-row">
                    <button type="submit" class="btn-action btn-primary">Save Changes</button>
                    <a href="thread.php?id=<?= $thread_id ?>" class="btn-action">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/include/footer.php'; ?>