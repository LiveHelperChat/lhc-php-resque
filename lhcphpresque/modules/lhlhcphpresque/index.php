<?php

$tpl = erLhcoreClassTemplate::getInstance( 'lhcphpresque/index.tpl.php');

$validList = [
    'lhc_lhesmail_index',
    'lhc_mailconv_sent_copy',
    'lhc_lheschat_index',
    'lhc_lhesou_index',
];


if (isset($_GET['list']) && in_array ($_GET['list'], $validList)) {

    $currentUser = erLhcoreClassUser::instance();

    if (!isset($_SERVER['HTTP_X_CSRFTOKEN']) || !$currentUser->validateCSFRToken($_SERVER['HTTP_X_CSRFTOKEN'])) {
        die('Invalid CSRF Token');
        exit;
    }

    $db = ezcDbInstance::get();
    $db->query("UPDATE " . $db->quoteIdentifier($_GET['list']) ." SET status = 0");
}

// Clear workers functionality
if (isset($_POST['clear_workers'])) {

    $currentUser = erLhcoreClassUser::instance();

    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        erLhcoreClassModule::redirect('lhcphpresque/index' );
        exit;
    }

    try {
        $redis = erLhcoreClassRedis::instance();
        $redis->del('resque:workers');
        $tpl->set('success_message', erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','All workers cleared'));
    } catch (Exception $e) {
        $tpl->set('error_message', erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Failed to clear workers') . ': ' . $e->getMessage());
    }
}

// Kill worker functionality
if (isset($_POST['kill_worker']) && !empty($_POST['worker_id'])) {
    
    $currentUser = erLhcoreClassUser::instance();

    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        erLhcoreClassModule::redirect('lhcphpresque/index' );
        exit;
    }

    $workerId = $_POST['worker_id'];
    
    try {
        $redis = erLhcoreClassRedis::instance();
        
        // Extract PID from worker ID (format: hostname:pid:queues)
        $workerParts = explode(':', $workerId);
        if (count($workerParts) >= 2) {
            $pid = $workerParts[1];
            
            // Check if worker exists
            if ($redis->sismember('resque:workers', $workerId)) {
                // Send TERM signal to gracefully stop the worker
                if (function_exists('posix_kill')) {
                    // SIGTERM is 15 - define it if PCNTL extension is not loaded
                    $sigterm = defined('SIGTERM') ? SIGTERM : 15;
                    posix_kill($pid, $sigterm);
                    $tpl->set('success_message', 'Worker ' . htmlspecialchars($workerId) . ' has been sent termination signal (SIGTERM)');
                } else {
                    // Fallback for Windows or systems without posix extension
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        exec("taskkill /PID $pid /F");
                        $tpl->set('success_message', 'Worker ' . htmlspecialchars($workerId) . ' termination command sent (Windows)');
                    } else {
                        exec("kill -15 $pid");
                        $tpl->set('success_message', 'Worker ' . htmlspecialchars($workerId) . ' termination command sent');
                    }
                }
            } else {
                $tpl->set('error_message', 'Worker not found in active workers list');
            }
        } else {
            $tpl->set('error_message', 'Invalid worker ID format');
        }
    } catch (Exception $e) {
        $tpl->set('error_message', 'Error killing worker: ' . $e->getMessage());
    }
}

$Result['content'] = $tpl->fetch();
$Result['path'] = array(array('url' => erLhcoreClassDesign::baseurl('lhcphpresque/index'), 'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('sugarcrm/module','PHP-Resque')));

?>