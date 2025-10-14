<?php 
$redis = erLhcoreClassRedis::instance();
// Use SSCAN to limit workers retrieval to avoid performance issues with dead workers
$workers = [];
$cursor = null;
$pattern = null;
do {
    $workers = $redis->smembers('resque:workers');
    if ($workers !== false && !empty($workers)) {
        if (count($workers) >= 100) {
            $workers = array_slice($workers, 0, 100);
        }
    }
} while ($cursor != 0);

if (empty($workers)) {
    // Get all keys matching 'resque:worker:*'
    $workerKeys = $redis->keys('resque:worker:*');
    $workersRegistered = count($workerKeys);

    $workersRaw = [];
    foreach ($workerKeys as $workerKey) {
        // Extract worker name (everything after 'resque:worker:')
        $workerName = substr($workerKey, strlen('resque:worker:'));
        $workersRaw[] = $workerName;
        if (count($workersRaw) >= 100) {
            break;
        }
    }
    $workers = $workersRaw;
} else {
    $workersRegistered = count($workers);
}


?>

<?php if (!empty($workers)) : ?>
    <li><strong><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Active Workers'); ?>:</strong>&nbsp; <?php echo $workersRegistered; ?></li>
    
    <?php 
    // Collect worker data with durations for sorting
    $workerList = [];
    foreach ($workers as $worker) {
        $workerData = $redis->get('resque:worker:' . $worker);
        $duration = -1;
        $job = null;
        
        if ($workerData) {
            $job = json_decode($workerData, true);
            if ($job && isset($job['run_at'])) {
                $startTime = strtotime($job['run_at']);
                $currentTime = time();
                $duration = $currentTime - $startTime;
            }
        }
        
        $workerList[] = [
            'worker' => $worker,
            'workerData' => $workerData,
            'job' => $job,
            'duration' => $duration
        ];
    }
    
    // Sort by duration (longest running first)
    usort($workerList, function($a, $b) {
        return $b['duration'] - $a['duration'];
    });
    ?>
    
    <?php foreach ($workerList as $workerInfo) : ?>
        <?php $worker = $workerInfo['worker']; ?>
        <?php $workerData = $workerInfo['workerData']; ?>
        <?php $job = $workerInfo['job']; ?>
        
        <?php if ($workerData) : ?>
            <?php if ($job && isset($job['payload']['class'])) : ?>
                <li style="margin-bottom: 10px;" class="fs13">
                    <strong title="<?php echo htmlspecialchars($worker); ?>"><?php echo erLhcoreClassDesign::shrt($worker,60,'...',30,ENT_QUOTES);?> </strong><form method="post" style="display:inline;" class="kill-worker-form" onsubmit="return confirm('Are you sure you want to kill this worker?');">
                        <input type="hidden" name="kill_worker" value="1">
                        <input type="hidden" name="worker_id" value="<?php echo htmlspecialchars($worker, ENT_QUOTES); ?>">
                        <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>
                        <button type="submit" class="btn btn-danger btn-xs csfr-required" style="margin-top: 5px;"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Kill Worker'); ?></button>
                    </form><br/>
                    <?php 
                    $duration = $workerInfo['duration'];
                    $hours = floor($duration / 3600);
                    $minutes = floor(($duration % 3600) / 60);
                    $seconds = $duration % 60;
                    ?>
                    Duration: <b><?php echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds); ?></b><br/>
                    Queue: 
<pre class="pb-0 mb-0" style="max-width: 400px;"><?php echo htmlspecialchars(json_encode($job, JSON_PRETTY_PRINT)); ?></pre>
                    Queued At: <?php echo date('Y-m-d H:i:s', (int)$job['payload']['queue_time']); ?><br/>
                    Started At: <?php echo htmlspecialchars(date('Y-m-d H:i:s',strtotime($job['run_at']))); ?><br/>

                </li>
            <?php else : ?>
                <li style="margin-bottom: 10px;">
                    <strong title="<?php echo htmlspecialchars($worker); ?>" class="fs13"><?php echo erLhcoreClassDesign::shrt($worker,60,'...',30,ENT_QUOTES);?></strong> - <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Idle'); ?>
                    &nbsp;&nbsp;<form method="post" style="display:inline;" class="kill-worker-form" onsubmit="return confirm('Are you sure you want to kill this worker?');">
                        <input type="hidden" name="kill_worker" value="1">
                        <input type="hidden" name="worker_id" value="<?php echo htmlspecialchars($worker, ENT_QUOTES); ?>">
                        <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>
                        <button type="submit" class="btn btn-danger btn-xs csfr-required" style="margin-top: 5px;"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Kill Worker'); ?></button>
                    </form>
                </li>
            <?php endif; ?>

        <?php else : ?>
            <li style="margin-bottom: 10px;">
                <strong title="<?php echo htmlspecialchars($worker); ?>" class="fs13"><?php echo erLhcoreClassDesign::shrt($worker,60,'...',30,ENT_QUOTES);?></strong> - <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Idle'); ?><br/>
                &nbsp;&nbsp;<form method="post" style="display:inline;" class="kill-worker-form" onsubmit="return confirm('Are you sure you want to kill this worker?');">
                    <input type="hidden" name="kill_worker" value="1">
                    <input type="hidden" name="worker_id" value="<?php echo htmlspecialchars($worker, ENT_QUOTES); ?>">
                    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>
                    <button type="submit" class="btn btn-danger btn-xs csfr-required" style="margin-top: 5px;"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Kill Worker'); ?></button>
                </form>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else : ?>
    <li><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','No active workers'); ?></li>
<?php endif; ?>