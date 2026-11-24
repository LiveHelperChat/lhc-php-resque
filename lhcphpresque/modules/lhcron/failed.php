<?php
// Returns the number of failed jobs in resque:failed
// /usr/bin/php cron.php -s site_admin -e lhcphpresque -c cron/failed

try {
    erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque');
    $redis = erLhcoreClassRedis::instance();

    echo (int)$redis->llen('resque:failed') . "\n";
} catch (Exception $e) {
    erLhcoreClassLog::write(
        'Error in failed jobs count cronjob: ' . $e->getMessage() . "\n" . $e->getTraceAsString(),
        ezcLog::ERROR,
        array(
            'source' => 'lhc',
            'category' => 'resque_fatal',
            'line' => __LINE__,
            'file' => __FILE__,
            'object_id' => 0
        )
    );
    echo "0\n";
}
