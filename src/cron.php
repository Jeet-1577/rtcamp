<?php

require_once 'functions.php';

// Include mail configuration
require_once 'php_mail_config.php';

// Set timezone to UTC for consistent operation across environments
date_default_timezone_set('UTC');

// Set up proper environment for CLI execution
if (php_sapi_name() === 'cli') {
    // Set $_SERVER variables for CLI environment
    $_SERVER['HTTP_HOST'] = 'localhost:8000';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

// Log cron job execution with proper timezone
$log_file = __DIR__ . '/cron_debug.log';
$timestamp = date('Y-m-d H:i:s');
file_put_contents($log_file, "[{$timestamp}] HOURLY CRON job started\n", FILE_APPEND | LOCK_EX);

// Send task reminders to all verified subscribers
// This function:
// 1. Reads verified subscribers from subscribers.txt
// 2. Gets all pending (incomplete) tasks
// 3. Sends HTML email reminders with unsubscribe links
// 4. Only sends if there are pending tasks
sendTaskReminders();

file_put_contents($log_file, "[{$timestamp}] HOURLY CRON job completed\n", FILE_APPEND | LOCK_EX);