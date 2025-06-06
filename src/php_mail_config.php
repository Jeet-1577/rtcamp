<?php
// Mail configuration for Mailpit (both local development and GitHub Actions)

// Ensure PHP errors are logged
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/application_mail_errors.log');

// Detect environment
$is_cli = (php_sapi_name() === 'cli');
$is_linux = (stripos(PHP_OS, 'linux') !== false);
$is_github_actions = !empty(getenv('GITHUB_ACTIONS')) || !empty(getenv('CI'));

error_log("PHP Mail Config: OS=" . PHP_OS . ", SAPI=" . php_sapi_name() . ", CI=" . ($is_github_actions ? 'true' : 'false') . "\n", 3, __DIR__ . '/application_mail_errors.log');

// Configure for Mailpit (works for both local development and GitHub Actions)
ini_set('SMTP', 'localhost');
ini_set('smtp_port', '1025');
ini_set('sendmail_from', 'no-reply@example.com');

if ($is_linux || $is_github_actions) {
    // Linux/GitHub Actions environment - configure sendmail for Mailpit
    if ($is_cli) {
        // Try different sendmail paths that work with Mailpit
        $sendmail_paths = [
            '/usr/sbin/sendmail -t -i',
            '/usr/bin/sendmail -t -i',
            '/bin/sendmail -t -i',
            '/usr/sbin/msmtp -t',
            '/usr/bin/msmtp -t'
        ];
        
        $sendmail_set = false;
        foreach ($sendmail_paths as $path) {
            $exe_path = explode(' ', $path)[0];
            if (file_exists($exe_path)) {
                ini_set('sendmail_path', $path);
                error_log("PHP Mail: Using sendmail at: " . $path . "\n", 3, __DIR__ . '/application_mail_errors.log');
                $sendmail_set = true;
                break;
            }
        }
        
        if (!$sendmail_set) {
            // Fallback - use default sendmail command
            ini_set('sendmail_path', '/usr/sbin/sendmail -t -i');
            error_log("PHP Mail: Using fallback sendmail\n", 3, __DIR__ . '/application_mail_errors.log');
        }
    }
    
    error_log("PHP Mail: Configured for Linux/GitHub Actions with Mailpit (localhost:1025)\n", 3, __DIR__ . '/application_mail_errors.log');
} else {
    // Windows configuration - also use Mailpit
    if (file_exists('C:\sendmail\sendmail.exe')) {
        ini_set('sendmail_path', 'C:\sendmail\sendmail.exe -t');
        error_log("PHP Mail: Windows sendmail configured\n", 3, __DIR__ . '/application_mail_errors.log');
    }
    
    error_log("PHP Mail: Windows environment configured for Mailpit\n", 3, __DIR__ . '/application_mail_errors.log');
}

// Set additional mail configuration for better Mailpit compatibility
ini_set('auto_detect_line_endings', 'Off');
ini_set('mail.add_x_header', 'On');

// Log final configuration
$final_sendmail = ini_get('sendmail_path');
$final_smtp = ini_get('SMTP');
$final_port = ini_get('smtp_port');
error_log("PHP Mail: Final config - SMTP='" . $final_smtp . ":" . $final_port . "', sendmail_path='" . $final_sendmail . "'\n", 3, __DIR__ . '/application_mail_errors.log');

// Test Mailpit connectivity (optional)
if ($is_github_actions || $is_cli) {
    $mailpit_test = @fsockopen('localhost', 1025, $errno, $errstr, 5);
    if ($mailpit_test) {
        error_log("PHP Mail: Mailpit connection test SUCCESSFUL\n", 3, __DIR__ . '/application_mail_errors.log');
        fclose($mailpit_test);
    } else {
        error_log("PHP Mail: Mailpit connection test FAILED - $errstr ($errno)\n", 3, __DIR__ . '/application_mail_errors.log');
    }
}
