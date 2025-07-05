<?php

namespace App\LogyModule\Model;

/**
 * Model logu pohybu po aplikaci
 */
class LogAccessModel extends \App\Model\AAModel
{
    const LOG_TABLE = "log.log_access";

    /**
     * Vrátí všechny záznamy z tabulky logu odeslaných zpráv 
     * @return type
     */
    public function findLogAccess() {
        return $this->dbpostgre->select("*")->from(self::LOG_TABLE, "log")->fetchAll();
    }   
    
}
?>
