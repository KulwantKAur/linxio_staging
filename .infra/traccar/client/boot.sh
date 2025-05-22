#!/usr/bin/env bash

tracker_server_host=${TCP_SERVER_HOST}
tracker_server_port=${TCP_SERVER_PORT}
message_file="/usr/src/app/message.txt"

# Copy file with tracker payload
if [ ! -f $message_file ]; then
    cp "$message_file.dist" "$message_file"
fi

# Wait for server container
chmod +x /usr/local/bin/wait-for.sh
/usr/local/bin/wait-for.sh $tracker_server_host:$tracker_server_port

# Prepare application
npm ci
npm start