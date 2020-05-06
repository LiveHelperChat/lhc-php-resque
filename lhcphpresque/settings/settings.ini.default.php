<?php 

return array(
    'connect_resque' => 'localhost',
    'connect_resque_db' => 1,
    'automated_hosting' => false,
    'site_address' => 'https://devmysql.livehelperchat.com/lhc_web/index.php',
    'queues' => array(
        'lhc_dummy_queue',
        'lhc_rest_api_queue',
    )
);

?>