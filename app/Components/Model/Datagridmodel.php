<?php
namespace App\Components\Model;

use	App\Model\AModel;

/**
 * Třída pro práci s číselníky
 *
 * @author Ing. Vítek Šmíd
 */
class Datagridmodul extends AModel
{

	const GRID = 'AKESO_LEKY_GRID_NASTAVENI';
	/**
	 * Summary of setDbGridUserSettings
	 * @param string $gridName
	 * @param array $values
	 * @return int
	 */
	public function setDbGridUserSettings(string $gridName, array $values): ?int{
		$values["NAZEV"] = $gridName;
		return $this->db->query("MERGE INTO %n as sett", self::GRID
					, "USING (SELECT UZIVATEL_ID = %i, NAZEV = %s)", $values["UZIVATEL_ID"], $values["NAZEV"]
					, "AS pr ON sett.UZIVATEL_ID = pr.UZIVATEL_ID AND sett.NAZEV = pr.NAZEV"
					, "WHEN MATCHED THEN UPDATE SET JSON = %s, AKTIVNI = 1, DATUM = %s, ID_U_ZALOZIL = %s", $values["JSON"], $values["DATUM"], $values["ID_U_ZALOZIL"]
					, "WHEN NOT MATCHED THEN INSERT %v;", $values)
					->getRowCount();
	}
	/**
	 * Summary of getDbGridUserSettings
	 * @param string $gridName
	 * @return mixed
	 */
	public function getDbGridUserSettings(string $gridName) : ?array {
		$json = $this->db->select("JSON")
						->from(self::GRID)
						->where("UZIVATEL_ID = %i", $this->user->getId())
						->and("NAZEV = %s", $gridName)
						->and("AKTIVNI = 1")
						->orderBy("ID")
						->fetchSingle();
		
		return $json ? json_decode($json, true) : NULL;
	}
}
