<h1 class="attr-header"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','PHP-Resque monitoring'); ?></h1>

<form action="" method="post">

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>

    <?php if (isset($updated) && $updated == 'done') : $msg = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/onlineusers','Settings updated'); ?>
        <?php include(erLhcoreClassDesign::designtpl('lhkernel/alert_success.tpl.php'));?>
    <?php endif; ?>

    <p><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Choose what queues we should monitor and enter threshold value for them.'); ?></p>

    <div class="row">
    <?php foreach (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->settings['queues'] as $queue) : ?>
    <div class="col-6 pb-1">
        <div class="form-row">
            <div class="col">
                <label>
                    <input type="checkbox" name="queue[<?php echo $queue?>]" value="1" <?php if (isset($phpresque_options['queue'][$queue]) && $phpresque_options['queue'][$queue] == 1) : ?>checked="checked"<?php endif;?> /> <?php echo $queue?>
                </label>
            </div>
            <div class="col">
                <input type="number" class="form-control form-control-sm" placeholder="" name="queue_limit[<?php echo $queue?>]" value="<?php isset($phpresque_options['queue_limit'][$queue]) ? print(htmlspecialchars($phpresque_options['queue_limit'][$queue])) : '';?>" />
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <div class="form-group">
        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','If queue limit is reached send alert e-mail to these e-mails.'); ?></label>
        <input type="text" class="form-control form-control-sm" name="report_email_phpresque" value="<?php isset($phpresque_options['report_email_phpresque']) ? print htmlspecialchars($phpresque_options['report_email_phpresque']) : ''?>" />
    </div>

    <p><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','PHP-Resque is in')?> - <span class="badge badge-<?php if (isset($phpresque_options['fail_mode']) && $phpresque_options['fail_mode'] == 1) : ?>danger<?php else : ?>success<?php endif; ?>"><?php if (isset($phpresque_options['fail_mode']) && $phpresque_options['fail_mode'] == 1) : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','unhealthy')?><?php else : ?><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','healthy')?><?php endif; ?> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','condition')?></p></span>

    <?php if (isset($phpresque_options['fail_reason']) && !empty($phpresque_options['fail_reason'])) : ?>
        <h5><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','First time failure detected')?></h5>
        <p class="text-<?php if (isset($phpresque_options['fail_mode']) && $phpresque_options['fail_mode'] == 1) : ?>danger<?php else : ?>muted<?php endif; ?>"><?php echo htmlspecialchars($phpresque_options['first_fail'])?></p>

        <h5><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','Present status')?></h5>
        <p class="text-<?php if (isset($phpresque_options['fail_mode']) && $phpresque_options['fail_mode'] == 1) : ?>danger<?php else : ?>muted<?php endif; ?>"><?php echo htmlspecialchars($phpresque_options['fail_reason'])?></p>
    <?php endif; ?>

    <input type="submit" class="btn btn-secondary" name="StoreOptions" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Save'); ?>" />

</form>
