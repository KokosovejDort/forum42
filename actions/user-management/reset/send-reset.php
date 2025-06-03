<?php
define('APP_INIT', true);
require_once __DIR__.'/../../../include/db.php';
require_once __DIR__.'/../../../include/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $query = $db->prepare("SELECT user_id FROM forum_users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    $message = "If the email exists, a reset link has been sent.";

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', time() + 1800);

        $db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['user_id']]);
        $query = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $query->execute([$user['user_id'], $token, $expires_at]);

        $reset_link = "https://eso.vse.cz/~dudt05/semestralka/actions/user-management/reset/reset-password.php?token=$token";
        $subject = "Password Reset Request";
        $body = "Click the following link to reset your password: $reset_link\nThis link expires in 30 minutes.";
        mail($email, $subject, $body);

    }
} else {
    $error_message = "This page is for processing password reset requests and should not be accessed directly. Please use the Forgot Password form.";
}
?>
<div class="page-container">
    <div class="content-card">
        <div class="content-card-header">
            <h2>Password Reset Request</h2>
        </div>
        <div class="content-card-body">
            <?php if (isset($message)): ?>
                <div class="status-badge success-badge">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php elseif (isset($error_message)): ?>
                 <div class="status-badge error-badge">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__.'/../../../include/footer.php'; ?>