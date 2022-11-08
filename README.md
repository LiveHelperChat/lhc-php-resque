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
4. Make sure you have composer dependencies installed.
```
cd extension/lhcphpresque && composer update
```
5. Make sure you copied extension/lhcphpresque/settings/settings.ini.default.php to extension/lhcphpresque/settings/settings.ini.php. Edit `queues` and add custom queues you have from other extension.

6. To start resque worker for debug just use. You can decrease interval how often worker checks for new jobs by settings interval value to 1
```
REDIS_BACKEND=localhost:6379 INTERVAL=5 REDIS_BACKEND_DB=1 VERBOSE=1 COUNT=1 QUEUE='*' /usr/bin/php resque.php
```

7. Once you are happy how everything works. Create folder named `cron` in root Live Helper Chat folder. Setup cronjobs as following. Before setuping cronjobs make sure you check their paths. First cronjobs ensures that worker is started upon reboot. Second cronjobs restarts worker every day (I suggest to keep it to avoid any memory leaks in php). Third one checks do we need to restart php resque or not. After code changes workers has to be restarted. Easiest way to restart is to create a lock file.
```
@reboot cd /var/www/web/extension/lhcphpresque/doc/ && ./phpresque.sh >> /dev/null 2>&1
40 7 * * * /bin/touch /var/www/web/extension/lhcphpresque/doc/runresque.lock > /dev/null 2>&1
* * * * * cd /var/www/web/extension/lhcphpresque/doc && ./resque.sh >> /dev/null 2>&1
```

8. Example schedule command
```
echo "Scheduling\n";
erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_dummy_queue', 'erLhcoreClassLHCDummyWorker', array('arguments' => 'first argument'));
```

9. Setup alerts for resque jobs overloads

To receive alerts about overload of jobs you have to setup cronjob to run every 5 minutes or so.

```
/usr/bin/php cron.php -s site_admin -e lhcphpresque -c cron/monitor
```

To active for webhooks events

```
 'webhooks' =>
    array (
      'enabled' => true,
      'worker' => 'resque'//'http',
    ),
```

