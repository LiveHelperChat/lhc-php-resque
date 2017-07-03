<?php

$Module = array( "name" => "PHP-Resque",
				 'variable_params' => true );

$ViewList = array();

$ViewList['index'] = array(
    'params' => array(),
    'uparams' => array(),
    'functions' => array('configure')
);

$ViewList['list'] = array(
    'params' => array('list'),
    'uparams' => array('reload'),
    'functions' => array('configure')
);

$FunctionList['use'] = array('explain' => 'Allow operator to use PHP-Resque module');
$FunctionList['configure'] = array('explain' => 'Allow operator to configure PHP-Resque module');