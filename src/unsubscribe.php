<?php
require_once 'functions.php';

$message = '';
$success = false;

if (isset($_GET['email'])) {
	$email = $_GET['email'];
	
	if (unsubscribeEmail($email)) {
		$message = 'You have been successfully unsubscribed from task reminders.';
		$success = true;
	} else {
		$message = 'Failed to unsubscribe. Email address may not be found in our subscriber list.';
	}
} else {
	$message = 'Invalid unsubscribe link.';
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Unsubscribe - Task Scheduler</title>
	<style>
		body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; text-align: center; }
		.message { padding: 20px; margin: 20px 0; border-radius: 5px; }
		.success { background-color: #e8f5e8; border: 1px solid #4caf50; color: #2e7d32; }
		.error { background-color: #ffeaea; border: 1px solid #f44336; color: #c62828; }
		.btn { padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 3px; display: inline-block; margin-top: 20px; }
	</style>
</head>
<body>
	<h1>Unsubscribe</h1>
	<div class="message <?php echo $success ? 'success' : 'error'; ?>">
		<?php echo htmlspecialchars($message); ?>
	</div>
	<a href="index.php" class="btn">Back to Task Scheduler</a>
</body>
</html>
