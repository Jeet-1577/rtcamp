#!/bin/bash

# Get the current directory
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Create the cron job entry
CRON_JOB="0 * * * * /usr/bin/php $DIR/cron.php"

# Add the cron job to the current user's crontab
(crontab -l 2>/dev/null | grep -v "$DIR/cron.php"; echo "$CRON_JOB") | crontab -

echo "CRON job has been set up successfully!"
echo "The task reminder system will run every hour."
echo "Cron job: $CRON_JOB"
