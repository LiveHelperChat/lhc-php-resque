<?php if (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->is_enabled_admin == true && erLhcoreClassUser::instance()->hasAccessTo('lhlhcphpresque','configure')) : ?>
<li class="nav-item"><a class="nav-link" href="<?php echo erLhcoreClassDesign::baseurl('lhcphpresque/index')?>">PHP-Resque</a></li>
<?php endif; ?>