<?php

namespace App\LekyModule\Model;

use App\Components\Model\Ciselnik;

class Leky extends \App\Model\AModel {

	/**
	 * Datasource pro DataGrid s areály
	 * @return type
	 */
	const LEKY = "LEKY",
			LEKY_VIEW = "AKESO_LEKY_VIEW",
			LEKY_EDIT = "AKESO_LEKY_EDIT",
			AKESO_LEKY = "AKESO_LEKY",
			POJISTOVNY = "AKESO_LEKY_POJISTOVNY",
			POJISTOVNY_DG = "AKESO_LEKY_POJISTOVNY_DG",
			UCINNA_LATKA = "AKESO_LEKY_UCINNALATKA",
			ORGANIZACE = "AKESO_LEKY_ORGANIZACE",
			DG_SKUP = "AKESO_LEKY_SPEC_DG_SKUP",
			CENA_VYROB = "AKESO_LEKY_CENAVYROBCE",
			MEDIOX = "[DWH_FONS].[dbo].[BEROUN_MEDIOX_CENY]",
			BIOSIMOLAR = "AKESO_LEKY_BIOSIMOLAR",
			WHERE_SADA = "ID_SADY = (SELECT ID_SADY FROM dbo.SADY WHERE (PLATNOST_OD <= cast(GETDATE() as date)) AND (cast(GETDATE() as date) <= PLATNOST_DO))";

	public function getDataSource($organizace = null, $history = null) { /* Historie kvůli tomu, že dřív to sloužilo k historii  */
		$select = $this->db->select("*")
				->from(self::LEKY_VIEW);

		if ($organizace) {
			$select->where("ORGANIZACE = %s", $organizace);
		}

		if (!$history) {
			$select->where("AKORD = 0");
		}
		return $select->fetchAll();
	}

	public function getDataSource_DG(string $id_leku) {
		return $this->db->select('ROW_NUMBER() OVER (ORDER BY ID_LEKY + 1) AS ID,[ID_LEKY],[ORGANIZACE],[POJISTOVNA],[DG_NAZEV],[VILP], CONVERT(nvarchar(20), DG_PLATNOST_OD, 104) as DG_PLATNOST_OD, CONVERT(nvarchar(20), DG_PLATNOST_DO, 104) as DG_PLATNOST_DO')
						->from(self::POJISTOVNY_DG)->as('dg1')
						->where('ID_LEKY = %s and (dg1.DG_PLATNOST_DO >= getdate() or dg1.DG_PLATNOST_DO is null) and NOT (dg1.POJISTOVNA = 0 AND EXISTS (SELECT 1 FROM AKESO_LEKY_POJISTOVNY_DG dg2 WHERE dg2.ID_LEKY = dg1.ID_LEKY AND dg2.DG_NAZEV = dg1.DG_NAZEV AND dg2.POJISTOVNA != 0))', $id_leku)->fetchAll();
	}

	public function get_setUcinnalatka($ID_LEK, $UCINNA_LATKA) {
		$this->db->query("MERGE INTO " . self::UCINNA_LATKA . " as UC USING (SELECT ID_LEKY = %s) AS lek ON lek.ID_LEKY = UC.ID_LEKY WHEN MATCHED THEN UPDATE SET %a", $ID_LEK, ['UCINNA_LATKA' => $UCINNA_LATKA], " WHEN NOT MATCHED THEN INSERT %v ;", ['ID_LEKY' => $ID_LEK, 'UCINNA_LATKA' => $UCINNA_LATKA]);
		return $this->db->select("UCINNA_LATKA")->from(self::UCINNA_LATKA)->where('ID_LEKY = %s', $ID_LEK)->fetchPairs("UCINNA_LATKA", "UCINNA_LATKA");
	}

	public function getHandle($ID_LEK, $SELECT) {
		return $this->db->select($SELECT)
						->from(self::LEKY)
						->where(self::WHERE_SADA . ' and ID_LEKY = %s', $ID_LEK)
						->fetchPairs($SELECT, $SELECT);
	}
	public function getHandleBIOSIMOLAR($ATC) {
		return $this->db->select('BIOSIMOLAR')
						->from(self::BIOSIMOLAR)
						->where('ATC = %s', $ATC)
						->fetchPairs('BIOSIMOLAR', 'BIOSIMOLAR');
	}
	public function getHandleMAX($ID_LEK) {
		return $this->db->select('(SELECT Max(maxvalue) FROM (VALUES (UHR1), (UHR2), (UHR3)) AS value(maxvalue)) as MAXVALUE')
						->from(self::LEKY)
						->where(self::WHERE_SADA . ' and ID_LEKY = %s', $ID_LEK)
						->fetchPairs('MAXVALUE', 'MAXVALUE');
	}

