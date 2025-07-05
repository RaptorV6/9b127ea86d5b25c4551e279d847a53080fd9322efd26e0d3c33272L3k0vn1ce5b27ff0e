<?php

namespace App\UserModule\Model;

use App\Components\Model\Ciselnik;

class User extends \App\Model\AModel
{
    /**
     * Datasource pro DataGrid s areály
     * @return type
     */
    const USER = "public.user";
    public function getDataSource(){
        return $this->db->select("*")
                        ->from(self::USER)
                        ->fetchAll();
    }
    /*public function setPassword($values){
        return $this->db->update(self::USER, $values)->execute();

    }*/
 public function getUser($id) {
     return  $this->db->select("*")
                        ->from(self::USER)
                        ->where('id = %s', $id)
                        ->orderBy('id')
                        ->fetch();
 }
  public function getUserKontrola($osobni_cislo) {
     return  $this->db->select("*")
                        ->from(self::USER)
                        ->where('osobni_cislo = %s', $osobni_cislo)
                        ->orderBy('osobni_cislo')
                        ->fetch();
 }
 public function setUser($values) {
     return $this->db->insert(self::USER, $values)->execute();
 }
 public function updateUser($values){
     return $this->db->update(self::USER, $values)->where('id = %i',$values->id)->execute();
 }
 public function deleteUser($id){
     return $this->db->delete(self::USER)->where('id = %i',$id)->execute();
 }
}
