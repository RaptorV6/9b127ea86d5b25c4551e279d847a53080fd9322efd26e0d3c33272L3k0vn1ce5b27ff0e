<?php

namespace App\LogyModule\Model;

use App\Components\Model\Ciselnik;

class Logy extends \App\Model\AAModel
{
    /**
     * Datasource pro DataGrid s areály
     * @return type
     */
    const CHANGE = "log.log_dbchanges";
    public function insertLog($table,$value,$user){
        unset($value->pojistovna);
$select = $this->db->select("*")->from($table)->where("ID_LEKY = %s", $value->ID_LEKY)->orderBy("ID_LEKY")->fetch();
        $select = $select ?? "Null";
        $data  = array("datum" => date('Y-m-d H:i:s'), "userid" => $user, "tablename" => $table, "datavaluesold" => json_encode($select), "datavaluesnew" => json_encode($value), "ip" => $_SERVER['REMOTE_ADDR']);
        $this->dbpostgre->insert(self::CHANGE, $data)->execute();
                    
    }
    public function insertUserLog($table,$value,$user){
        if((int)$value->id == false)
        {
            $value->id = null;
        }
        $select = $this->dbpostgre->select("*")->from($table)->where("id = %i", $value->id)->orderBy("id")->fetch();
        $select = $select ?? "Null";
        $data  = array("datum" => date('Y-m-d H:i:s'), "userid" => $user, "tablename" => $table, "datavaluesold" => json_encode($select), "datavaluesnew" => json_encode($value), "ip" => $_SERVER['REMOTE_ADDR']);
        $this->dbpostgre->insert(self::CHANGE, $data)->execute();
    }
}
