<?php

class erLhcoreClassRedis extends Credis_Client{

	private static $instance = null;
	
	public function __construct() {
	    
	    $settings = erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionLhcphpresque')->settings;
	    
	    $hostParts = explode(':', $settings['connect_resque']);
	    $port = isset($hostParts[1]) ? $hostParts[1] : 6379;
	  	    
		parent::__construct($hostParts[0],$port,null,'',$settings['connect_resque_db']);	
	}
	
    public static function instance() {
    	if (is_null(self::$instance)) {
    		self::$instance = new self();
    	}
    	return self::$instance;
    }
}


?>