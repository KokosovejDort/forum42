<?php
require_once '../../../include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($token && $password && $password == $password_confirm) {
        $query = $db->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $query->execute([$token]);
        $reset = $query->fetch(PDO::FETCH_ASSOC);

        if ($reset) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $query = $db->prepare("UPDATE forum_users SET password = ? WHERE user_id = ?");
            $query->execute([$hashed, $reset['user_id']]);
            $db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$reset['user_id']]);
            $success = true;
        }
    }
    else {
        $error = "Passwords do not match.";
    }
}
require_once '../../../include/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php elseif (!empty($success)): ?>
    <div class="alert alert-success">
        Password has been reset. You can now <a href="../../../login.php">log in</a>.
    </div>
<?php else: ?>
    <div class="alert alert-danger">Invalid or expired token.</div>
<?php endif; ?>
<?php require_once '../../../include/footer.php'; ?>
