<?php

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask( string $task_name ): bool {
	$file  = __DIR__ . '/tasks.txt';
	
	// Check if task already exists to prevent duplicates
	$existing_tasks = getAllTasks();
	foreach ($existing_tasks as $task) {
		if (trim($task['name']) === trim($task_name)) {
			return false; // Duplicate task
		}
	}
	
	// Generate unique ID
	$task_id = uniqid();
	$task_data = $task_id . '|' . $task_name . '|0' . PHP_EOL; // 0 = not completed
	
	return file_put_contents($file, $task_data, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks(): array {
	$file = __DIR__ . '/tasks.txt';
	
	if (!file_exists($file)) {
		return [];
	}
	
	$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$tasks = [];
	
	foreach ($lines as $line) {
		$parts = explode('|', $line);
		if (count($parts) === 3) {
			$tasks[] = [
				'id' => $parts[0],
				'name' => $parts[1],
				'completed' => (bool)$parts[2]
			];
		}
	}
	
	return $tasks;
}

/**
 * Marks a task as completed or uncompleted
 * 
 * @param string  $task_id The ID of the task to mark.
 * @param bool $is_completed True to mark as completed, false to mark as uncompleted.
 * @return bool True on success, false on failure
 */
function markTaskAsCompleted( string $task_id, bool $is_completed ): bool {
	$file  = __DIR__ . '/tasks.txt';
	
	$tasks = getAllTasks();
	$updated = false;
	
	foreach ($tasks as &$task) {
		if ($task['id'] === $task_id) {
			$task['completed'] = $is_completed;
			$updated = true;
			break;
		}
	}
	
	if (!$updated) {
		return false;
	}
	
	// Rewrite the file
	$content = '';
	foreach ($tasks as $task) {
		$content .= $task['id'] . '|' . $task['name'] . '|' . ($task['completed'] ? '1' : '0') . PHP_EOL;
	}
	
	return file_put_contents($file, $content, LOCK_EX) !== false;
}

/**
 * Deletes a task from the task list
 * 
 * @param string $task_id The ID of the task to delete.
 * @return bool True on success, false on failure.
 */
function deleteTask( string $task_id ): bool {
	$file  = __DIR__ . '/tasks.txt';
	
	$tasks = getAllTasks();
	$filtered_tasks = array_filter($tasks, function($task) use ($task_id) {
		return $task['id'] !== $task_id;
	});
	
	if (count($filtered_tasks) === count($tasks)) {
		return false; // Task not found
	}
	
	// Rewrite the file
	$content = '';
	foreach ($filtered_tasks as $task) {
		$content .= $task['id'] . '|' . $task['name'] . '|' . ($task['completed'] ? '1' : '0') . PHP_EOL;
	}
	
	return file_put_contents($file, $content, LOCK_EX) !== false;
}

/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode(): string {
	return sprintf('%06d', mt_rand(100000, 999999));
}

/**
 * Simulates email sending for development environment
 * 
 * @param string $to Email recipient
 * @param string $subject Email subject
 * @param string $message Email content
 * @param string $headers Email headers
 * @return bool Always returns true for development
 */
function simulateEmailForDevelopment($to, $subject, $message, $headers) {
	// Create a log file to simulate email sending
	$log_file = __DIR__ . '/email_log.txt';
	$timestamp = date('Y-m-d H:i:s');
	
	$email_log = "=== EMAIL SENT ===\n";
	$email_log .= "Timestamp: {$timestamp}\n";
	$email_log .= "To: {$to}\n";
	$email_log .= "Subject: {$subject}\n";
	$email_log .= "Headers: {$headers}\n";
	$email_log .= "Message:\n{$message}\n";
	$email_log .= "==================\n\n";
	
	file_put_contents($log_file, $email_log, FILE_APPEND | LOCK_EX);
	return true;
}

/**
 * Sends email with fallback for development environment
 * 
 * @param string $to Email recipient
 * @param string $subject Email subject
 * @param string $message Email content
 * @param string $headers Email headers
 * @return bool True if email sent successfully or simulated
 */
function sendEmail($to, $subject, $message, $headers) {
	// Try to send real email first
	$result = @mail($to, $subject, $message, $headers);
	
	// If mail fails (like in development), simulate it
	if (!$result) {
		return simulateEmailForDevelopment($to, $subject, $message, $headers);
	}
	
	return $result;
}

/**
 * Subscribe an email address to task notifications.
 *
 * Generates a verification code, stores the pending subscription,
 * and sends a verification email to the subscriber.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */
function subscribeEmail( string $email ): bool {
	$file = __DIR__ . '/pending_subscriptions.txt';
	
	// Check if email is already subscribed
	$subscribers_file = __DIR__ . '/subscribers.txt';
	if (file_exists($subscribers_file)) {
		$subscribers = file($subscribers_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (in_array($email, $subscribers)) {
			return false; // Already subscribed
		}
	}
	
	// Check if email is already pending verification
	if (file_exists($file)) {
		$pending = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($pending as $line) {
			$parts = explode('|', $line);
			if (count($parts) >= 2 && $parts[0] === $email) {
				return false; // Already pending verification
			}
		}
	}
	
	// Generate verification code
	$code = generateVerificationCode();
	
	// Store pending subscription
	$pending_data = $email . '|' . $code . '|' . time() . PHP_EOL;
	if (file_put_contents($file, $pending_data, FILE_APPEND | LOCK_EX) === false) {
		return false;
	}
	
	// Send verification email
	$verification_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/verify.php?email=" . urlencode($email) . "&code=" . $code;
	
	$subject = 'Verify subscription to Task Planner';
	$message = '<p>Click the link below to verify your subscription to Task Planner:</p>' . PHP_EOL;
	$message .= '<p><a id="verification-link" href="' . $verification_link . '">Verify Subscription</a></p>';
	
	$headers = "From: no-reply@example.com" . PHP_EOL;
	$headers .= "Content-Type: text/html; charset=UTF-8" . PHP_EOL;
	
	return sendEmail($email, $subject, $message, $headers);
}

/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription( string $email, string $code ): bool {
	$pending_file     = __DIR__ . '/pending_subscriptions.txt';
	$subscribers_file = __DIR__ . '/subscribers.txt';
	
	if (!file_exists($pending_file)) {
		return false;
	}
	
	$pending_lines = file($pending_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$found = false;
	$updated_pending = [];
	
	foreach ($pending_lines as $line) {
		$parts = explode('|', $line);
		if (count($parts) >= 3 && $parts[0] === $email && $parts[1] === $code) {
			$found = true;
			// Don't add this line to updated_pending (remove it)
		} else {
			$updated_pending[] = $line;
		}
	}
	
	if (!$found) {
		return false;
	}
	
	// Update pending subscriptions file
	file_put_contents($pending_file, implode(PHP_EOL, $updated_pending) . (empty($updated_pending) ? '' : PHP_EOL), LOCK_EX);
	
	// Add to subscribers
	$subscriber_data = $email . PHP_EOL;
	return file_put_contents($subscribers_file, $subscriber_data, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail( string $email ): bool {
	$subscribers_file = __DIR__ . '/subscribers.txt';
	
	if (!file_exists($subscribers_file)) {
		return false;
	}
	
	$subscribers = file($subscribers_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$updated_subscribers = array_filter($subscribers, function($subscriber) use ($email) {
		return trim($subscriber) !== $email;
	});
	
	if (count($updated_subscribers) === count($subscribers)) {
		return false; // Email not found
	}
	
	return file_put_contents($subscribers_file, implode(PHP_EOL, $updated_subscribers) . (empty($updated_subscribers) ? '' : PHP_EOL), LOCK_EX) !== false;
}

/**
 * Sends task reminders to all subscribers
 * Internally calls  sendTaskEmail() for each subscriber
 */
function sendTaskReminders(): void {
	$subscribers_file = __DIR__ . '/subscribers.txt';
	
	if (!file_exists($subscribers_file)) {
		return;
	}
	
	$subscribers = file($subscribers_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$pending_tasks = array_filter(getAllTasks(), function($task) {
		return !$task['completed'];
	});
	
	if (empty($pending_tasks)) {
		return; // No pending tasks to send
	}
	
	foreach ($subscribers as $email) {
		$email = trim($email);
		if (!empty($email)) {
			sendTaskEmail($email, $pending_tasks);
		}
	}
}

/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail( string $email, array $pending_tasks ): bool {
	$subject = 'Task Planner - Pending Tasks Reminder';
	
	$unsubscribe_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/unsubscribe.php?email=" . urlencode($email);
	
	$message = '<h2>Pending Tasks Reminder</h2>' . PHP_EOL;
	$message .= '<p>Here are the current pending tasks:</p>' . PHP_EOL;
	$message .= '<ul>' . PHP_EOL;
	
	foreach ($pending_tasks as $task) {
		$message .= '<li>' . htmlspecialchars($task['name']) . '</li>' . PHP_EOL;
	}
	
	$message .= '</ul>' . PHP_EOL;
	$message .= '<p><a id="unsubscribe-link" href="' . $unsubscribe_link . '">Unsubscribe from notifications</a></p>';
	
	$headers = "From: no-reply@example.com" . PHP_EOL;
	$headers .= "Content-Type: text/html; charset=UTF-8" . PHP_EOL;
	
	return sendEmail($email, $subject, $message, $headers);
}
