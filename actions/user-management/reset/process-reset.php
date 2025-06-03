<?php
define('APP_INIT', true);
require_once __DIR__.'/../../../include/db.php';
require_once __DIR__.'/../../../include/header.php';


$session_error = $_SESSION['reset_error'] ?? '';
unset($_SESSION['reset_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm) || $password !== $password_confirm) {
        $_SESSION['reset_error'] = "Passwords do not match or are empty.";
        header("Location: reset-password.php?token=" . urlencode($token));
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['reset_error'] = "Password must be at least 6 characters long.";
        header("Location: reset-password.php?token=" . urlencode($token));
        exit();
    }

    if (strlen($password) > 255) {
        $_SESSION['reset_error'] = "Password must be between 6 and 255 characters";
        header("Location: reset-password.php?token=" . urlencode($token));
        exit;
    }

    if ($token && $password) {
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
?>

<div class="page-container">
    <div class="content-card">
        <div class="content-card-header">
            <h2>Password Reset Status</h2>
        </div>
        <div class="content-card-body">
            <?php if (!empty($session_error)): ?>
                <div class="status-badge error-badge">
                    <?php echo htmlspecialchars($session_error); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="status-badge error-badge"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (isset($success)):
                $success = true;
            ?>
                <div class="status-badge success-badge">
                    Password has been reset. You can now <a href="../../../login.php">log in</a>.
                </div>
            <?php else: ?>
                 <div class="status-badge error-badge">An unexpected error occurred during password reset.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../../include/footer.php'; ?>
