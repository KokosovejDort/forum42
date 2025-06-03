<?php
define('APP_INIT', true);
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';

if (!isset($_SESSION["user_id"]) || !$_SESSION['admin'] || !isset($_SESSION["admin"])) {
    header("Location: index.php");
    exit();
}

$users = fetchAllUsers();
$categories = fetchAllCategories();
?>

<div class="page-container">
    <h1>Admin Panel</h1>

    <?php if (isset($_SESSION['category_error'])): ?>
        <div class="status-badge error-badge" style="margin-bottom: 1rem;">
            <?= htmlspecialchars($_SESSION['category_error']) ?>
        </div>
        <?php unset($_SESSION['category_error']); ?>
    <?php endif; ?>

    <!-- Thread Management -->
    <div class="content-card">
        <div class="content-card-header">
            <h2>Thread Management</h2>
        </div>
        <div class="content-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $threads = fetchAllThreadsForAdmin();
                ?>
                <?php foreach ($threads as $thread): ?>
                    <tr>
                        <td><?= $thread['thread_id'] ?></td>
                        <td>
                            <a href="thread.php?id=<?= $thread['thread_id'] ?>">
                                <?= htmlspecialchars($thread['title']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($thread['author_name'] ?? '[Deleted User]') ?></td>
                        <td><?= htmlspecialchars($thread['category_name']) ?></td>
                        <td>
                            <span class="status-badge <?= $thread['is_closed'] ? 'status-badge-danger' : 'status-badge-success' ?>">
                                <?= $thread['is_closed'] ? 'Closed' : 'Open' ?>
                            </span>
                        </td>
                        <td>
                            <div class="button-row">
                                <form method="post" action="actions/thread/move-thread.php" class="inline-block" style="display: flex; gap: 0.5rem; align-items: center; margin: 0;">
                                    <input type="hidden" name="thread_id" value="<?= $thread['thread_id'] ?>">
                                    <select name="new_category_id" class="form-input">
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['category_id'] ?>" <?= $category['category_id'] == $thread['category_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn-action btn-outline-primary">
                                        <i class="bi bi-arrow-right-circle"></i> Move
                                    </button>
                                </form>
                                <form method="post" action="actions/thread/delete-thread.php" class="inline-block" style="margin: 0;">
                                    <input type="hidden" name="thread_id" value="<?= $thread['thread_id'] ?>">
                                    <button type="submit" class="btn-action btn-outline-danger" onclick="return confirm('Are you sure you want to delete this thread?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Management -->
    <div class="content-card">
        <div class="content-card-header">
            <h2>User Management</h2>
        </div>
        <div class="content-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="status-badge <?= $user['admin'] ? 'status-badge-danger' : 'status-badge-secondary' ?>">
                                    <?= $user['admin'] ? 'Admin' : 'User' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <form method="post" action="actions/user-management/toggle-admin.php" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        <button type="submit" class="btn-action <?= $user['admin'] ? 'btn-outline-danger' : 'btn-outline-primary' ?>" onclick="return confirm('Are you sure you want to <?= $user['admin'] ? 'remove' : 'grant' ?> admin privileges?')">
                                            <i class="bi <?= $user['admin'] ? 'bi-person-x' : 'bi-person-check' ?>"></i>
                                            <?= $user['admin'] ? 'Remove Admin' : 'Make Admin' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="status-badge status-badge-secondary">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Management -->
    <div class="content-card">
        <div class="content-card-header">
            <h2>Category Management</h2>
        </div>
        <div class="content-card-body">
            <div class="category-add-wrap">
                <form method="post" action="actions/category/add-category.php">
                    <div>
                        <input type="text" class="form-input wide" id="categoryName" name="name" placeholder="New Category Name" required>
                        <button type="submit" class="btn-action btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Category
                        </button>
                    </div>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Threads</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= $category['category_id'] ?></td>
                            <td><?= htmlspecialchars($category['name']) ?></td>
                            <td>
                                <span class="status-badge status-badge-secondary"><?= $category['thread_count'] ?></span>
                            </td>
                            <td>
                                <div class="category-action-row">
                                    <input type="text" class="form-input wide" name="name" value="<?= htmlspecialchars($category['name']) ?>" required form="editcat<?= $category['category_id'] ?>">
                                    <form id="editcat<?= $category['category_id'] ?>" method="post" action="actions/category/edit-category.php" class="inline-block">
                                        <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                        <button type="submit" class="btn-action btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Update
                                        </button>
                                    </form>
                                    <?php if ($category['thread_count'] == 0): ?>
                                        <form method="post" action="actions/category/delete-category.php" class="inline-block">
                                            <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                            <button type="submit" class="btn-action btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/include/footer.php'; ?>
