<?php
require_once 'functions.php';

// Handle AJAX email subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_email'])) {
    header('Content-Type: application/json');
    
    $email = trim($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("DEBUG: Processing AJAX email subscription for: " . $email);
        $result = subscribeEmail($email);
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Verification email sent successfully! Please check your inbox and click the verification link.'
            ]);
            error_log("DEBUG: AJAX Subscription successful for: " . $email);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to send verification email. You may already be subscribed or have a pending verification.'
            ]);
            error_log("DEBUG: AJAX Subscription failed for: " . $email);
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Please enter a valid email address.'
        ]);
        error_log("DEBUG: AJAX Invalid email format: " . $email);
    }
    exit;
}

// Handle form submissions (non-AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['task-name']) && !empty(trim($_POST['task-name']))) {
		$result = addTask(trim($_POST['task-name']));
		$message = $result ? 'Task added successfully!' : 'Failed to add task or task already exists.';
	}
	
	if (isset($_POST['task_action'])) {
		if ($_POST['task_action'] === 'toggle' && isset($_POST['task_id'])) {
			$is_completed = isset($_POST['is_completed']) && $_POST['is_completed'] === '1';
			markTaskAsCompleted($_POST['task_id'], $is_completed);
		}
		
		if ($_POST['task_action'] === 'delete' && isset($_POST['task_id'])) {
			deleteTask($_POST['task_id']);
		}
	}
	
	// Fallback for non-AJAX email submission
	if (isset($_POST['email']) && !isset($_POST['ajax_email']) && !empty(trim($_POST['email']))) {
		$email = trim($_POST['email']);
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			error_log("DEBUG: Processing fallback email subscription for: " . $email);
			$result = subscribeEmail($email);
			if ($result) {
				$email_message = 'Verification email sent successfully! Please check your inbox and click the verification link.';
				error_log("DEBUG: Fallback subscription successful for: " . $email);
			} else {
				$email_message = 'Failed to send verification email. You may already be subscribed or have a pending verification.';
				error_log("DEBUG: Fallback subscription failed for: " . $email);
			}
		} else {
			$email_message = 'Please enter a valid email address.';
			error_log("DEBUG: Fallback invalid email format: " . $email);
		}
	}
}

