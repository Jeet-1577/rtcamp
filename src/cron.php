<?php

require_once 'functions.php';

// Include mail configuration
require_once 'php_mail_config.php';

// Set up proper environment for CLI execution
if (php_sapi_name() === 'cli') {
    // Set $_SERVER variables for CLI environment
    $_SERVER['HTTP_HOST'] = 'localhost:8000';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

// Log cron job execution
$log_file = __DIR__ . '/cron_debug.log';
$timestamp = date('Y-m-d H:i:s');
file_put_contents($log_file, "[{$timestamp}] CRON job started\n", FILE_APPEND | LOCK_EX);

// Send task reminders to all subscribers.
sendTaskReminders();

file_put_contents($log_file, "[{$timestamp}] CRON job completed\n", FILE_APPEND | LOCK_EX);