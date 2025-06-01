<?php require_once '../../../include/header.php'; ?>
<h2>Forgot Password</h2>
<form method="POST" action="send-reset.php">
    <div class="form-group">
        <label for="email">Enter your email address:</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Send Reset Link</button>
</form>
<?php require_once '../../../include/footer.php'; ?>