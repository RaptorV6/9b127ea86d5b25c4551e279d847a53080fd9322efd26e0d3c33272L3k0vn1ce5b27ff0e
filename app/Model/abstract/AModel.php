<?php
namespace App\Model;

    abstract class AModel {
        
        /** @var \Dibi\Connection*/
        protected $db;
        
        /** @var \Nette\Security\User*/
        protected $user;
        
        /** @var Array|ArrayHash*/
        protected $params;
        
        use \Nette\SmartObject;
        
        public function __construct(\Nette\Security\User $user, \Dibi\Connection $db, Array $params) {
            $this->db = $db;
            $this->params = \Nette\Utils\ArrayHash::from($params);
            $this->user = $user;
        }
        
        
          
        public function __destruct() {
            $this->db->disconnect(); 
        }
    }

?>