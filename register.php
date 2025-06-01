<?php
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';

$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);

$input_data = $_SESSION['register_input_data'] ?? [];
unset($_SESSION['register_input_data']);
?>

<div class="page-container">
    <div class="content-card">
        <div class="content-card-header">
            <h2>Register</h2>
        </div>
        <div class="content-card-body">
            <?php if (!empty($error)): ?>
                <div class="status-badge error-badge" style="margin-bottom: 1rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="post" action="actions/user-management/register.php">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-input" id="username" name="username" value="<?= htmlspecialchars($input_data['username'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-input" id="email" name="email" value="<?= htmlspecialchars($input_data['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-input" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirm Password:</label>
                    <input type="password" class="form-input" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="btn-action btn-primary" style="margin-top: 1rem;">Register</button>
            </form>
            <p style="margin-top: 1.5rem;">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/include/footer.php'; ?>