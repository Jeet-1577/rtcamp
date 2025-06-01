<?php
require_once 'functions.php';

// Handle form submissions
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
	
	if (isset($_POST['email']) && !empty(trim($_POST['email']))) {
		$email = trim($_POST['email']);
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$result = subscribeEmail($email);
			if ($result) {
				$email_message = 'Verification email sent! Please check your inbox and click the verification link.';
			} else {
				$email_message = 'Failed to send verification email. You may already be subscribed or have a pending verification.';
			}
		} else {
			$email_message = 'Please enter a valid email address.';
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
		.message { padding: 10px; margin: 10px 0; background: #e8f5e8; border: 1px solid #4caf50; }
	</style>
</head>

<body>
	<h1>Task Scheduler</h1>
	
	<?php if (isset($message)): ?>
		<div class="message"><?php echo htmlspecialchars($message); ?></div>
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
							<input type="checkbox" class="task-status" <?php echo $task['completed'] ? 'checked' : ''; ?> 
								   onchange="this.form.submit()">
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
		<?php if (isset($email_message)): ?>
			<div class="message"><?php echo htmlspecialchars($email_message); ?></div>
		<?php endif; ?>
		
		<!-- Subscription Form -->
		<form method="POST" action="">
			<input type="email" name="email" placeholder="Enter your email" required />
			<button type="submit" id="submit-email">Subscribe</button>
		</form>
	</div>

</body>

</html>
