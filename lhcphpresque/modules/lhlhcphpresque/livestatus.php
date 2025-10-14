<?php

header('Content-Type: text/html; charset=utf-8');

try {
    $tpl = erLhcoreClassTemplate::getInstance('lhcphpresque/live_status.tpl.php');
    echo $tpl->fetch();
} catch (Exception $e) {
    echo '<li>Error loading live status: ' . htmlspecialchars($e->getMessage()) . '</li>';
}

exit;

?>
