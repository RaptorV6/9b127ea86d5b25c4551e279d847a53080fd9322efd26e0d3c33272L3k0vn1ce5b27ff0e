<?php
namespace App\Model;

    abstract class AAModel {
        
        /** @var \Dibi\Connection*/
        protected $db;
        
        /** @var \Dibi\Connection*/
        protected $dbpostgre;
      
        /** @var \Nette\Security\User*/
        protected $user;
        
        /** @var Array|ArrayHash*/
        protected $params;
        
        use \Nette\SmartObject;
        
        public function __construct(\Nette\Security\User $user, \Dibi\Connection $db,\Dibi\Connection $dbpostgre, Array $params) {
            $this->db = $db;
            $this->dbpostgre = $dbpostgre;
            $this->params = \Nette\Utils\ArrayHash::from($params);
            $this->user = $user;
        }
        
        
          
        public function __destruct() {
            $this->db->disconnect(); 
        }
    }

?>