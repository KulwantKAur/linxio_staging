#!/usr/bin/env bash

set -euo pipefail

touch /opt/traccar/logs/tracker-server.log

dockerize -timeout 1m -wait-retry-interval 10s \
  -template /opt/traccar/conf/traccar.xml.tpl:/opt/traccar/conf/traccar.xml \
  -stdout /opt/traccar/logs/tracker-server.log \
  java -Xms1g -Xmx1g -Djava.net.preferIPv4Stack=true -jar tracker-server.jar conf/traccar.xml
