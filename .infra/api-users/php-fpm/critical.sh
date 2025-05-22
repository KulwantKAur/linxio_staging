#!/bin/bash

CRITICAL_LOG_FILE="/srv/var/log/critical_log.log"
LOG_GROUP_NAME="/aws/ecs/linxio-prod-main-app-api-users-critical"
LOG_STREAM_NAME="critical.log"

while [ ! -f "$CRITICAL_LOG_FILE" ]; do
    sleep 1
done

tail -n+1 -f "$CRITICAL_LOG_FILE" | while read -r line; do
    # Escape double quotes and backslashes
    escaped_line=$(echo "$line" | sed 's/["\\]/\\&/g')

    # Wrap the entire string in double quotes to ensure it's a valid JSON string
    escaped_line="\"$escaped_line\""

    aws logs put-log-events \
        --log-group-name "$LOG_GROUP_NAME" \
        --log-stream-name "$LOG_STREAM_NAME" \
        --log-events "timestamp=$(date +%s%N | cut -b1-13),message=$escaped_line"
done