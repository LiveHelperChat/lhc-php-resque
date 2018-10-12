Requirements

LHC 2.52v

1. To start resque worker for debug just use. Change interval to 1 second if you want to check for new jobs every second.
REDIS_BACKEND=localhost:6379 INTERVAL=5 REDIS_BACKEND_DB=0 VERBOSE=1 COUNT=1 QUEUE='*' /usr/bin/php resque.php

2. lhcphpresque/doc contains sample shell script to have it auto running.

// php cron.php -s site_admin -e lhcphpresque -c lhcphpresque/test

echo "Scheduling\n";
erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_dummy_queue', 'erLhcoreClassLHCDummyWorker', array('arguments' => 'first argument'));