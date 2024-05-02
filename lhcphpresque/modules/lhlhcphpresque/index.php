<?php

$tpl = erLhcoreClassTemplate::getInstance( 'lhcphpresque/index.tpl.php');

$validList = [
    'lhc_lhesmail_index',
    'lhc_mailconv_sent_copy',
    'lhc_lheschat_index',
];


if (isset($_GET['list']) && in_array ($_GET['list'], $validList)) {

    $currentUser = erLhcoreClassUser::instance();

    if (!isset($_SERVER['HTTP_X_CSRFTOKEN']) || !$currentUser->validateCSFRToken($_SERVER['HTTP_X_CSRFTOKEN'])) {
        die('Invalid CSRF Token');
        exit;
    }

    $db = ezcDbInstance::get();
    $db->query("UPDATE " . $db->quoteIdentifier($_GET['list']) ." SET status = 0");
}

$Result['content'] = $tpl->fetch();
$Result['path'] = array(array('url' => erLhcoreClassDesign::baseurl('lhcphpresque/index'), 'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('sugarcrm/module','PHP-Resque')));

?>