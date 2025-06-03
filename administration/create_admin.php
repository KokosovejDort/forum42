<?php
define('APP_INIT', true);
require_once __DIR__.'/../include/db.php';

$security_password = "2y$10srr7z7zSpwWtYfwBSAUd8ude5fP.Z7MC";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['security_password']) || $_POST['security_password'] !== $security_password) {
        $message = "Incorrect security password. Access denied.";
    } 
    else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $message = "All fields are required.";
        }
        else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = $db->prepare("SELECT user_id FROM forum_users WHERE username = ? OR email = ?");
            $query->execute([$username, $email]);
            if ($query->rowCount() > 0) {
                $message = "Username or email already in use.";
            }
            else {
                $query = $db->prepare("
                    INSERT INTO forum_users (username, email, password, admin) 
                    VALUES (?, ?, ?, 1)
                ");
                try {
                    $query->execute([$username, $email, $hashed_password]);
                    $message = "Admin user created successfully! Username: " . htmlspecialchars($username);
                } catch (PDOException $e) {
                    $message = "Error creating admin user: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin User</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 500px; margin: 0 auto; }
        .message { padding: 10px; margin-bottom: 15px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"], input[type="password"] { 
            width: 100%; 
            padding: 8px;
            box-sizing: border-box;
        }
        button { 
            padding: 10px 15px; 
            background-color: #007bff; 
            color: white; 
            border: none; 
            cursor: pointer; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Admin User</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, "successfully") !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="security_password">Security Password:</label>
                <input type="password" id="security_password" name="security_password" required>
                <small>Enter the special password to access this form</small>
            </div>
            
            <div class="form-group">
                <label for="username">Admin Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Admin Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Admin Password:</label>
                <input type="password" id="password" name="password" required>
                <small>This will be properly hashed before storage</small>
            </div>
            
            <button type="submit">Create Admin User</button>
        </form>
    </div>
</body>
</html>
