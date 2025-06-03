<?php 
define('APP_INIT', true);
require_once __DIR__.'/../../../include/header.php'; ?>

<div class="page-container">
    <div class="content-card">
        <div class="content-card-header">
            <h2>Forgot Password</h2>
        </div>
        <div class="content-card-body">
            <form method="POST" action="send-reset.php">
                <div class="form-group">
                    <label for="email">Enter your email address:</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <button type="submit" class="btn-action btn-primary" style="margin-top: 1rem;">Send Reset Link</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../../../include/footer.php'; ?>