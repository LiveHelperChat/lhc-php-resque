<?php

namespace LiveHelperChatExtension\lhcphpresque\providers\helpers;

class Rescheduler
{
    public static function reloadRedisFailedClasses($classes = array())
    {
        if (count($classes) > 0) {
            $items = \erLhcoreClassRedis::instance()->lrange('resque:failed', 0, 100);

            foreach ($items as $key => $item) {
                $jobData = json_decode($item, true);
                $time = strtotime($jobData['failed_at']);

                // Delete older jobs than 7 days
                if (time() > $time + (7 * 24 * 3600)) {
                    \erLhcoreClassRedis::instance()->lrem('resque:failed', 1, $item);
                }

                if (isset($jobData['payload']['class']) && in_array($jobData['payload']['class'], $classes)) {
                    self::enqueue($jobData['queue'], $jobData['payload']['class'], $jobData['payload']['args'][0]);
                    \erLhcoreClassRedis::instance()->lrem('resque:failed', 1, $item);
                }
            }
        }
    }

    public static function rescheduleAllFailed()
    {
        $redis = \erLhcoreClassRedis::instance();
        $items = $redis->lrange('resque:failed', 0, -1);

        foreach ($items as $item) {
            $jobData = json_decode($item, true);

            // Invalid entry, remove it
            if (!is_array($jobData) || !isset($jobData['payload']['class']) || !isset($jobData['queue'])) {
                $redis->lrem('resque:failed', 1, $item);
                continue;
            }

            $args = isset($jobData['payload']['args'][0]) ? $jobData['payload']['args'][0] : array();
            self::enqueue($jobData['queue'], $jobData['payload']['class'], $args);
            $redis->lrem('resque:failed', 1, $item);
        }
    }

    public static function enqueue($queue, $class, $params)
    {
        \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue($queue, $class, $params);
    }
}
