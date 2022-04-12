<?php

/**
 * Global queue
 *
 * REDIS_BACKEND=localhost:6379 REDIS_BACKEND_DB=0 VERBOSE=1 COUNT=1 QUEUE='*' /usr/bin/php resque.php > var/resque.log
 *
 * by priority
 *
 * */

$QUEUE = getenv('QUEUE');

if(empty($QUEUE)) {
    die("Set QUEUE env var containing the list of queues to work.\n");
}

require_once "ezcomponents/Base/src/base.php"; // dependent on installation method, see below

ezcBase::addClassRepository( './','./lib/autoloads');

spl_autoload_register(array('ezcBase','autoload'), true, false);
spl_autoload_register(array('erLhcoreClassSystem','autoload'), true, false);

// your code here
ezcBaseInit::setCallback(
    'ezcInitDatabaseInstance',
    'erLhcoreClassLazyDatabaseConfiguration'
);

function defaultCronjobFatalHandler($errno, $errstr, $errfile, $errline) {

    $msg = 'Unexpected error, the message was : ' . $errstr . ' in ' . $errfile . ' on line ' . $errline;

    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

    if ($errno == E_USER_ERROR || $errno == E_COMPILE_ERROR || $errno == E_PARSE || $errno == E_ERROR || $errno == E_RECOVERABLE_ERROR || $errno == E_WARNING) {
        error_log($msg);

        erLhcoreClassLog::write($msg);
        erLhcoreClassLog::write(
            json_encode([
                'msg' => $msg,
                'trace' => $trace
            ]),
            ezcLog::SUCCESS_AUDIT,
            array(
                'source' => 'lhc',
                'category' => 'resque_fatal',
                'line' => __LINE__,
                'file' => __FILE__,
                'object_id' => 0
            )
        );
    }

    return false;
}

function defaultCronjobExceptionHandler($e) {

    error_log($e);

    erLhcoreClassLog::write(json_encode([
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTrace(),
        'raw' => (string)$e,
    ],JSON_PRETTY_PRINT));

    erLhcoreClassLog::write(
        json_encode([
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
            'raw' => (string)$e,
        ],JSON_PRETTY_PRINT)
        ,
        ezcLog::SUCCESS_AUDIT,
        array(
            'source' => 'lhc',
            'category' => 'resque_exception',
            'line' => __LINE__,
            'file' => __FILE__,
            'object_id' => 0
        )
    );
}

set_exception_handler( 'defaultCronjobExceptionHandler' );
set_error_handler ( 'defaultCronjobFatalHandler' );

$instance = erLhcoreClassSystem::instance();
$instance->SiteDir = dirname(__FILE__).'/';
$instance->backgroundMode = true;

$cfgSite = erConfigClassLhConfig::getInstance();

$defaultSiteAccess = $cfgSite->getSetting( 'site', 'default_site_access' );
$optionsSiteAccess = $cfgSite->getSetting('site_access_options',$defaultSiteAccess);
$instance->Language = $optionsSiteAccess['locale'];
$instance->ThemeSite = $optionsSiteAccess['theme'];
$instance->SiteAccess = $defaultSiteAccess;
$instance->WWWDirLang = '';

erLhcoreClassModule::attatchExtensionListeners();

$tz = isset($optionsSiteAccess['time_zone']) ? $optionsSiteAccess['time_zone'] : $cfgSite->getSetting( 'site', 'time_zone' );

date_default_timezone_set($tz);

erLhcoreClassModule::$defaultTimeZone = $tz;
erLhcoreClassModule::$dateFormat = $cfgSite->getSetting('site', 'date_format', false);
erLhcoreClassModule::$dateHourFormat = $cfgSite->getSetting('site', 'date_hour_format', false);
erLhcoreClassModule::$dateDateHourFormat = $cfgSite->getSetting('site', 'date_date_hour_format', false);

//************************

$QUEUE = getenv('QUEUE');
if(empty($QUEUE)) {
    die("Set QUEUE env var containing the list of queues to work.\n");
}
/**
 * REDIS_BACKEND can have simple 'host:port' format or use a DSN-style format like this:
 * - redis://user:pass@host:port
 *
 * Note: the 'user' part of the DSN URI is required but is not used.
 */
$REDIS_BACKEND = getenv('REDIS_BACKEND');
// A redis database number
$REDIS_BACKEND_DB = getenv('REDIS_BACKEND_DB');
if(!empty($REDIS_BACKEND)) {
    if (empty($REDIS_BACKEND_DB))
        Resque::setBackend($REDIS_BACKEND);
    else
        Resque::setBackend($REDIS_BACKEND, $REDIS_BACKEND_DB);
}
$logLevel = false;
$LOGGING = getenv('LOGGING');
$VERBOSE = getenv('VERBOSE');
$VVERBOSE = getenv('VVERBOSE');
if(!empty($LOGGING) || !empty($VERBOSE)) {
    $logLevel = true;
}
else if(!empty($VVERBOSE)) {
    $logLevel = true;
}
$APP_INCLUDE = getenv('APP_INCLUDE');
if($APP_INCLUDE) {
    if(!file_exists($APP_INCLUDE)) {
        die('APP_INCLUDE ('.$APP_INCLUDE.") does not exist.\n");
    }
    require_once $APP_INCLUDE;
}
// See if the APP_INCLUDE containes a logger object,
// If none exists, fallback to internal logger
if (!isset($logger) || !is_object($logger)) {
    $logger = new Resque_Log($logLevel);
}
$BLOCKING = getenv('BLOCKING') !== FALSE;
$interval = 5;
$INTERVAL = getenv('INTERVAL');
if(!empty($INTERVAL)) {
    $interval = $INTERVAL;
}
$count = 1;
$COUNT = getenv('COUNT');
if(!empty($COUNT) && $COUNT > 1) {
    $count = $COUNT;
}
$PREFIX = getenv('PREFIX');
if(!empty($PREFIX)) {
    $logger->log(Psr\Log\LogLevel::INFO, 'Prefix set to {prefix}', array('prefix' => $PREFIX));
    Resque_Redis::prefix($PREFIX);
}
if($count > 1) {
    for($i = 0; $i < $count; ++$i) {
        $pid = Resque::fork();
        if($pid === false || $pid === -1) {
            $logger->log(Psr\Log\LogLevel::EMERGENCY, 'Could not fork worker {count}', array('count' => $i));
            die();
        }
        // Child, start the worker
        else if(!$pid) {
            $queues = explode(',', $QUEUE);
            $worker = new Resque_Worker($queues);
            $worker->setLogger($logger);
            $logger->log(Psr\Log\LogLevel::NOTICE, 'Starting worker {worker}', array('worker' => $worker));
            $worker->work($interval, $BLOCKING);
            break;
        }
    }
}
// Start a single worker
else {
    $queues = explode(',', $QUEUE);
    $worker = new Resque_Worker($queues);
    $worker->setLogger($logger);
    $PIDFILE = getenv('PIDFILE');
    if ($PIDFILE) {
        file_put_contents($PIDFILE, getmypid()) or
        die('Could not write PID information to ' . $PIDFILE);
    }
    $logger->log(Psr\Log\LogLevel::NOTICE, 'Starting worker {worker}', array('worker' => $worker));
    $worker->work($interval, $BLOCKING);
}

//************************
?>