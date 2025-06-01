<?php
require_once '../../../include/db.php';
require_once '../../../include/header.php';

$token = $_GET['token'] ?? '';
$valid = false;

if ($token) {
    $query = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $query->execute([$token]);
    $reset = $query->fetch(PDO::FETCH_ASSOC);
    if ($reset) {
        $valid = true;
    }
}
?>
<?php if ($valid): ?>
    <h2>Set New Password</h2>
    <form method="POST" action="process-reset.php">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
            <label for="password">New Password:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm New Password:</label>
            <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
<?php else: ?>
    <p>Invalid or expired reset link.</p>
<?php endif; ?>
<?php require_once '../../../include/footer.php'; ?>
