<?php

namespace App\LogyModule\Model;

/**
 * Model logu změn v databázi
 */
class LogDB extends \App\Model\AAModel
{
	const LOG_VIEW = "log.log_dbchanges";

    public function findLogDBByID($id) {
        return
            $this->dbpostgre->select("*")
            ->from(self::LOG_VIEW)
            ->where("id = %i", $id);
    }

    public function findLogDB() {
        return
            $this->dbpostgre->select("*")->from(self::LOG_VIEW);
       
    }   

}
?>
