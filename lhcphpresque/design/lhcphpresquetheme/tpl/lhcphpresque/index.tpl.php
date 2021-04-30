<?php if (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->is_enabled_admin == false) : ?>
    <?php $errors = array(erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Module is not enabled in your instance!'))?>
    
    <?php if (isset($errors)) : ?>
    	<?php include(erLhcoreClassDesign::designtpl('lhkernel/validation_error.tpl.php'));?>
    <?php endif; ?>
    
<?php else : ?>
    <h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Statistic')?></h3>
    <ul>
        <li><a href="<?=erLhcoreClassDesign::baseurl('lhcphpresque/options')?>">Options</a></li>
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