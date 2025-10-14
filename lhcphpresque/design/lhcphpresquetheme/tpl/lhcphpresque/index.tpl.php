<?php if (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->is_enabled_admin == false) : ?>
    <?php $errors = array(erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Module is not enabled in your instance!'))?>
    
    <?php if (isset($errors)) : ?>
    	<?php include(erLhcoreClassDesign::designtpl('lhkernel/validation_error.tpl.php'));?>
    <?php endif; ?>
    
<?php else : ?>
    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Statistic')?></h3>
    <ul>
        <li><a href="<?php echo erLhcoreClassDesign::baseurl('lhcphpresque/options')?>">Options</a></li>

        <?php
        try {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT count(`chat_id`) FROM lhc_lheschat_index WHERE status = 0');
            $stmt->execute();
            $records = $stmt->fetchColumn();
            echo "<li>Chats pending to index - " . $records . " <a class='csfr-required csfr-post' data-secured='1' href='list=lhc_lheschat_index'>Reschedule</a></li>";
        } catch (Exception $e) {

        }
        ?>

        <?php
        try {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT count(`chat_id`) FROM lhc_lheschat_index WHERE status = 1');
            $stmt->execute();
            $records = $stmt->fetchColumn();
            echo "<li>Chats index in progress - " . $records . " <a class='csfr-required csfr-post' data-secured='1' href='?list=lhc_lheschat_index'>Reschedule</a></li>";
        } catch (Exception $e) {

        }
        ?>

        <?php
        try {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT count(`mail_id`) FROM lhc_lhesmail_index WHERE status = 0 AND op = 1');
            $stmt->execute();
            $records = $stmt->fetchColumn();
            echo "<li>Conversations to index - " . $records . " <a class='csfr-required csfr-post' data-secured='1' href='?list=lhc_lhesmail_index'>Reschedule</a></li>";
        } catch (Exception $e) {

        }
        ?>


        <?php
        try {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT count(`mail_id`) FROM lhc_lhesmail_index WHERE status = 1 AND op = 1');
            $stmt->execute();
            $records = $stmt->fetchColumn();
            echo "<li>Conversations index in progress - " . $records . " <a class='csfr-required csfr-post' data-secured='1' href='?list=lhc_lhesmail_index'>Reschedule</a></li>";
        } catch (Exception $e) {

        }
        ?>


        <?php
        try {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT count(`mail_id`) FROM lhc_lhesmail_index WHERE status = 0 AND op = 0');
            $stmt->execute();
            $records = $stmt->fetchColumn();
            echo "<li>Mails to index - " . $records . " <a class='csfr-required csfr-post' data-secured='1' href='?list=lhc_lhesmail_index'>Reschedule</a></li>";
        } catch (Exception $e) {

        }
        ?>

        <?php
        try {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT count(`mail_id`) FROM lhc_lhesmail_index WHERE status = 1 AND op = 0');
            $stmt->execute();
            $records = $stmt->fetchColumn();
            echo "<li>Mails index progress - " . $records . " <a class='csfr-required csfr-post' data-secured='1' href='?list=lhc_lhesmail_index'>Reschedule</a></li>";
        } catch (Exception $e) {

        }
        ?>


        <?php
        try {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT count(`id`) FROM lhc_mailconv_sent_copy');
            $stmt->execute();
            $records = $stmt->fetchColumn();
            echo "<li>Imap copy pending - " . $records . " <a class='csfr-required csfr-post' data-secured='1' href='?list=lhc_mailconv_sent_copy'>Reschedule</a></li>";
        } catch (Exception $e) {

        }
        ?>

        <?php
        try {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('SELECT count(`online_user_id`) FROM lhc_lhesou_index');
            $stmt->execute();
            $records = $stmt->fetchColumn();
            echo "<li>Online visitors pending - " . $records . " <a class='csfr-required csfr-post' data-secured='1' href='?list=lhc_lhesou_index'>Reschedule</a></li>";
        } catch (Exception $e) {

        }
        ?>
        <?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>
    </ul>
    <div class="row">
    	<div class="col-sm-4">
		    <h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Main queues')?></h3>
    		<ul>
        		<?php foreach (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->settings['queues'] as $queue) : ?>
        		  <li>
                      <a href="<?php echo erLhcoreClassDesign::baseurl('lhcphpresque/list')?>/resque:queue:<?php echo $queue?>"><?php echo $queue?> (<?php echo erLhcoreClassRedis::instance()->llen('resque:queue:' . $queue)?>)</a> | <a href="<?php echo erLhcoreClassDesign::baseurl('lhcphpresque/list')?>/resque:working:<?php echo $queue?>">In progress (<?php echo erLhcoreClassRedis::instance()->llen('resque:working:' . $queue)?>)</a>
                  </li>

        		<?php endforeach; ?>
    		</ul>				
    	</div>
        <div class="col-sm-4">
            <h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Live status')?>
                <button id="toggleRefresh" class="btn btn-sm btn-success" style="margin-left: 10px;">
                    <span id="refreshIcon">▶</span> <span id="refreshText">Start Auto-refresh</span>
                </button>
            </h3>
            <div style="margin-bottom: 10px; font-size: 12px; color: #666;">
                <span id="lastUpdateText">Last updated: </span><span id="lastUpdateTime"><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
            <ul id="liveStatusContainer">
                <?php include(erLhcoreClassDesign::designtpl('lhcphpresque/live_status.tpl.php')); ?>
            </ul>
        </div>
    </div>
    <div class="row">
    	<div class="col-sm-4">
    		<h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Executed jobs statistic')?></h3>
    		<ul>
    		  <li><a href="<?php echo erLhcoreClassDesign::baseurl('lhcphpresque/list')?>/resque:failed"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Failed jobs')?> (<?php echo erLhcoreClassRedis::instance()->llen('resque:failed')?>)</a></li>
    		  <li><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Failed tasks')?> - <?php echo erLhcoreClassRedis::instance()->get('resque:stat:failed')?></li>
    		  <li><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Executed tasks')?> - <?php echo erLhcoreClassRedis::instance()->get('resque:stat:processed')?></li>
    		</ul>
    	</div>	
    </div>

    <script>
    (function() {
        let refreshInterval = null;
        let isRefreshing = false;
        
        const toggleBtn = document.getElementById('toggleRefresh');
        const refreshIcon = document.getElementById('refreshIcon');
        const refreshText = document.getElementById('refreshText');
        const liveStatusContainer = document.getElementById('liveStatusContainer');
        const lastUpdateTime = document.getElementById('lastUpdateTime');
        
        function updateTimestamp() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            lastUpdateTime.textContent = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }
        
        function refreshLiveStatus() {
            if (!isRefreshing) return;
            
            fetch('<?php echo erLhcoreClassDesign::baseurl('lhcphpresque/livestatus')?>')
                .then(response => response.text())
                .then(html => {
                    liveStatusContainer.innerHTML = html;
                    updateTimestamp();
                })
                .catch(error => {
                    console.error('Error refreshing live status:', error);
                });
        }
        
        function startRefreshing() {
            isRefreshing = true;
            refreshIcon.textContent = '⏸';
            refreshText.textContent = 'Stop Auto-refresh';
            toggleBtn.classList.remove('btn-success');
            toggleBtn.classList.add('btn-primary');
            
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            
            refreshInterval = setInterval(() => {
                refreshLiveStatus();
            }, 2000);
        }
        
        function stopRefreshing() {
            isRefreshing = false;
            refreshIcon.textContent = '▶';
            refreshText.textContent = 'Start Auto-refresh';
            toggleBtn.classList.remove('btn-primary');
            toggleBtn.classList.add('btn-success');
            
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }
        
        toggleBtn.addEventListener('click', function() {
            if (isRefreshing) {
                stopRefreshing();
            } else {
                startRefreshing();
            }
        });
        
        // Initialize button state for stopped refresh
        refreshIcon.textContent = '▶';
        refreshText.textContent = 'Start Auto-refresh';
        toggleBtn.classList.add('btn-success');
    })();
    </script>

<?php endif;?>