# 🚀 lhc-php-resque  
*Background job processing for Live Helper Chat*

> 💡 Enables asynchronous handling of webhooks, stats, notifications, and more via Redis and PHP Resque.

---

## 📋 Requirements

- ✅ Live Helper Chat 2.52v or newer  
- ✅ Redis server (`redis-server`)  
- ✅ PHP with `pcntl`, `posix`, `json`  
- ✅ Composer installed  
- ✅ Background worker runs as same user as web server (e.g. `www-data`)

---

## 🔧 Installation

1. **Copy `resque.php` to LHC root**
   ```bash
   cp lhc-php-resque/doc/resque.php /var/www/web/
   ```

2. **Copy extension**
   ```bash
   cp -r lhc-php-resque/lhcphpresque /var/www/web/extension/lhcphpresque
   ```

3. **Enable in `settings/settings.ini.php`**
   ```php
   'extensions' => array(
       'lhcphpresque'
   ),
   ```

4. **Install dependencies**
   ```bash
   cd /var/www/web
   composer install
   ```

5. **Configure queues**
   ```bash
   cp extension/lhcphpresque/settings/settings.ini.default.php extension/lhcphpresque/settings/settings.ini.php
   ```
   Edit it to include needed queues:
   ```php
   'queues' => array(
       'webhooks',
       'lhc_rest_webhook',
       'lhc_stats_resque',
       'lhc_mobile_notify',
       // add others as needed
   )
   ```

6. **Create cache dir & enable cron**
   ```bash
   mkdir -p /var/www/web/extension/lhcphpresque/doc/cache/translations
   touch /var/www/web/extension/lhcphpresque/doc/.enable-cron
   ```

---

## ⚙️ Scripts (Improved & Reliable)

> 📌 Replace `/var/www/web` with your actual LHC path if different.

### ✅ `doc/resque.sh` — Worker Monitor
```bash
#!/bin/bash

## Exit if system just booted (<120s)
uptime_secs=$(cut -d"." -f1 /proc/uptime)
if (( uptime_secs < 120 )); then
    exit 1
fi

BASE_DIR="/var/www/web"
fileCron="$BASE_DIR/extension/lhcphpresque/doc/.enable-cron"

# Exit if disabled
[ ! -f "$fileCron" ] && exit 1

# Avoid too many workers
numberProcess=$(ps aux | grep "[0-9] resque-1.2: *" | grep -v grep | wc -l)
(( numberProcess > 4 )) && exit 1

fileLock="$BASE_DIR/extension/lhcphpresque/doc/runresque.lock"

if [ -f "$fileLock" ]; then
    pkill -9 -f "php resque.php"
    sleep 2
    cd "$BASE_DIR/extension/lhcphpresque/doc" && ./phpresque.sh &
    rm -f "$fileLock"
else
    if ! ps aux | grep -q "[0-9] resque-1.2: *"; then
        cd "$BASE_DIR/extension/lhcphpresque/doc" && ./phpresque.sh &
    fi
fi
```

### ✅ `doc/phpresque.sh` — Worker Starter (Fast + Logging)
```bash
#!/bin/bash

cd /var/www/web || {
    echo "❌ ERROR: Cannot cd to LHC root. Check path!" >&2
    exit 1
}

# 🔥 Reduce INTERVAL to 1s for faster webhook response
export QUEUE="*"
export REDIS_BACKEND=localhost:6379
export REDIS_BACKEND_DB=1
export INTERVAL=1
export COUNT=1
export VERBOSE=1

# 📂 Log only errors + rotation-friendly
php extension/lhcphpresque/doc/resque.php >> extension/lhcphpresque/doc/resque_worker.log 2>&1
```

> 📊 **Check logs**:  
> `tail -f extension/lhcphpresque/doc/resque_worker.log`

---

## 🕹️ Test Worker Manually
```bash
cd /var/www/web
QUEUE="*" REDIS_BACKEND_DB=1 php resque.php
```

---

## 🕐 Cron Setup (Optimized)

```bash
# 🔄 Start on reboot (with delay)
@reboot sleep 30 && cd /var/www/web/extension/lhcphpresque/doc && ./resque.sh >> /dev/null 2>&1

# 🔄 Restart daily to prevent memory leaks
40 7 * * * /bin/touch /var/www/web/extension/lhcphpresque/doc/runresque.lock > /dev/null 2>&1

# 🔄 Check every minute
* * * * * cd /var/www/web/extension/lhcphpresque/doc && ./resque.sh >> /dev/null 2>&1

# 🚨 Monitor job overload
*/5 * * * * cd /var/www/web && php cron.php -s site_admin -e lhcphpresque -c cron/monitor > /dev/null 2>&1
```

---

## 📦 Log Rotation (Avoid Disk Fill)

Create logrotate config:
```bash
sudo nano /etc/logrotate.d/lhc-resque
```

Add:
```text
/var/www/web/extension/lhcphpresque/doc/resque_worker.log {
    daily
    missingok
    rotate 7
    compress
    delaycompress
    notifempty
    copytruncate
    su www-data www-data
}
```

> ✅ Keeps last 7 days, compresses, won’t break running process.

---

## 🌐 Enable Webhooks in Background

In `settings/settings.ini.php`:
```php
'webhooks' => array(
    'enabled' => true,
    'worker' => 'resque'  // ✅ Not 'http'
),
```

---

## 🛠️ Troubleshooting

| Issue | Solution |
|------|----------|
| 🔴 Webhooks delayed | ✅ Reduced `INTERVAL=1` in `phpresque.sh` |
| 📂 `filemtime()` error | ✅ Run: `mkdir -p /var/www/web/extension/lhcphpresque/doc/cache/translations` |
| 🚫 Worker not starting | ✅ Check: `touch /var/www/web/extension/lhcphpresque/doc/.enable-cron` |
| 📜 Logs too big | ✅ Use `logrotate` config above |
| 🔁 Restart worker | `touch /var/www/web/extension/lhcphpresque/doc/runresque.lock` |

---

## 💡 Pro Tips

- 🚀 `INTERVAL=1` → faster webhook response (default is 5)
- 🧹 `logrotate` → no more giant log files
- 🧪 Use `QUEUE="webhooks,lhc_rest_webhook"` to limit queues
- 📂 All scripts assume `/var/www/web` — change if needed!

> 🙌 Thanks to real-world deployments for these fixes!
