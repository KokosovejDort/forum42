<?php
define('APP_INIT', true);
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

$username = $_SESSION['login_username'] ?? '';
unset($_SESSION['login_username']);

?>

<div class="page-container">
    <div class="content-card">
        <div class="content-card-header">
            <h2>Login</h2>
        </div>
        <div class="content-card-body">
            <?php if (!empty($error)): ?>
                <div class="status-badge error-badge" style="margin-bottom: 1rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="post" action="actions/user-management/login.php">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-input" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-input" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-action btn-primary" style="margin-top: 1rem;">Login</button>
            </form>
            <p style="margin-top: 1.5rem;">Don't have an account? <a href="register.php">Register here</a></p>
            <p style="margin-top: 1rem;"> <a href="actions/user-management/reset/request-reset.php">Forgot your password?</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/include/footer.php'; ?>