<?php
// Run me every few minutes or so
// /usr/bin/php cron.php -s site_admin -e lhcphpresque -c cron/reschedule
//
// Class can be either provided or not:
// - /usr/bin/php cron.php -s site_admin -e lhcphpresque -c cron/reschedule --class=erLhcoreClassLHCUACWorker
// - /usr/bin/php cron.php -s site_admin -e lhcphpresque -c cron/reschedule --class=ClassOne,ClassTwo
// If no class is provided all failed jobs are rescheduled.
// Jobs can also be filtered by class via lhcphpresque_options -> reschedule_classes config option.

$messages = array();

try {
    $resqueInstance = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque');
    $redis = erLhcoreClassRedis::instance();

    $phpresqueOptions = erLhcoreClassModelChatConfig::fetch('lhcphpresque_options');
    $dataOptions = (array)$phpresqueOptions->data;

    // Class filter - can be provided via CLI argument or via lhcphpresque_options config
    $classes = array();

    // From CLI argument --class=ClassOne,ClassTwo
    if (isset($argv)) {
        foreach ($argv as $arg) {
            if (strpos($arg, '--class=') === 0) {
                $cliClasses = array_filter(array_map('trim', explode(',', substr($arg, 8))));
                $classes = array_merge($classes, $cliClasses);
            }
        }
    }

    // From lhcphpresque_options config reschedule_classes
    if (empty($classes) && isset($dataOptions['reschedule_classes']) && !empty($dataOptions['reschedule_classes'])) {
        $configClasses = is_array($dataOptions['reschedule_classes']) ? $dataOptions['reschedule_classes'] : array_map('trim', explode(',', $dataOptions['reschedule_classes']));
        $classes = array_merge($classes, array_filter($configClasses));
    }

    // Discard failed jobs older than this amount of days
    $maxAgeDays = isset($dataOptions['reschedule_max_age_days']) && !empty($dataOptions['reschedule_max_age_days']) ? (int)$dataOptions['reschedule_max_age_days'] : 7;

    // How many failed jobs to process per cron run, 0 means no limit
    $processLimit = isset($dataOptions['reschedule_limit']) && !empty($dataOptions['reschedule_limit']) ? (int)$dataOptions['reschedule_limit'] : 0;

    $items = $redis->lrange('resque:failed', 0, -1);

    $rescheduled = 0;
    $cleaned = 0;
    $processed = 0;

    foreach ($items as $item) {
        if ($processLimit > 0 && $processed >= $processLimit) {
            break;
        }

        $processed++;

        $jobData = json_decode($item, true);

        // Invalid entry, remove it
        if (!is_array($jobData) || !isset($jobData['payload']['class']) || !isset($jobData['queue'])) {
            $redis->lrem('resque:failed', 1, $item);
            $cleaned++;
            continue;
        }

        // Discard jobs that failed too long ago
        if (isset($jobData['failed_at'])) {
            $failedAt = strtotime($jobData['failed_at']);
            if ($failedAt !== false && time() > $failedAt + ($maxAgeDays * 24 * 3600)) {
                $redis->lrem('resque:failed', 1, $item);
                $cleaned++;
                continue;
            }
        }

        $jobClass = $jobData['payload']['class'];

        // If a class filter is provided, only reschedule matching jobs
        if (count($classes) > 0 && !in_array($jobClass, $classes)) {
            continue;
        }

        // Re-enqueue the failed job to its original queue
        $args = isset($jobData['payload']['args'][0]) ? $jobData['payload']['args'][0] : array();
        $resqueInstance->enqueue($jobData['queue'], $jobClass, $args);

        $redis->lrem('resque:failed', 1, $item);
        $rescheduled++;

        $messages[] = 'Rescheduled failed job. Class: ' . $jobClass . ', Queue: ' . $jobData['queue'] . ' at ' . date('Y-m-d H:i:s');
        echo $messages[count($messages)-1] . "\n";
    }

    if (count($messages) > 0) {
        // Dispatch event so extensions can react to rescheduled jobs
        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('lhlhcphpresque.reschedule', array(
            'rescheduled' => $rescheduled,
            'cleaned' => $cleaned,
            'classes' => array_values($classes)
        ));
    }
} catch (Exception $e) {
    $messages[] = 'Error rescheduling failed jobs: ' . $e->getMessage();
    echo $messages[count($messages)-1] . "\n";
    erLhcoreClassLog::write(
        'Error in reschedule cronjob: ' . $e->getMessage() . "\n" . $e->getTraceAsString(),
        ezcLog::ERROR,
        array(
            'source' => 'lhc',
            'category' => 'resque_fatal',
            'line' => __LINE__,
            'file' => __FILE__,
            'object_id' => 0
        )
    );
}
