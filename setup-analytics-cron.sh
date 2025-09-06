#!/bin/bash

# Setup script for Simple Model Analytics Extension
# This script sets up a cron job to extend model_profiles.json with analytics every 30 minutes

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
TRACKER_SCRIPT="$SCRIPT_DIR/extend-model-analytics.php"

echo "Setting up Simple Model Analytics Extension..."

# Check if the tracker script exists
if [ ! -f "$TRACKER_SCRIPT" ]; then
    echo "Error: Analytics script not found at $TRACKER_SCRIPT"
    exit 1
fi

# Make sure PHP is available
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH"
    exit 1
fi

# Test the tracker script
echo "Testing tracker script..."
php "$TRACKER_SCRIPT"
if [ $? -ne 0 ]; then
    echo "Error: Tracker script failed to run"
    exit 1
fi

# Create the cron job entry
CRON_ENTRY="*/30 * * * * cd $SCRIPT_DIR && /usr/bin/php extend-model-analytics.php >> /tmp/analytics.log 2>&1"

# Add to crontab (this will append to existing crontab)
echo "Adding cron job to run every 30 minutes..."
(crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -

echo ""
echo "✅ Simple Analytics Extension setup complete!"
echo ""
echo "The system will now:"
echo "  • Extend model_profiles.json with analytics data every 30 minutes"
echo "  • Track viewer history, peak viewers, and trends"
echo "  • Calculate consistency scores and performance metrics"
echo "  • Keep 30 days of detailed history per model"
echo ""
echo "Cron job added: $CRON_ENTRY"
echo ""
echo "To view cron jobs: crontab -l"
echo "To remove this cron job: crontab -e (then delete the analytics line)"
echo "To check logs: tail -f /tmp/analytics.log"
echo ""
echo "Enhanced analytics will appear on model pages after a few data collection cycles."