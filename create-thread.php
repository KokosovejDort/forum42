<?php
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$query = $db->query("SELECT category_id, name FROM forum_categories ORDER BY name");
$categories = $query->fetchAll(PDO::FETCH_ASSOC);

$error = $_SESSION['thread_error'] ?? '';
unset($_SESSION['thread_error']);

$input_data = $_SESSION['thread_input_data'] ?? [];
unset($_SESSION['thread_input_data']);
?>

<div class="page-container">
    <div class="content-card">
        <div class="content-card-header">
            <h2>Create New Thread</h2>
        </div>
        <div class="content-card-body">
            <?php if (!empty($error)): ?>
                <div class="status-badge error-badge" style="margin-bottom: 1rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="post" action="actions/thread/create-thread.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Thread Title:</label>
                    <input type="text" class="form-input" id="title" name="title" value="<?= htmlspecialchars($input_data['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="images">Images (optional):</label>
                    <input type="file" class="form-input" id="images" name="images[]" accept="image/*" multiple>
                    <small style="color: #666;">Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF</small>
                </div>
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select class="form-input" id="category" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>" <?= (($input_data['category_id'] ?? '') == $category['category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="content">Initial Post:</label>
                    <textarea class="form-input min-textarea" id="content" name="content" rows="5" required><?= htmlspecialchars($input_data['content'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-action btn-primary">Create Thread</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/include/footer.php'; ?>