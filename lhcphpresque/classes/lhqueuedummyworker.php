<?php

/**
 * Example of worker usage
 * 
 * */
class erLhcoreClassLHCDummyWorker {
     
    public function perform()
    {
        $db = ezcDbInstance::get();
        $db->reconnect(); // Because it timeouts automatically, this calls to reconnect to database, this is implemented in 2.52v
        
        $stmt = $db->prepare('SHOW TABLES');           
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        print_r($rows);
    }
}

?>