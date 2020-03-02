#!/bin/sh

## exit immediately if uptime is lower than 120 seconds:
uptime_secs=$(cat /proc/uptime | /bin/cut -d"." -f1)
if (( ${uptime_secs} < 120 )); then
    echo "uptime lower than 120 seconds. Exit."
    exit 1
fi

fileCron='/var/www/web/extension/lhcphpresque/doc/.enable-cron'

if [ -f $fileCron ];
then

numberProcess=$(ps aux | grep "[0-9] resque-1.2: *" | awk '{print $2}' | wc -l)

if (( $numberProcess > 4 ));
then
  echo "To many running process..."
  exit 1
fi

fileLock="/var/www/web/extension/lhcphpresque/doc/runresque.lock"

if [ -f $fileLock ];
then
    kill -9 $(ps aux | grep "[0-9] resque-1.2: *" | awk '{print $2}')
    cd /var/www/web/extension/lhcphpresque/doc/ && ./phpresque.sh
    rm -f $fileLock;
else
    PIDS=`ps aux | grep '[0-9] resque-1.2: *'`
    if [ -z "$PIDS" ]; then
       echo "Starting resque"
       cd /var/www/web/extension/lhcphpresque/doc/ && ./phpresque.sh
    fi
fi

fi
