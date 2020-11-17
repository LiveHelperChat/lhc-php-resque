<?php 

return array(
    'connect_resque' => 'localhost',
    'connect_resque_db' => 1,
    'automated_hosting' => false,
    'uac' => false,
    'site_address' => 'https://example.org/lhc_web/index.php',
    'queues' => array(
        'lhc_dummy_queue',
        'lhc_rest_api_queue',
        'lhc_rest_webhook',
        'lhc_uac_queue',
        'lhc_mobile_notify',
        'lhc_stats_resque',
    )
);

?>