	public function getHandleMediox($ID_LEK, $SELECT) {
		return $this->db->select('top 1 ' . $SELECT)
						->from(self::MEDIOX)
						->where('ID_LEKY = %s', $ID_LEK)
						->orderBy('DATUM_IMPORTU')
						->desc()
						->fetchPairs($SELECT, $SELECT);
	}

	public function getHandleMulti($VALUES, $SELECT) {
		return $this->db->select($SELECT)
						->from(self::LEKY)
						->where(self::WHERE_SADA . " and ID_LEKY in %l ", $VALUES)
						->fetchPairs($SELECT, $SELECT);
	}

	public function getIdlek($ID_LEK) {
		return $this->db->select('ID_LEKY')
						->from(self::LEKY)
						->where(self::WHERE_SADA . ' and ID_LEKY like %s', $ID_LEK . '%')
						->fetchPairs('ID_LEKY', 'ID_LEKY');
	}

	/*    public function getOrganizace(){
	  return $this->db->select('ORGANIZACE as [key], ORGANIZACE as [value]')
	  ->from(self::ORGANIZACE);
	  } */

	public function getAtc_skup_data() {
		return $this->db->select("*")->from(self::DG_SKUP);
	}

public function getLeky($id) {
    return $this->db->select("*")
                    ->from(self::LEKY_VIEW)
                    ->where('ID_LEKY = %s', $id)
                    ->orderBy('ID_LEKY')
                    ->fetch();
}
	public function getAtc_skup($ATC1) {
		return $this->db->select("*")
						->from(self::DG_SKUP)
						->where('ATC1 = %s', $ATC1)
						->orderBy('ATC1')
						->fetch();
	}

	public function getPojistovny($id, $org) {
		return $this->db->select("*")
						->from(self::POJISTOVNY)
						->where('ID_LEKY = %s and ORGANIZACE = %s', $id, $org)
						->orderBy('ID_LEKY')
						->fetchAssoc('POJISTOVNA');
	}

	public function getPojistovny_DG($id, $org, $poj) {
		return $this->db->select("*")
						->from(self::POJISTOVNY_DG)
						->where('ID_LEKY = %s and ORGANIZACE = %s and POJISTOVNA = %i and (DG_PLATNOST_DO >= getdate() or DG_PLATNOST_DO is null)', $id, $org, $poj)
						->orderBy('ID_LEKY')
						->fetchAll();
	}

	public function getDg($values) {
		return $this->db->select('KOD_SKUP')
						->from(\App\CiselnikyModule\Presenters\DgPresenter::DG)
						->where('KOD_SKUP like %s', $values . '%')
						->fetchPairs('KOD_SKUP', 'KOD_SKUP');
	}

	public function getDg_suggestor() {
		return $this->db->select('KOD_SKUP')
						->from(\App\CiselnikyModule\Presenters\DgPresenter::DG)
						->fetchPairs('KOD_SKUP', 'KOD_SKUP');
	}

	public function insert_edit_pojistovny($value) {
		$value->VILP_PLATNOST_OD = $value->VILP_PLATNOST_OD ?? null;
		$value->VILP_PLATNOST_DO = $value->VILP_PLATNOST_DO ?? null;
		$this->db->query("MERGE INTO " . self::POJISTOVNY . " as poj USING (SELECT ID_LEKY = %s, ORGANIZACE = %s, POJISTOVNA = %i) AS spoj ON poj.ID_LEKY = spoj.ID_LEKY AND poj.ORGANIZACE = spoj.ORGANIZACE AND poj.POJISTOVNA = spoj.POJISTOVNA WHEN MATCHED THEN UPDATE SET ID_LEKY = %s, NASMLOUVANO_OD = %d, ORGANIZACE = %s, POJISTOVNA =%i, STAV = %s, RL = %s, SMLOUVA = %s, POZNAMKA = %s WHEN NOT MATCHED THEN INSERT (ID_LEKY, NASMLOUVANO_OD, ORGANIZACE, POJISTOVNA, STAV, RL, SMLOUVA, POZNAMKA) VALUES(%s,%d,%s,%i,%s,%s,%s,%s); ", $value->ID_LEKY, $value->ORGANIZACE, $value->POJISTOVNA, $value->ID_LEKY, $value->NASMLOUVANO_OD, $value->ORGANIZACE, $value->POJISTOVNA, $value->STAV, $value->RL, $value->SMLOUVA, $value->POZNAMKA, $value->ID_LEKY, $value->NASMLOUVANO_OD, $value->ORGANIZACE, $value->POJISTOVNA, $value->STAV, $value->RL, $value->SMLOUVA, $value->POZNAMKA);
	}

