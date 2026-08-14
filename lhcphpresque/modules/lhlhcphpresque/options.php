<?php

$tpl = erLhcoreClassTemplate::getInstance('lhcphpresque/options.tpl.php');

if (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->is_enabled_admin == false) {

    $tpl = erLhcoreClassTemplate::getInstance('lhkernel/validation_error.tpl.php');
    $tpl->set('errors', array('Module is not enabled in your instance!'));
    $Result['content'] = $tpl->fetch();
    $Result['path'] = array(
        array('url' => erLhcoreClassDesign::baseurl('lhcphpresque/index'), 'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('sugarcrm/module','PHP-Resque')),
        array('title' => erTranslationClassLhTranslation::getInstance()->getTranslation('sugarcrm/module','List'))
    );

} else {

    $phpresqueOptions = erLhcoreClassModelChatConfig::fetch('lhcphpresque_options');
    $data = (array)$phpresqueOptions->data;

    if (isset($_POST['StoreOptions'])) {

        $definition = array(
            'queue' => new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'string', null, FILTER_REQUIRE_ARRAY
            ),
            'queue_limit' => new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1), FILTER_REQUIRE_ARRAY
            ),
            'queue_timeout_limit' => new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1), FILTER_REQUIRE_ARRAY
            ),
            'queue_limit_clean' => new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1), FILTER_REQUIRE_ARRAY
            ),
            'report_email_phpresque' => new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
            ),
            'failed_jobs_limit' => new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1)
            ),
            'failed_jobs_timeout' => new ezcInputFormDefinitionElement(
                ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1)
            ),
        );

        $form = new ezcInputForm(INPUT_POST, $definition);
        $Errors = array();

        if ($form->hasValidData('queue')) {
            $data['queue'] = $form->queue;
        }

        if ($form->hasValidData('queue_limit')) {
            $data['queue_limit'] = $form->queue_limit;
        } else {
            $data['queue_limit'] = [];
        }

        if ($form->hasValidData('queue_limit_clean')) {
            $data['queue_limit_clean'] = $form->queue_limit_clean;
        } else {
            $data['queue_limit_clean'] = [];
        }

        if ($form->hasValidData('queue_timeout_limit')) {
            $data['queue_timeout_limit'] = $form->queue_timeout_limit;
        } else {
            $data['queue_timeout_limit'] = [];
        }

        if ($form->hasValidData('report_email_phpresque')) {
            $data['report_email_phpresque'] = $form->report_email_phpresque;
        } else {
            $data['report_email_phpresque'] = '';
        }

        if ($form->hasValidData('failed_jobs_limit')) {
            $data['failed_jobs_limit'] = $form->failed_jobs_limit;
        } else {
            $data['failed_jobs_limit'] = 0;
        }

        if ($form->hasValidData('failed_jobs_timeout')) {
            $data['failed_jobs_timeout'] = $form->failed_jobs_timeout;
        } else {
            $data['failed_jobs_timeout'] = 0;
        }

        $phpresqueOptions->explain = '';
        $phpresqueOptions->type = 0;
        $phpresqueOptions->hidden = 1;
        $phpresqueOptions->identifier = 'lhcphpresque_options';
        $phpresqueOptions->value = serialize($data);
        $phpresqueOptions->saveThis();

        $tpl->set('updated', 'done');
    }

    $tpl->set('phpresque_options', $data);

    $Result['content'] = $tpl->fetch();

    $Result['path'] = array(
        array(
            'url' => erLhcoreClassDesign::baseurl('lhcphpresque/index'),
            'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin', 'PHP-Resque')
        ),
        array(
            'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhcphpresquetheme/admin', 'Options')
        )
    );
}

?>