#!/bin/bash

# Script to automatically configure CRON job for Task Planner reminders.
# This script adds a CRON job that runs cron.php every minute for testing.

echo "Setting up Task Planner CRON job..."

# Get the absolute path to the directory containing this script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# Construct the full path to cron.php
CRON_PHP_PATH="$SCRIPT_DIR/cron.php"
# Attempt to find PHP executable
PHP_EXECUTABLE=$(command -v php)

# Check if cron.php exists
if [ ! -f "$CRON_PHP_PATH" ]; then
    echo "ERROR: cron.php not found at $CRON_PHP_PATH"
    exit 1
fi

# Check if PHP is available
if [ -z "$PHP_EXECUTABLE" ]; then
    echo "ERROR: PHP executable not found. Please ensure PHP is installed and in your PATH."
    exit 1
fi
echo "Using PHP executable at: $PHP_EXECUTABLE"

# Define the CRON job entry (runs every minute for testing)
CRON_JOB_COMMAND="$PHP_EXECUTABLE $CRON_PHP_PATH"
CRON_SCHEDULE="* * * * *" # Every minute for testing
FULL_CRON_JOB="$CRON_SCHEDULE $CRON_JOB_COMMAND"

# Check if crontab command is available
if ! command -v crontab &> /dev/null; then
    echo "ERROR: crontab command not found. Is cron daemon installed and running?"
    exit 1
fi

# Backup current crontab
crontab -l > crontab_backup.txt 2>/dev/null
echo "Current crontab backed up to crontab_backup.txt (if it existed)."

# Remove any old versions of this specific cron job to avoid duplicates
(crontab -l 2>/dev/null | grep -v -F "$CRON_PHP_PATH" ; echo "$FULL_CRON_JOB") | crontab -

if [ $? -eq 0 ]; then
    echo "CRON job successfully configured!"
    echo "Job entry: $FULL_CRON_JOB"
    echo "This will run '$CRON_PHP_PATH' every minute for testing."
else
    echo "ERROR: Failed to configure CRON job."
    echo "You might need appropriate permissions, or the cron daemon might not be running."
    exit 1
fi

echo ""
echo "Current CRON jobs for this user:"
crontab -l

echo ""
echo "Setup complete."
echo "To manually test the reminder script, run: $PHP_EXECUTABLE $CRON_PHP_PATH"
echo "Note: CRON job is set to run every minute for testing."
echo "Task reminders will be sent to all verified subscribers about pending tasks."
