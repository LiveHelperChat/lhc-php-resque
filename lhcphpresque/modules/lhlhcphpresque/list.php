<?php

if (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->is_enabled_admin == false) {
    
    $tpl = erLhcoreClassTemplate::getInstance('lhkernel/validation_error.tpl.php');
    $tpl->set('errors', array('Module is not enabled in your instance!'));
    $Result['content'] = $tpl->fetch();
    $Result['path'] = array(
        array('url' => erLhcoreClassDesign::baseurl('lhcphpresque/index'), 'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('sugarcrm/module','PHP-Resque')),
        array('title' => erTranslationClassLhTranslation::getInstance()->getTranslation('sugarcrm/module','List'))
    );
    
} else {
    $tpl = erLhcoreClassTemplate::getInstance('lhcphpresque/list.tpl.php');
    $classList = array();
    
    $list = $Params['user_parameters']['list'];
    
    $tpl->set('list',$list);
    
    if (isset($_POST['cleanActions']) && isset($_POST['cleanActions'])) {
        erLhcoreClassRedis::instance()->del($list);
    }
    
    if (isset($Params['user_parameters_unordered']['reload'])) {
        $classList = array($Params['user_parameters_unordered']['reload']);
    }
    
    if (count($classList) > 0) {
        erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->reloadRedisFailedClasses($classList);   
        erLhcoreClassModule::redirect('lhcphpresque/list', '/resque:failed'); //redirect to url
    }
    
    $items = array();
    
    $pages = new lhPaginator();
    $pages->items_total = erLhcoreClassRedis::instance()->llen($list);
    $pages->translationContext = 'abstract/list';
    $pages->serverURL = erLhcoreClassDesign::baseurl('lhcphpresque/list') . '/' .$list;
    $pages->setItemsPerPage(20);
    $pages->paginate();
    
    $tpl->set('pages', $pages);
    
    if ($pages->items_total > 0) {
        $items = erLhcoreClassRedis::instance()->lrange($list, $pages->low, $pages->low + $pages->items_per_page);
    }
    
    $tpl->set('items', $items);
    
    $Result['content'] = $tpl->fetch();
    $Result['path'] = array(
        array('url' => erLhcoreClassDesign::baseurl('lhcphpresque/index'), 'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','PHP-Resque')),
        array('title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin','List'))
    );
}