	public function insertLeky($value) {
		return $this->db->query("MERGE INTO " . self::AKESO_LEKY . " as lek USING (SELECT ID_LEKY = %s) AS id_leky ON lek.ID_LEKY = id_leky.ID_LEKY WHEN MATCHED THEN UPDATE SET ID_LEKY = %s, NAZ = %s, DOP = %s, SILA = %s, BALENI = %s, POZNAMKA = %s, UCINNA_LATKA = %s, BIOSIMOLAR = %s, ORGANIZACE = (%s), ATC = %s, ATC3 = %s, UHR1 = %f, UHR2 = %f, UHR3 = %f, CENA_FAKTURACE = %f, CENA_MAX = %f,  CENA_VYROBCE_BEZDPH = %f, CENA_SENIMED_BEZDPH = %f, CENA_MUS_PHARMA = %f, CENA_MUS_NC_BEZDPH = %f, CENA_MUS_NC = %f, UHRADA = %f, KOMPENZACE = %f, BONUS = %f WHEN NOT MATCHED THEN INSERT (ID_LEKY,NAZ,DOP,SILA,BALENI,POZNAMKA,UCINNA_LATKA,BIOSIMOLAR,ORGANIZACE,ATC,ATC3,UHR1,UHR2,UHR3,CENA_FAKTURACE,CENA_MAX, CENA_VYROBCE_BEZDPH, CENA_SENIMED_BEZDPH, CENA_MUS_PHARMA, CENA_MUS_NC_BEZDPH, CENA_MUS_NC, UHRADA, KOMPENZACE, BONUS) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,(%s),%s,%s,%f,%f,%f,%f,%f,%f,%f,%f,%f,%f,%f,%f,%f); ", $value->ID_LEKY, $value->ID_LEKY, $value->NAZ, $value->DOP, $value->SILA, $value->BALENI, $value->POZNAMKA, $value->UCINNA_LATKA, $value->BIOSIMOLAR, $value->ORGANIZACE, $value->ATC, $value->ATC3, $value->UHR1, $value->UHR2, $value->UHR3, $value->CENA_FAKTURACE, $value->CENA_MAX, $value->CENA_VYROBCE_BEZDPH, $value->CENA_SENIMED_BEZDPH, $value->CENA_MUS_PHARMA, $value->CENA_MUS_NC_BEZDPH, $value->CENA_MUS_NC, $value->UHRADA, $value->KOMPENZACE, $value->BONUS, $value->ID_LEKY, $value->NAZ, $value->DOP, $value->SILA, $value->BALENI, $value->POZNAMKA, $value->UCINNA_LATKA, $value->BIOSIMOLAR, $value->ORGANIZACE, $value->ATC, $value->ATC3, $value->UHR1, $value->UHR2, $value->UHR3, $value->CENA_FAKTURACE, $value->CENA_MAX, $value->CENA_VYROBCE_BEZDPH, $value->CENA_SENIMED_BEZDPH, $value->CENA_MUS_PHARMA, $value->CENA_MUS_NC_BEZDPH, $value->CENA_MUS_NC, $value->UHRADA, $value->KOMPENZACE, $value->BONUS);
	}

	public function insertAtc($value) {
		return $this->db->query("MERGE INTO " . self::DG_SKUP . " as lek USING (SELECT ATC1 = %s) AS dg_skup ON lek.ATC1 = dg_skup.ATC1 WHEN MATCHED THEN UPDATE SET ATC1 = %s, POPIS = %s, LECBA = %s, PLATNOST_OD = %d, PLATNOST_DO = %d WHEN NOT MATCHED THEN INSERT (ATC1,POPIS,LECBA,PLATNOST_OD, PLATNOST_DO) VALUES(%s,%s,%s,%d,%d); ", $value->ATC1, $value->ATC1, $value->POPIS, $value->LECBA, $value->PLATNOST_OD, $value->PLATNOST_DO, $value->ATC1, $value->POPIS, $value->LECBA, $value->PLATNOST_OD, $value->PLATNOST_DO);
	}

