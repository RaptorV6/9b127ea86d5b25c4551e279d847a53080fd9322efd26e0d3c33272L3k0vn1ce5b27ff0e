<?php

namespace App\LogyModule\Model;

/**
 * Model logu výjimek
 */
class LogExceptionsModel extends \App\Model\AAModel
{
    const LOG_TABLE = "log.log_exceptions";

    /**
     * Vrátí všechny záznamy z tabulky logu výjimek
     * @return type
     */
    public function findLogExceptions() {
        return $this->dbpostgre->select("*")->from(self::LOG_TABLE, "log");
    }
    
    /**
     * Vrátí všechny typy výjimek z tabulky logu výjimek
     * @return type
     */
    public function findExceptionTypes() {
        return $this->dbpostgre->select("type")->from(self::LOG_TABLE)->groupBy("type");
    }
    
}
?>
