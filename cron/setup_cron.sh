#!/bin/bash

# Get the absolute path of the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Create the cron job to run daily at 9 AM
(crontab -l 2>/dev/null; echo "0 9 * * * php $SCRIPT_DIR/membership_reminder.php >> $SCRIPT_DIR/cron.log 2>&1") | crontab -

echo "Cron job has been set up successfully!"
echo "The membership reminder script will run daily at 9 AM."
echo "Logs will be written to: $SCRIPT_DIR/cron.log"

chmod +x cron/setup_cron.sh
./cron/setup_cron.sh 