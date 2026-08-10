<?php

// php cron.php -s site_admin -e lhcphpresque -c lhcphpresque/test

echo "Scheduling\n";
erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_dummy_queue', 'erLhcoreClassLHCDummyWorker', array('delay' => 2));
erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_dummy_queue', 'erLhcoreClassLHCDummyWorker', array('delay' => 4));
erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->enqueue('lhc_dummy_queue', 'erLhcoreClassLHCDummyWorker', array('delay' => 5));



