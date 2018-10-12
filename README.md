# lhc-php-resque
Enables php-resque functionality in Live Helper Chat

### Requirements

LHC 2.52v

1. Copy lhcphpresque/doc/resque.php to main folder lhc_web. Just root folder where Live Helper Chat is installed.
2. Copy lhc-php-resque/lhcphpresque to extension/lhcphpresque
3. Activate extension in settings/settings.ini.php extensions section.
```
'extensions' => 
      array (
        'lhcphpresque'
      ),
```
5. Make sure you have composer dependencies installed.
```
cd extension/lhcphpresque && composer update
```
4. To start resque worker for debug just use. You can decrease interval how often worker checks for new jobs by settings interval value to 1
```
REDIS_BACKEND=localhost:6379 INTERVAL=5 REDIS_BACKEND_DB=0 VERBOSE=1 COUNT=1 QUEUE='*' /usr/bin/php resque.php
```

5. Once you are happy how everything works. Create folder named 'cron' in root Live Helper Chat folder. Setup cronjobs as following. Before setuping cronjobs make sure you check their paths. First cronjobs ensures that worker is started upon reboot. Second cronjobs restarts worker every day (I suggest to keep it to avoid any memory leaks in php). Third one checks do we need to restart php resque or not. After code changes workers has to be restarted. Easiest way to restart is to create a lock file.
```
@reboot cd /var/www/web/extension/lhcphpresque/doc/ && ./phpresque.sh >> /dev/null 2>&1
40 7 * * * /bin/touch /var/www/web/extension/lhcphpresque/doc/runresque.lock > /dev/null 2>&1
* * * * * cd /var/www/web/extension/lhcphpresque/doc && ./resque.sh >> /dev/null 2>&1
```

5. Example schedule command
```
echo "Scheduling\n";
erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_dummy_queue', 'erLhcoreClassLHCDummyWorker', array('arguments' => 'first argument'));
```
