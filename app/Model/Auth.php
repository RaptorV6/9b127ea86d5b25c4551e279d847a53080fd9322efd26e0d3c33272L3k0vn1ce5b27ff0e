<?php
namespace App\Model;
    /**
     * Třída pro autorizaci a autentizaci
     */

    class Auth extends AModel {
        const USER = 'public.user',
              LOGIN_LOG = 'log.log_access';
        
        public $userID;

        
        public function insertAcessLog($osobni_cislo){
            $this->db->insert(self::LOGIN_LOG,["user_id" => $this->user->getIdentity()->id, "time" => date('d.m.Y H:i:s'), "uri" => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], "ip" => $_SERVER['REMOTE_ADDR'], "user" => $this->user->getId() ])->execute();
        }

        /**
         * Přihlásí registrovaného uživatele - formulář FirstPage
         * @param string $osobni_cislo Osobní číslo uživatele
         * @param string $password heslo
         * @return boolean je-li přihlášení úspěšné
         */
        public function login(string $osobni_cislo, string $password) {
			$dbData = $this->db->select("id as identifikator,osobni_cislo,jmeno,prijmeni,heslo,prava,modul_poj,modul_fin,modul_lek,preferovana_organizace")->from(self::USER)->where("osobni_cislo = %s and active = true", $osobni_cislo)->orderBy("osobni_cislo")->fetch();
			if($dbData) {
                $dbPassword = $dbData->heslo;
                if(crypt($password, $dbPassword)==$dbPassword) {
                    $this->userLogin($dbData);
                    return true;
                }
            }
            return false;
        }
        
        private function userLogin($dbData)
        {
           unset($dbData->heslo);
           return $this->user->login(new \Nette\Security\Identity($dbData->osobni_cislo, $dbData->prava, $dbData));
        }
  
        /**
         * Odhlásí přihlášeného registrovaného uživatele
         */
        public function logout() {
            session_destroy();
            $this->user->logout();
        }
            
        /**
         * Zaloguje přístup do aplikace
         * @param type $email
         * @return boolean
         */
        private function accessLog($email){
            $res = $this->db->insert("access_log", ["email"=>$email])->execute(\dibi::IDENTIFIER);
            return $res ? true : false;
        }
    }
    

?>