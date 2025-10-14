<?php
// Run me every 5 minutes or so
// /usr/bin/php cron.php -s site_admin -e lhcphpresque -c cron/monitor

$phpresqueOptions = erLhcoreClassModelChatConfig::fetch('lhcphpresque_options');
$dataOptions = (array)$phpresqueOptions->data;

// Implement queue_timeout_limit - Kill jobs running longer than configured time
if (isset($dataOptions['queue_timeout_limit']) && !empty($dataOptions['queue_timeout_limit'])) {
    $messages = array();

    try {
        $redis = erLhcoreClassRedis::instance();
        // Use SSCAN to limit workers retrieval to avoid performance issues with dead workers
        $workers = [];
        $cursor = null;
        $pattern = null;
        do {
            $members = $redis->sscan($cursor, 'resque:workers', $pattern, 100);
            if ($members !== false && !empty($members)) {
                $workers = array_merge($workers, $members);
                if (count($workers) >= 100) {
                    $workers = array_slice($workers, 0, 100);
                    break;
                }
            } else {
                break;
            }
        } while ($cursor != 0);

        if (empty($workers)) {
            // Get all keys matching 'resque:worker:*'
            $workerKeys = $redis->keys('resque:worker:*');
            foreach ($workerKeys as $workerKey) {
                // Extract worker name (everything after 'resque:worker:')
                $workerName = substr($workerKey, strlen('resque:worker:'));
                $workers[] = $workerName;
                if (count($workers) >= 100) {
                    break;
                }
            }
        }

        if (!empty($workers)) {
            foreach ($workers as $worker) {
                $workerData = $redis->get('resque:worker:' . $worker);
                
                if ($workerData) {
                    $job = json_decode($workerData, true);
                    
                    if ($job && isset($job['payload']['class']) && isset($job['queue']) && isset($job['run_at'])) {
                        $queue = $job['queue'];
                        
                        // Check if this queue has a timeout limit configured
                        if (isset($dataOptions['queue_timeout_limit'][$queue]) && 
                            is_numeric($dataOptions['queue_timeout_limit'][$queue]) && 
                            $dataOptions['queue_timeout_limit'][$queue] > 0) {
                            
                            $timeoutLimit = (int)$dataOptions['queue_timeout_limit'][$queue];
                            $startTime = strtotime($job['run_at']);
                            $currentTime = time();
                            $duration = $currentTime - $startTime;
                            
                            // If job is running longer than the limit, kill it
                            if ($duration >= $timeoutLimit) {
                                $messages[] = 'Queue: ' . $queue . ', Worker: ' . $worker . ' - Job timeout exceeded (' . $duration . 's >= ' . $timeoutLimit . 's) at ' . date('Y-m-d H:i:s') . '. Job class: ' . $job['payload']['class'];
                                echo $messages[count($messages)-1] . "\n";
                                
                                // Extract PID from worker ID (format: hostname:pid:queues)
                                $workerParts = explode(':', $worker);
                                if (count($workerParts) >= 2) {
                                    $pid = (int)$workerParts[1];
                                    
                                    // Kill the worker process
                                    if (function_exists('posix_kill')) {
                                        // SIGTERM is 15 - define it if PCNTL extension is not loaded
                                        $sigterm = defined('SIGTERM') ? SIGTERM : 15;
                                        $killed = posix_kill($pid, $sigterm);

                                        if ($killed) {
                                            $messages[] = 'Successfully killed worker PID: ' . $pid;
                                            echo $messages[count($messages)-1] . "\n";
                                            
                                            // Clean up Redis worker data
                                            $redis->srem('resque:workers', $worker);
                                            $redis->del('resque:worker:' . $worker);
                                            $redis->del('resque:worker:' . $worker . ':started');
                                            
                                            // Remove the job from processing
                                            $jobId = isset($job['payload']['id']) ? $job['payload']['id'] : 'unknown';
                                            $messages[] = 'Removed worker from Redis: ' . $worker . ', Job ID: ' . $jobId;
                                            echo $messages[count($messages)-1] . "\n";
                                            
                                            // Log the killed job for audit
                                            erLhcoreClassLog::write(
                                                json_encode([
                                                    'queue' => $queue,
                                                    'worker' => $worker,
                                                    'pid' => $pid,
                                                    'duration' => $duration,
                                                    'timeout_limit' => $timeoutLimit,
                                                    'job_class' => $job['payload']['class'],
                                                    'job_id' => $jobId,
                                                    'job_data' => $job
                                                ], JSON_PRETTY_PRINT),
                                                ezcLog::SUCCESS_AUDIT,
                                                array(
                                                    'source' => 'lhc',
                                                    'category' => 'resque_timeout_kill',
                                                    'line' => __LINE__,
                                                    'file' => __FILE__,
                                                    'object_id' => 0
                                                )
                                            );
                                            
                                            // Dispatch event so extensions know we killed a job
                                            erLhcoreClassChatEventDispatcher::getInstance()->dispatch('lhlhcphpresque.timeout_kill', array(
                                                'queue' => $queue,
                                                'worker' => $worker,
                                                'pid' => $pid,
                                                'duration' => $duration,
                                                'timeout_limit' => $timeoutLimit,
                                                'job' => $job
                                            ));
                                        } else {
                                            $messages[] = 'Failed to kill worker PID: ' . $pid . ' (may already be dead or no permission)';
                                            echo $messages[count($messages)-1] . "\n";
                                        }
                                    } else {
                                        // Fallback for systems without posix extension
                                        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                            exec("taskkill /PID $pid /F", $output, $returnCode);
                                            if ($returnCode === 0) {
                                                $messages[] = 'Successfully killed worker PID: ' . $pid . ' (Windows)';
                                                echo $messages[count($messages)-1] . "\n";
                                                
                                                // Clean up Redis worker data
                                                $redis->srem('resque:workers', $worker);
                                                $redis->del('resque:worker:' . $worker);
                                                $redis->del('resque:worker:' . $worker . ':started');
                                            } else {
                                                $messages[] = 'Failed to kill worker PID: ' . $pid . ' (Windows)';
                                                echo $messages[count($messages)-1] . "\n";
                                            }
                                        } else {
                                            exec("kill -15 $pid 2>&1", $output, $returnCode);
                                            if ($returnCode === 0) {
                                                $messages[] = 'Successfully killed worker PID: ' . $pid . ' (Unix)';
                                                echo $messages[count($messages)-1] . "\n";
                                                
                                                // Clean up Redis worker data
                                                $redis->srem('resque:workers', $worker);
                                                $redis->del('resque:worker:' . $worker);
                                                $redis->del('resque:worker:' . $worker . ':started');
                                            } else {
                                                $messages[] = 'Failed to kill worker PID: ' . $pid . ' (Unix)';
                                                echo $messages[count($messages)-1] . "\n";
                                            }
                                        }
                                    }
                                } else {
                                    $messages[] = 'Invalid worker ID format: ' . $worker;
                                    echo $messages[count($messages)-1] . "\n";
                                }
                            }
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        $messages[] = 'Error checking queue timeouts: ' . $e->getMessage();
        echo $messages[count($messages)-1] . "\n";
        erLhcoreClassLog::write(
            'Error in queue_timeout_limit monitor: ' . $e->getMessage() . "\n" . $e->getTraceAsString(),
            ezcLog::ERROR,
            array(
                'source' => 'lhc',
                'category' => 'resque_timeout_error',
                'line' => __LINE__,
                'file' => __FILE__,
                'object_id' => 0
            )
        );
    }
}

if (isset($dataOptions['queue_limit_clean']) && !empty($dataOptions['queue_limit_clean'])) {
    $messages = array();

    foreach ($dataOptions['queue_limit_clean'] as $queue => $queueLimit) {
        if (isset($dataOptions['queue'][$queue]) && $dataOptions['queue'][$queue] == 1 && is_numeric($queueLimit) && $queueLimit > 0) {
            $queueLength = erLhcoreClassRedis::instance()->llen('resque:queue:' . $queue);
            if ($queueLength >= $queueLimit) {
                $messages[] = $queue.' clean up limit has been reached ' . $queueLength . ' >= '. $queueLimit . ' at ' . date('Y-m-d H:i:s');
                echo $messages[count($messages)-1] . "\n";
                erLhcoreClassRedis::instance()->del('resque:queue:' . $queue);

                // Dispatch event, so extensions knows we have some problems with big queues.
                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('lhlhcphpresque.clean_queue', array('queue' => $queue));
            }
        }
    }

    if (isset($dataOptions['report_email_phpresque']) && !empty($dataOptions) && !empty($messages)) {
        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->FromName = 'Live Helper Chat PHP-Resque';
        $mail->Subject = 'PHP-Resque clean-up overload detected';
        $mail->Body = "Status at the time clean-up overload happened\n" . implode("\n",$messages);

        $emailRecipient = explode(',',$dataOptions['report_email_phpresque']);

        foreach ($emailRecipient as $receiver) {
            $mail->AddAddress( trim($receiver) );
        }

        erLhcoreClassChatMail::setupSMTP($mail);
        $mail->Send();
    }
}

if (isset($dataOptions['queue_limit']) && !empty($dataOptions['queue_limit'])) {

    $messages = array();

    foreach ($dataOptions['queue_limit'] as $queue => $queueLimit) {
        if (isset($dataOptions['queue'][$queue]) && $dataOptions['queue'][$queue] == 1 && is_numeric($queueLimit) && $queueLimit > 0) {
            $queueLength = erLhcoreClassRedis::instance()->llen('resque:queue:' . $queue);
            if ($queueLength >= $queueLimit) {
                $messages[] = $queue.' limit has been reached ' . $queueLength . ' >= '. $queueLimit . ' at ' . date('Y-m-d H:i:s');
                echo $messages[count($messages)-1] . "\n";
            }
        }
    }

    $firstFail = false;

    if (empty($messages)) {
        $dataOptions['fail_mode'] = 0;
    } else {
        if (!isset($dataOptions['fail_mode']) || $dataOptions['fail_mode'] == 0) {
            $dataOptions['fail_mode'] = 1;
            $firstFail = true;
            $dataOptions['first_fail'] = implode("\n",$messages);
        }
        $dataOptions['fail_reason'] = implode("\n",$messages);
    }

    $phpresqueOptions->explain = '';
    $phpresqueOptions->type = 0;
    $phpresqueOptions->hidden = 1;
    $phpresqueOptions->identifier = 'lhcphpresque_options';
    $phpresqueOptions->value = serialize($dataOptions);
    $phpresqueOptions->saveThis();

    if ($firstFail == true && isset($dataOptions['report_email_phpresque']) && !empty($dataOptions))
    {
        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->FromName = 'Live Helper Chat PHP-Resque';
        $mail->Subject = 'PHP-Resque overload detected';
        $mail->Body = "Status at the time overload happened\n" . implode("\n",$messages);

        $emailRecipient = explode(',',$dataOptions['report_email_phpresque']);

        foreach ($emailRecipient as $receiver) {
            $mail->AddAddress( trim($receiver) );
        }

        erLhcoreClassChatMail::setupSMTP($mail);
        $mail->Send();
    }
}

?>