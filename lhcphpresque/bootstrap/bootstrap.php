<?php

class erLhcoreClassExtensionLhcphpresque
{

    public function __construct()
    {}

    public function run()
    {
        $this->registerAutoload();
    }
    
    public function registerAutoload()
    {
    	include 'extension/lhcphpresque/vendor/autoload.php';
    	
        spl_autoload_register(array(
            $this,
            'autoload'
        ), true, false);
    }
        
    public function enqueue($queue, $class, $params) {

        if (self::$resqueSet == false) {
            self::$resqueSet = true;
            Resque::setBackend($this->settings['connect_resque'], $this->settings['connect_resque_db']);
        }

        Resque::enqueue($queue, $class, $params);
    }
    
    public function autoload($className)
    {
        $classesArray = array(
            'erLhcoreClassLHCDummyWorker' => 'extension/lhcphpresque/classes/lhqueuedummyworker.php'
        );
        
        if (key_exists($className, $classesArray)) {
            include_once $classesArray[$className];
        }
    }

    public static function getSession()
    {
        if (! isset(self::$persistentSession)) {
            self::$persistentSession = new ezcPersistentSession(ezcDbInstance::get(), new ezcPersistentCodeManager('./extension/lhcphpresque/pos'));
        }
        return self::$persistentSession;
    }

    public function __get($var)
    {
        switch ($var) {
            
            case 'settings':
                $this->settings = include ('extension/lhcphpresque/settings/settings.ini.php');
                return $this->settings;
                break;
            
            default:
                ;
                break;
        }
    }

    private static $resqueSet = false;

    private static $persistentSession;
}