$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Task Scheduler</title>
	<style>
		body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
		.container { margin-bottom: 30px; }
		.task-item { padding: 10px; border: 1px solid #ddd; margin: 5px 0; display: flex; align-items: center; gap: 10px; }
		.task-item.completed { background-color: #f0f8f0; text-decoration: line-through; }
		.delete-task { background: #ff4444; color: white; border: none; padding: 5px 10px; cursor: pointer; }
		button { padding: 10px 15px; margin: 5px; cursor: pointer; }
		input[type="text"], input[type="email"] { padding: 10px; width: 300px; }
		.message { padding: 10px; margin: 10px 0; background: #e8f5e8; border: 1px solid #4caf50; position: relative; }
		.error-message { padding: 10px; margin: 10px 0; background: #ffeaea; border: 1px solid #f44336; color: #c62828; position: relative; }
		.loading { display: none; padding: 10px; margin: 10px 0; background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
		#submit-email:disabled { background: #ccc; cursor: not-allowed; }
		.close-btn { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 18px; cursor: pointer; color: #666; padding: 0; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; }
		.close-btn:hover { color: #000; background: rgba(0,0,0,0.1); border-radius: 3px; }
	</style>
</head>

<body>
	<h1>Task Scheduler</h1>
	
	<?php if (isset($message)): ?>
		<div class="message" id="task-message">
			<?php echo htmlspecialchars($message); ?>
			<button type="button" class="close-btn" onclick="closeMessage('task-message')" title="Close message">&times;</button>
		</div>
	<?php endif; ?>

	<div class="container">
		<h2>Add New Task</h2>
		<!-- Add Task Form -->
		<form method="POST" action="">
			<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
			<button type="submit" id="add-task">Add Task</button>
		</form>
	</div>

	<div class="container">
		<h2>Tasks List</h2>
		<!-- Tasks List -->
		<ul class="tasks-list" id="tasks-list">
			<?php if (empty($tasks)): ?>
				<li>No tasks available. Add a task above!</li>
			<?php else: ?>
				<?php foreach ($tasks as $task): ?>
					<li class="task-item <?php echo $task['completed'] ? 'completed' : ''; ?>">
						<form method="POST" action="" style="display: inline;">
							<input type="hidden" name="task_action" value="toggle">
							<input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
							<input type="hidden" name="is_completed" value="<?php echo $task['completed'] ? '0' : '1'; ?>">
							<input type="checkbox" class="task-status" 
								   <?php echo $task['completed'] ? 'checked' : ''; ?> 
								   onchange="this.form.submit()"
								   title="Mark as <?php echo $task['completed'] ? 'incomplete' : 'complete'; ?>">
						</form>
						
						<span><?php echo htmlspecialchars($task['name']); ?></span>
						
						<form method="POST" action="" style="display: inline; margin-left: auto;">
							<input type="hidden" name="task_action" value="delete">
							<input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
							<button type="submit" class="delete-task" onclick="return confirm('Are you sure you want to delete this task?')">Delete</button>
						</form>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</div>

	<div class="container">
		<h2>Email Subscription</h2>
		<div id="email-messages">
			<?php if (isset($email_message)): ?>
				<div class="message"><?php echo htmlspecialchars($email_message); ?></div>
			<?php endif; ?>
		</div>
		
		<!-- Subscription Form with AJAX support and fallback -->
		<form id="email-form" method="POST" action="">
			<input type="email" name="email" id="email-input" placeholder="Enter your email" required />
			<button type="submit" id="submit-email">Subscribe</button>
		</form>
		
		<div id="loading-message" class="loading">
			Sending verification email, please wait...
		</div>
	</div>

	<script>
		// Function to close messages
		function closeMessage(messageId) {
			const messageElement = document.getElementById(messageId);
			if (messageElement) {
				messageElement.style.display = 'none';
			}
		}
		
		// AJAX email subscription with fallback
		document.getElementById('email-form').addEventListener('submit', function(e) {
			e.preventDefault();
			
			const emailInput = document.getElementById('email-input');
			const submitBtn = document.getElementById('submit-email');
			const loadingMsg = document.getElementById('loading-message');
			const messagesDiv = document.getElementById('email-messages');
			const email = emailInput.value.trim();
			
			if (!email) {
				showMessage('Please enter an email address.', false);
				return;
			}
			
			// Show loading state
			submitBtn.disabled = true;
			submitBtn.textContent = 'Sending...';
			loadingMsg.style.display = 'block';
			messagesDiv.innerHTML = '';
			
			// Create FormData for AJAX request
			const formData = new FormData();
			formData.append('email', email);
			formData.append('ajax_email', '1');
			
			// AJAX request
			fetch('', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				showMessage(data.message, data.success);
				if (data.success) {
					emailInput.value = '';
				}
			})
			.catch(error => {
				console.error('AJAX Error:', error);
				// Fallback: submit form normally
				showMessage('Connection issue. Submitting form normally...', false);
				setTimeout(() => {
					// Add hidden field to indicate non-AJAX submission
					const hiddenField = document.createElement('input');
					hiddenField.type = 'hidden';
					hiddenField.name = 'fallback_submit';
					hiddenField.value = '1';
					this.appendChild(hiddenField);
					this.submit();
				}, 1000);
			})
			.finally(() => {
				// Reset UI state
				submitBtn.disabled = false;
				submitBtn.textContent = 'Subscribe';
				loadingMsg.style.display = 'none';
			});
		});
		
		function showMessage(message, isSuccess) {
			const messagesDiv = document.getElementById('email-messages');
			const messageClass = isSuccess ? 'message' : 'error-message';
			const messageId = 'email-message-' + Date.now();
			messagesDiv.innerHTML = `<div class="${messageClass}" id="${messageId}">${message}<button type="button" class="close-btn" onclick="closeMessage('${messageId}')" title="Close message">&times;</button></div>`;
		}
		
		// Auto-close messages after 5 seconds (optional)
		document.addEventListener('DOMContentLoaded', function() {
			const messages = document.querySelectorAll('.message, .error-message');
			messages.forEach(function(message) {
				if (message.id) {
					setTimeout(function() {
						if (message.style.display !== 'none') {
							message.style.opacity = '0.5';
						}
					}, 5000);
				}
			});
		});
	</script>

</body>

</html>
