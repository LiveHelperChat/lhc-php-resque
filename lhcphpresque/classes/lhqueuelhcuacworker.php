<?php

/**
 * php-resque worker to update active operators chats count
 * */
class erLhcoreClassLHCUACWorker {

    public function perform()
    {
        // We want small delay so previous transactions from which we got event would have time to finish up
        sleep(1);

        $db = ezcDbInstance::get();
        $db->reconnect(); // Because it timeouts automatically, this calls to reconnect to database, this is implemented in 2.52v

        erLhcoreClassChat::updateActiveChats($this->args['user_id'], true);
    }
}

?>