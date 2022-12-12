<?php
// Run me every 5 minutes or so
// /usr/bin/php cron.php -s site_admin -e lhcphpresque -c cron/monitor

$phpresqueOptions = erLhcoreClassModelChatConfig::fetch('lhcphpresque_options');
$dataOptions = (array)$phpresqueOptions->data;

if (isset($dataOptions['queue_limit_clean']) && !empty($dataOptions['queue_limit_clean'])) {
    $messages = array();

    foreach ($dataOptions['queue_limit_clean'] as $queue => $queueLimit) {
        if (isset($dataOptions['queue'][$queue]) && $dataOptions['queue'][$queue] == 1 && is_numeric($queueLimit) && $queueLimit > 0) {
            $queueLength = erLhcoreClassRedis::instance()->llen('resque:queue:' . $queue);
            if ($queueLength >= $queueLimit) {
                $messages[] = $queue.' clean up limit has been reached ' . $queueLength . ' >= '. $queueLimit . ' at ' . date('Y-m-d H:i:s');
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
        if (isset($dataOptions['queue'][$queue]) && $dataOptions['queue'][$queue] == 1) {
            $queueLength = erLhcoreClassRedis::instance()->llen('resque:queue:' . $queue);
            if ($queueLength >= $queueLimit) {
                $messages[] = $queue.' limit has been reached ' . $queueLength . ' >= '. $queueLimit . ' at ' . date('Y-m-d H:i:s');
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




