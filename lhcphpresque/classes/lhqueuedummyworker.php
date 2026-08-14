<?php

/**
 * Example of worker usage
 * 
 * */
#[\AllowDynamicProperties]
class erLhcoreClassLHCDummyWorker {
     
    public function perform()
    {
        $db = ezcDbInstance::get();
        $db->reconnect(); // Because it timeouts automatically, this calls to reconnect to database, this is implemented in 2.52v

        $stmt = $db->query('SELECT 1');
        if ($stmt instanceof PDOStatement) {
            $stmt->closeCursor();
        }

        $db->beginTransaction();

        $stmt = $db->prepare('SELECT id from lh_users where id = 1 FOR UPDATE');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if ($this->args['delay'] != 5) {
            ezcDbInstance::reset();
            sleep($this->args['delay']);
        } else {
            erLhcoreClassModelChatConfig::$disableCache = true;
            erLhcoreClassModelChatConfig::fetch('elasticsearch_options');
        }

        print_r($rows);
        echo "finished - ",$this->args['delay'],"\n";

        $db->commit();

        if ($this->args['delay'] == 5) {
            throw new Exception('dummy exception');
        }


        print_r('TABLES - FOUND - ' . count($rows));
    }
}

?>