<?php 

return array(
    'connect_resque' => getenv('REDIS_BACKEND') ?: 'localhost',
    'connect_resque_db' => getenv('REDIS_BACKEND_DB') ?: 1,
    'automated_hosting' => false,
    'uac' => false,
    'site_address' => getenv('RESQUE_SITEADDRESS') ?: 'https://example.org/lhc_web/index.php',
    'queues' => array(
        'lhc_dummy_queue',
        'lhc_rest_api_queue',
        'lhc_rest_webhook',
        'lhc_uac_queue',
        'lhc_mobile_notify',
        'lhc_stats_resque',
        'lhc_views_update',
        'lhc_mailconv',
        'lhc_mailconv_lang',
        'lhc_mailing',
        'lhc_elastic_queue',
        'lhc_insult',
    )
);





?>
