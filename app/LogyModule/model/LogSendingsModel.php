<?php

namespace App\LogyModule\Model;

/**
 * Model logu odeslaných zpráv
 */
class LogSendingsModel extends \App\Model\AAModel
{
    const LOG_TABLE = "log.log_sendings";

    /**
     * Vrátí všechny záznamy z tabulky logu odeslaných zpráv 
     * @return type
     */
    public function findLogSendings() {
        return $this->dbpostgre->select("*")->from(self::LOG_TABLE);
    }   
    
}
?>
