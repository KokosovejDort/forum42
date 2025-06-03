<?php
define('APP_INIT', true);
require_once __DIR__.'/../../../include/db.php';
require_once __DIR__.'/../../../include/header.php';

$token = $_GET['token'] ?? '';
$valid = false;

$session_error = $_SESSION['reset_error'] ?? '';
unset($_SESSION['reset_error']);

if ($token) {
    $query = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $query->execute([$token]);
    $reset = $query->fetch(PDO::FETCH_ASSOC);
    if ($reset) {
        $valid = true;
    }
}
?>

<div class="page-container">
    <div class="content-card">
        <?php if (!empty($session_error)): ?>
            <div class="status-badge error-badge" style="margin-bottom: 1rem;">
                <?= htmlspecialchars($session_error) ?>
            </div>
        <?php endif; ?>

        <?php if ($valid): ?>
            <div class="content-card-header">
                <h2>Set New Password</h2>
            </div>
            <div class="content-card-body">
                <form method="POST" action="process-reset.php">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label for="password">New Password:</label>
                        <input type="password" name="password" id="password" class="form-input" required>
                    </div>
                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="password_confirm">Confirm New Password:</label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-input" required>
                    </div>
                    <button type="submit" class="btn-action btn-primary" style="margin-top: 1.5rem;">Reset Password</button>
                </form>
            </div>
        <?php else: ?>
             <div class="content-card-header">
                <h2>Password Reset</h2>
            </div>
            <div class="content-card-body">
                <div class="status-badge error-badge">Invalid or expired reset link.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__.'/../../../include/footer.php'; ?>
