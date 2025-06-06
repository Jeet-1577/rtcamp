<?php
// Mail configuration for both local development and GitHub Actions test environment

// Ensure PHP errors are logged
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/application_mail_errors.log');

// Detect environment
$is_cli = (php_sapi_name() === 'cli');
$is_linux = (stripos(PHP_OS, 'linux') !== false);
$is_github_actions = !empty(getenv('GITHUB_ACTIONS')) || !empty(getenv('CI'));

error_log("PHP Mail Config: OS=" . PHP_OS . ", SAPI=" . php_sapi_name() . "\n", 3, __DIR__ . '/application_mail_errors.log');

// Configure for GitHub Actions test environment (uses Mailpit)
if ($is_github_actions || $is_linux) {
    // GitHub Actions / Linux environment - configure for Mailpit
    ini_set('SMTP', 'localhost');
    ini_set('smtp_port', '1025');
    ini_set('sendmail_from', 'no-reply@example.com');
    
    // For Linux CLI, try to use sendmail that works with Mailpit
    if ($is_cli) {
        // In Docker/GitHub Actions, sendmail should be configured to route to Mailpit
        $sendmail_paths = [
            '/usr/sbin/sendmail -t -i',
            '/usr/bin/sendmail -t -i',
            '/bin/sendmail -t -i'
        ];
        
        foreach ($sendmail_paths as $path) {
            $exe_path = explode(' ', $path)[0];
            if (file_exists($exe_path)) {
                ini_set('sendmail_path', $path);
                error_log("PHP Mail: Using sendmail at: " . $path . "\n", 3, __DIR__ . '/application_mail_errors.log');
                break;
            }
        }
    }
    
    error_log("PHP Mail: Configured for Linux/GitHub Actions with Mailpit (localhost:1025)\n", 3, __DIR__ . '/application_mail_errors.log');
} else {
    // Windows configuration (local development)
    error_log("PHP Mail: Windows environment detected\n", 3, __DIR__ . '/application_mail_errors.log');
}

// Log final configuration
$final_sendmail = ini_get('sendmail_path');
$final_smtp = ini_get('SMTP');
$final_port = ini_get('smtp_port');
error_log("PHP Mail: Final config - SMTP='" . $final_smtp . ":" . $final_port . "', sendmail_path='" . $final_sendmail . "'\n", 3, __DIR__ . '/application_mail_errors.log');
