#!/bin/bash

# Script to automatically configure CRON job for Task Planner reminders.
# This script adds a CRON job that runs cron.php every 1 hour as per README requirements.

echo "Setting up Task Planner CRON job (every hour)..."

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

# Define the CRON job entry (runs every hour as per README requirements)
CRON_JOB_COMMAND="$PHP_EXECUTABLE $CRON_PHP_PATH"
CRON_SCHEDULE="0 * * * *" # Every hour (at minute 0 of every hour)
FULL_CRON_JOB="$CRON_SCHEDULE $CRON_JOB_COMMAND"

# Check if crontab command is available
if ! command -v crontab &> /dev/null; then
    echo "ERROR: crontab command not found. Is cron daemon installed and running?"
    exit 1
fi

# Backup current crontab
crontab -l > "crontab_backup_$(date +%Y%m%d_%H%M%S).txt" 2>/dev/null
echo "Current crontab backed up."

# Remove any old versions of this specific cron job to avoid duplicates
(crontab -l 2>/dev/null | grep -v -F "$CRON_PHP_PATH" ; echo "$FULL_CRON_JOB") | crontab -

if [ $? -eq 0 ]; then
    echo "SUCCESS: CRON job successfully configured!"
    echo "Job entry: $FULL_CRON_JOB"
    echo "This will run '$CRON_PHP_PATH' every hour (at the start of each hour)."
    echo "Next execution: $(date -d '+1 hour' +'%Y-%m-%d %H:00:00' 2>/dev/null || date -v+1H +'%Y-%m-%d %H:00:00' 2>/dev/null || echo 'Next hour')"
else
    echo "ERROR: Failed to configure CRON job."
    exit 1
fi

echo ""
echo "Current CRON jobs for this user:"
crontab -l

echo ""
echo "Checking if cron service is running..."
if command -v systemctl &> /dev/null; then
    systemctl is-active cron >/dev/null 2>&1 && echo "Cron service is running" || echo "Cron service may not be running"
elif command -v service &> /dev/null; then
    service cron status >/dev/null 2>&1 && echo "Cron service is running" || echo "Cron service may not be running"
else
    echo "Cannot check cron service status. Please ensure cron is running."
fi

echo ""
echo "Setup complete."
echo "To manually test the reminder script, run: $PHP_EXECUTABLE $CRON_PHP_PATH"
echo "CRON job is set to run every hour as per README requirements."
echo "Check logs with: tail -f $SCRIPT_DIR/cron_debug.log"
echo ""
echo "Note: The job will run at the start of every hour (XX:00:00)"
echo "If you want to test immediately, run the manual test command above."
