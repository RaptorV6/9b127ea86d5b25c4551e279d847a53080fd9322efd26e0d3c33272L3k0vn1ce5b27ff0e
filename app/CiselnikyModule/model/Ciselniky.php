<?php

namespace App\CiselnikyModule\Model;

use App\Components\Model\Ciselnik;

class Ciselniky extends \App\Model\AModel
{
	/**
     * Datasource pro DataGrid s areály
     * @return type
     */
	
    public function getDataSource($table){
        return $this->db->select("*")
                        ->from($table);
    }
	public function getEditView($id, $table) {
		return $this->db->select("*")->from($table)->where('id = %i',$id)->orderBy('ID')->fetch();
	}
	public function setInsert($values,$table) {
	   return $this->db->insert($table, $values)->execute();
	}
	public function setupdate($values,$table,$id){
		return $this->db->update($table, $values)->where('ID = %i',$id)->execute();
	}
	public function getsada() {
		return $this->db->select('ID_SADY')->from('dbo.SADY')->orderBy('ID_SADY')->where('(PLATNOST_OD <= cast(GETDATE() as date)) AND (cast(GETDATE() as date) <= PLATNOST_DO)')->fetchSingle();
	}
}
