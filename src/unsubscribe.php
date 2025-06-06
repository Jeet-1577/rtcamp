<?php
require_once 'functions.php';

$message = '';
$success = false;

if (isset($_GET['email'])) {
    $email = trim($_GET['email']);

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (unsubscribeEmail($email)) {
            $message = 'You have been successfully unsubscribed from task reminders.';
            $success = true;
        } else {
            $message = 'Unsubscribe failed. You may not be subscribed or the email was not found.';
        }
    } else {
        $message = 'Invalid email address provided.';
    }
} else {
    $message = 'Invalid unsubscribe link. No email address provided.';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - Task Scheduler</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; text-align: center; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .message { padding: 20px; margin-bottom: 20px; border-radius: 5px; }
        .success { background-color: #e8f5e8; border: 1px solid #4caf50; color: #2e7d32; }
        .error { background-color: #ffeaea; border: 1px solid #f44336; color: #c62828; }
        a.button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        a.button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <h1>Unsubscribe from Task Scheduler</h1>
    
    <div class="message <?php echo $success ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    
    <?php if ($success): ?>
        <p>You will no longer receive task reminder emails.</p>
        <p>If you change your mind, you can always subscribe again on our main page.</p>
    <?php endif; ?>
    
    <a href="index.php" class="button">Back to Task Scheduler</a>
</body>
</html>
