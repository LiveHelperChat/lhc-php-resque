<?php
#[\AllowDynamicProperties]
class erLhcoreClassExtensionLhcphpresque
{
    public function __construct()
    {}

    public function run()
    {
        $this->registerAutoload();

        if ($this->settings['uac'] === true) {
            $dispatcher = erLhcoreClassChatEventDispatcher::getInstance();
            $dispatcher->listen('chat.update_active_chats', array($this, 'updateActiveCounter'));
        }
    }
    
    public function registerAutoload()
    {
    	include 'extension/lhcphpresque/vendor/autoload.php';
    	
        spl_autoload_register(array(
            $this,
            'autoload'
        ), true, false);
    }

    public function updateActiveCounter($params) {
        $this->enqueue('lhc_uac_queue', 'erLhcoreClassLHCUACWorker', array('user_id' => $params['user_id']));
    }

    public function enqueue($queue, $class, $params) {

        if (self::$resqueSet == false) {
            self::$resqueSet = true;
            Resque::setBackend($this->settings['connect_resque'], $this->settings['connect_resque_db']);
        }

        Resque::enqueue($queue, $class, $params);
    }
    
    public function reloadRedisFailedClasses($classes = array())
    {
        if (count($classes) > 0) {
            $items = erLhcoreClassRedis::instance()->lrange('resque:failed',0, 100);
            	
            foreach ($items as $key => $item) {
                	
                $jobData = json_decode($item,true);
                $time = strtotime($jobData['failed_at']);
                	
                // Delete older jobs than 7 days
                if (time() > $time+(7*24*3600)) {
                    erLhcoreClassRedis::instance()->lrem('resque:failed',1,$item);
                }
                	
                if (isset($jobData['payload']['class']) && in_array($jobData['payload']['class'], $classes))
                {
                    $this->enqueue($jobData['queue'], $jobData['payload']['class'], $jobData['payload']['args'][0]);
                    erLhcoreClassRedis::instance()->lrem('resque:failed',1,$item);
                }
            }
        }
    }        
    
    public function autoload($className)
    {
        $classesArray = array(
            'erLhcoreClassLHCDummyWorker' => 'extension/lhcphpresque/classes/lhqueuedummyworker.php',
            'erLhcoreClassLHCUACWorker' => 'extension/lhcphpresque/classes/lhqueuelhcuacworker.php',
            'erLhcoreClassRedis' => 'extension/lhcphpresque/classes/lhpredis.php'
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
            
            case 'is_enabled_admin':
                $this->is_enabled_admin = $this->settings['automated_hosting'] == false || ($this->settings['automated_hosting'] == true && in_array('instance', erConfigClassLhConfig::getInstance()->getSetting('site', 'extensions')));
                return $this->is_enabled_admin;
                break;
            
            default:
                ;
                break;
        }
    }

    private static $resqueSet = false;

    private static $persistentSession;
}


