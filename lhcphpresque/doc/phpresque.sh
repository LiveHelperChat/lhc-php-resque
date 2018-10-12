#!/bin/sh
# live site cronjobs

echo "Running live site cronjobs"
cd /var/www/web
REDIS_BACKEND=localhost:6379 INTERVAL=5 REDIS_BACKEND_DB=0 COUNT=4 VERBOSE=0 QUEUE='*' /usr/bin/php resque.php 2>&1