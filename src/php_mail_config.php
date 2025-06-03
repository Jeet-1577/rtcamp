<?php
// Mail configuration for Ubuntu/Linux environment with ssmtp

// Ensure PHP errors are logged
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/application_mail_errors.log');

// Detect environment
$is_cli = (php_sapi_name() === 'cli');
$is_linux = (stripos(PHP_OS, 'linux') !== false);

error_log("PHP Mail Config: OS=" . PHP_OS . ", SAPI=" . php_sapi_name() . "\n", 3, __DIR__ . '/application_mail_errors.log');

if ($is_linux) {
    // Ubuntu/Linux configuration with ssmtp
    if ($is_cli) {
        // Force use of ssmtp since we installed it
        $sendmail_path = '/usr/sbin/ssmtp -t';
        
        if (file_exists('/usr/sbin/ssmtp')) {
            ini_set('sendmail_path', $sendmail_path);
            error_log("PHP Mail: Set sendmail_path to: " . $sendmail_path . "\n", 3, __DIR__ . '/application_mail_errors.log');
        } else {
            error_log("PHP Mail: ERROR - ssmtp not found at /usr/sbin/ssmtp\n", 3, __DIR__ . '/application_mail_errors.log');
        }
    }
} else {
    // Windows configuration (fallback)
    error_log("PHP Mail: Windows environment detected\n", 3, __DIR__ . '/application_mail_errors.log');
}

// Log final configuration
$final_sendmail = ini_get('sendmail_path');
error_log("PHP Mail: Final sendmail_path='" . $final_sendmail . "'\n", 3, __DIR__ . '/application_mail_errors.log');
