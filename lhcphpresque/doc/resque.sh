#!/bin/sh

## exit immediately if uptime is lower than 120 seconds:
uptime_secs=$(cat /proc/uptime | /bin/cut -d"." -f1)
if (( ${uptime_secs} < 120 )); then 
    echo "uptime lower than 120 seconds. Exit."
    exit 1
fi

fileCron='/var/www/.enable-cron'

if [ -f $fileCron ];
then

fileLock="/var/www/web/cron/runresque.lock"

if [ -f $fileLock ];
then
    kill -9 $(ps aux | grep "[0-9] /usr/bin/php resque.php" | awk '{print $2}')    
    cd /var/www/web && /usr/bin/php cron.php -s site_admin -c cron/util/clear_cache
    cd /var/www/cronjobs/ && ./phpresque.sh
    rm -f $fileLock;
else
    PIDS=`ps aux | grep '[0-9] /usr/bin/php resque.php'`
    if [ -z "$PIDS" ]; then
       echo "Starting resque"
       cd /var/www/web && /usr/bin/php cron.php -s site_admin -c cron/util/clear_cache
       cd /var/www/cronjobs/ && ./phpresque.sh
    fi
fi

fi