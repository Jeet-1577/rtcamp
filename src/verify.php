<?php
require_once __DIR__ . '/php_mail_config.php'; // Add this line
require_once 'functions.php';

$message = '';
$success = false;

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = trim($_GET['email']);
    $code = trim($_GET['code']);

    if (!empty($email) && !empty($code)) {
        if (verifySubscription($email, $code)) {
            $message = 'Your email address has been successfully verified! You are now subscribed to task reminders.';
            $success = true;
        } else {
            $message = 'Verification failed. The link may be invalid, expired, or your email is already verified.';
        }
    } else {
        $message = 'Invalid verification parameters.';
    }
} else {
    $message = 'Verification link is incomplete. Please use the link provided in the email.';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Task Scheduler</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; text-align: center; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .message { padding: 20px; margin-bottom: 20px; border-radius: 5px; }
        .success { background-color: #e8f5e8; border: 1px solid #4caf50; color: #2e7d32; }
        .error { background-color: #ffeaea; border: 1px solid #f44336; color: #c62828; }
        a.button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Email Verification</h1>
    <div class="message <?php echo $success ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <a href="index.php" class="button">Back to Task Scheduler</a>
</body>
</html>