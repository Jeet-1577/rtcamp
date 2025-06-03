<?php
require_once __DIR__ . '/php_mail_config.php'; // Add this line
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
            $message = 'Failed to unsubscribe. Your email address was not found in our subscriber list, or an error occurred.';
        }
    } else {
        $message = 'Invalid email address provided for unsubscription.';
    }
} else {
    $message = 'Unsubscribe link is incomplete. Please use the link provided in the email.';
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
        a.button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Unsubscribe</h1>
    <div class="message <?php echo $success ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <a href="index.php" class="button">Back to Task Scheduler</a>
</body>
</html>
