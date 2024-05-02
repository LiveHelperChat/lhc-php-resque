<?php if (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->is_enabled_admin == false) : ?>
    <?php $errors = array(erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Module is not enabled in your instance!'))?>
    
    <?php if (isset($errors)) : ?>
    	<?php include(erLhcoreClassDesign::designtpl('lhkernel/validation_error.tpl.php'));?>
    <?php endif; ?>
    
<?php else : ?>
    <h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Statistic')?></h3>
    <ul>
        <li><a href="<?=erLhcoreClassDesign::baseurl('lhcphpresque/options')?>">Options</a></li>

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
        <?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>
    </ul>
    <div class="row">
    	<div class="col-sm-4">
		    <h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Main queues')?></h3>	
    		<ul>
        		<?php foreach (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->settings['queues'] as $queue) : ?>
        		  <li><a href="<?=erLhcoreClassDesign::baseurl('lhcphpresque/list')?>/resque:queue:<?php echo $queue?>"><?php echo $queue?> (<?php echo erLhcoreClassRedis::instance()->llen('resque:queue:' . $queue)?>)</a></li>
        		<?php endforeach; ?>
    		</ul>				
    	</div>
    </div>
    <div class="row">
    	<div class="col-sm-4">
    		<h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Executed jobs statistic')?></h3>
    		<ul>
    		  <li><a href="<?=erLhcoreClassDesign::baseurl('lhcphpresque/list')?>/resque:failed"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Failed jobs')?> (<?php echo erLhcoreClassRedis::instance()->llen('resque:failed')?>)</a></li>
    		  <li><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Failed tasks')?> - <?php echo erLhcoreClassRedis::instance()->get('resque:stat:failed')?></li>
    		  <li><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Executed tasks')?> - <?php echo erLhcoreClassRedis::instance()->get('resque:stat:processed')?></li>
    		</ul>
    	</div>	
    </div>
<?php endif;?>