	public function insert_edit_pojistovny_dg($dg) {
		return $this->db->query("MERGE INTO " . self::POJISTOVNY_DG . " as poj USING (SELECT ID_LEKY = %s, ORGANIZACE = %s, POJISTOVNA = %i, DG_NAZEV = %s) AS spoj ON poj.ID_LEKY = spoj.ID_LEKY AND poj.ORGANIZACE = spoj.ORGANIZACE AND poj.POJISTOVNA = spoj.POJISTOVNA AND poj.DG_NAZEV = spoj.DG_NAZEV WHEN MATCHED THEN UPDATE SET ID_LEKY = %s, ORGANIZACE = %s, POJISTOVNA = %i, DG_NAZEV = %s, VILP = %b, DG_PLATNOST_OD = %d, DG_PLATNOST_DO = %d WHEN NOT MATCHED THEN INSERT (ID_LEKY, ORGANIZACE, POJISTOVNA, DG_NAZEV, VILP, DG_PLATNOST_OD, DG_PLATNOST_DO) VALUES(%s,%s,%i,%s,%b,%d,%d);", $dg->ID_LEKY, $dg->ORGANIZACE, $dg->POJISTOVNA, $dg->DG_NAZEV, $dg->ID_LEKY, $dg->ORGANIZACE, $dg->POJISTOVNA, $dg->DG_NAZEV, $dg->VILP, $dg->DG_PLATNOST_OD, $dg->DG_PLATNOST_DO, $dg->ID_LEKY, $dg->ORGANIZACE, $dg->POJISTOVNA, $dg->DG_NAZEV, $dg->VILP, $dg->DG_PLATNOST_OD, $dg->DG_PLATNOST_DO);
	}
	public function set_pojistovny_dg($values){
		return $this->db->insert(self::POJISTOVNY_DG, $values)->execute();
	}
	public function set_pojistovny_dg_edit($values){
		return $this->db->update(self::POJISTOVNY_DG, ['VILP'=>$values['VILP'],'DG_PLATNOST_OD'=> $values['DG_PLATNOST_OD'],'DG_PLATNOST_DO'=> $values['DG_PLATNOST_DO']])->where("ID_LEKY = %s and ORGANIZACE = %s and POJISTOVNA = %s and DG_NAZEV = %s", $values['ID_LEKY'], $values['ORGANIZACE'],$values['POJISTOVNA'], $values['DG_NAZEV'])->execute();
	}
	public function unset_pojistovny_dg($values){
		return $this->db->delete(self::POJISTOVNY_DG)->where("ID_LEKY = %s and ORGANIZACE = %s and POJISTOVNA = %s", $values['ID_LEKY'], $values['ORGANIZACE'],$values['POJISTOVNA'])->execute();
	}
	public function insertCena($value, $table, $cena, $cenaValue) {
		$this->db->query("MERGE INTO " . $table . " as CEN USING (SELECT ID_LEKY = %s) AS idleks ON idleks.ID_LEKY = cen.ID_LEKY WHEN MATCHED THEN UPDATE SET AKTIVNI = 0;", $value->ID_LEKY);
		return $this->db->insert($table, ['ID_LEKY' => $value->ID_LEKY, $cena => $cenaValue, 'DATUM' => date('Y-m-d'), 'AKTIVNI' => 1])->execute();
	}
	public function getDGSkup($id) {
		return $this->db->select("DG_NAZEV,VILP,DG_PLATNOST_OD,DG_PLATNOST_DO")
						->from(self::POJISTOVNY_DG)
						->where('ID_LEKY IN %in and (DG_PLATNOST_DO >= getdate() or DG_PLATNOST_DO is null)', $id)
						->orderBy('ID_LEKY')
						->groupBy("ID_LEKY,DG_NAZEV,VILP,DG_PLATNOST_OD,DG_PLATNOST_DO")
						->fetchAll();
	}
	public function getDGSkupPK($id,$value) {
		return $this->db->select($value)
						->from(self::POJISTOVNY_DG)
						->where('ID_LEKY IN %in and POJISTOVNA != 0 and (DG_PLATNOST_DO >= getdate() or DG_PLATNOST_DO is null)', $id)
						->orderBy('ID_LEKY')
						->groupBy("ID_LEKY,%n",$value)
						->fetchPairs();
	}

}
