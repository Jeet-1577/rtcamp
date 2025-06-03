<?php
require_once __DIR__ . '/php_mail_config.php'; // Add this line
require_once 'functions.php';

// Add debug logging to confirm cron.php is running
$debug_log = __DIR__ . '/cron_debug.log';
$timestamp = date('Y-m-d H:i:s');
file_put_contents($debug_log, "[$timestamp] CRON job started\n", FILE_APPEND | LOCK_EX);

// Send task reminders to all subscribers.
sendTaskReminders();

file_put_contents($debug_log, "[$timestamp] CRON job completed\n", FILE_APPEND | LOCK_EX);