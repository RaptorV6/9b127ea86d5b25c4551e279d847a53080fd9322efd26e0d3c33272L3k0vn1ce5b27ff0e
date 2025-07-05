<?php

namespace App\LogyModule\Model;

/**
 * Model logu přihlášení
 */
class LogLoginModel extends \App\Model\AAModel
{
    const LOG_TABLE = "log.log_login";

    public function find() {
        return $this->dbpostgre->select('L.id, L.datumcas, L.ip, U.username, AR.username as admin_relogin')
		                ->from(self::LOG_TABLE, 'L')
						->join(self::USERS_VIEW, 'U')->on('L.uzivid=U.id')
                                                ->leftJoin(self::USERS_VIEW, 'AR')->on('L.adminid_relogin=AR.id')
						->fetchAll();
    }

}
?>
