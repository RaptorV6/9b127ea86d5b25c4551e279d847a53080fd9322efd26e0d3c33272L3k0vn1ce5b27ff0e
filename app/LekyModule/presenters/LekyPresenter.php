<?php

namespace App\LekyModule\Presenters;

use Nette\Application\UI\Form,
	Nette\Application\UI\Multiplier,
	Ublaboo\DataGrid\DataGrid;
use App\LekyModule\Model\Leky;
use Ublaboo\DataGrid\AkesoGrid;

class LekyPresenter extends \App\Presenters\SecurePresenter {

	/** @var \App\LekyModule\Grids\LekyGridFactory @inject */
	public $GridFactory;

	/** @var \App\LekyModule\Forms\LekyFormFactory @inject */
	public $FormFactory;

	/** @var \App\LekyModule\Model\Leky @inject */
	public $BaseModel;

	/** @var \App\LogyModule\Model\Logy @inject */
	public $LogyModel;

	const STAV = array(
			'' => '-- Vybere te stav --', /* Černá */
			'Nasmlouváno' => 'Ano/Nasmlouváno', /* Zelený   */
			'Čeká se' => 'Ne/Čeká se', /* Oranžový */
			'Nezadáno' => 'Ne/Nezadáno',
			'Zamítnuto' => 'Ne/Zamítnuto'), /* Tmavě červená */

			/* 'Nenasmlouváno' => 'Ne/Nenasmlouváno',		 /* Červená 
			  'Není nasmlouváno' => 'Ne/Není nasmlouváno'),/* Odstín zelený */

			POJISTOVNY = array('111' => '111', '201' => '201', '205' => '205', '207' => '207', '209' => '209', '211' => '211', '213' => '213'),
			ORGANIZACE = ["NH" => "Hořovice", "RNB" => "Beroun", "MUS" => "Pardubice", "DCNH" => "Diagnostické centrum nemocnice Hořovice"],
			ORGANIZACE_VISIBLE_NULL = ["0", "NH", "RNB", "MUS", "DCNH"],
			ORGANIZACE_VISIBLE = ["NH", "RNB", "MUS", "DCNH"],
			REVIZAK = ["" => "-- Vyber te možnost -- ", "povolení RL- žádanka §16", "epikríza/info pro RL"],
			SMLOUVY = ["" => "-- Vyber te smlouvu -- ", "VZP/ ČPZP / OZP Zvláštní smlouva na dobu neurčitou – spektrum léčiv a limitace sjednávána nově na aktuální rok", "ZPŠ/ RBP/ Zvláštní smlouva na dobu určitou (rok) s automatickou prolongací o další 1 rok; limitace nesjednávána, spektrum léčiv  zachováváno po dobu platnosti ZS", "VOZP / Zvláštní smlouva na dobu určitou (rok) s automatickou prolongací o další 1 rok – spektrum léčiv a limitace sjednávána nově na aktuální rok", "ZPMV / Zvláštní smlouva formou dodatku k Smlouvě na dobu určitou (rok) obsahuje spektrum léčiv i limitace (Kč)- vše sjednáváno nově na aktuální rok"],
			ORGANIZACE_SELECT = [NULL => "Vše", "NH" => "Hořovice", "RNB" => "Beroun", "MUS" => "Pardubice", "DCNH" => "Diagnostické centrum nemocnice Hořovice", "BEZ_ORG" => "Není zadaná organizace"],
			TRUEORFALSE = [ "" => "Ne", "0" => "Ne", "1" => "Ano"];

	public function startup() {
		parent::startup();
	}

	protected function createComponentLekyDataGrid(string $name) {
		$grid = new AkesoGrid($this, $name);
		$session = $this->getComponent('lekyDataGrid');
		if ($this->user->getIdentity()->preferovana_organizace !== null && $session->getSessionData('ORGANIZACE')=== null) {
			$defaultHodnoty = array_intersect(explode(', ', $this->user->getIdentity()->preferovana_organizace), self::ORGANIZACE_VISIBLE);

		} else {
			$defaultHodnoty = $session->getSessionData('ORGANIZACE');
		}

		$this->GridFactory->setLekyGrid($grid, $this->user->getIdentity()->prava, $this->user->getIdentity()->modul_poj,$defaultHodnoty);
		
		$grid->setItemsDetail(true, "ID_LEKY")
			->setClass("btn btn-primary btn-sm ajax")
			->setTitle("Diagnostické skupiny")
			->setText("DG")
			->setIcon("arrow-down")
			->setTemplateParameters(["ID_LEKY"=>"ID_LEKY"])
			->setType("template")
			->setTemplate(__DIR__."/../templates/Leky/itemDetail.latte");

		$grid->setDataSource($this->BaseModel->getDataSource($grid->getSessionData()->lekarnaVyber, $grid->getSessionData()->histori));
		return $grid;
	}
	
	public function createComponentDGDataGrid(string $name): Multiplier{
    return new Multiplier(function ($ID_LEKY) {

        $grid = new DataGrid(null, $ID_LEKY);
        $this->GridFactory->setDGGrid($grid, $ID_LEKY);
        $grid->setDataSource($this->BaseModel->getDataSource_DG($ID_LEKY));
        
        $grid->getInlineAdd()->onSubmit[] = function(\Nette\Utils\ArrayHash $values): void {
            echo "<h1>🔥 OFICIÁLNÍ INLINE ADD HANDLER</h1>";
            var_dump($values);
            die();
            
            $this->BaseModel->set_pojistovny_dg($values);
            $this->flashMessage("Vkládání do databáze proběhlo v pořádku", 'success');
            $this->redirect('this');
        };
        
        $grid->getInlineEdit()->onSubmit[] = function($id, $values): void {
            echo "<h1>🔥 OFICIÁLNÍ INLINE EDIT HANDLER SPUŠTĚN!</h1>";
            echo "<h2>ID: $id</h2>";
            echo "<h2>VALUES:</h2>";
            var_dump($values);
            echo "<h2>Tracy Bar info:</h2>";
            \Tracy\Debugger::barDump($values, 'Oficiální Edit Values');
            \Tracy\Debugger::barDump($id, 'Oficiální Edit ID');
            die();
            
            $this->BaseModel->set_pojistovny_dg_edit($values);
            $this->flashMessage("Editace proběhla v pořádku", 'success');
            $this->redirect('this');
        };
        
        return $grid;
    });
}

	protected function createComponentLekyForm(string $name) {
		$form = new \Nette\Application\UI\Form($this, $name);
		$lek = $this->BaseModel->getLeky($this->getParameter('ID_LEKY'));

		if (isset($lek->ORGANIZACE)) {
			$lek->ORGANIZACE = explode(", ", $lek->ORGANIZACE);
		}
		$lek['POJ'] = [];
		$values = '';
		//&& isset($lek->ORGANIZACE);

		foreach (\App\LekyModule\Presenters\LekyPresenter::ORGANIZACE_VISIBLE as $org) {
			$lek[$org] = \Nette\Utils\ArrayHash::from($this->BaseModel->getPojistovny($this->getParameter('ID_LEKY'), $org));
			if (isset($lek->$org)) {
				foreach ($lek->$org as $value => $key) {
					if ($lek[$org][$value]['RL'] != '' && ($lek[$org][$value]['RL'] == 1 || $lek[$org][$value]['RL'] == 0)) {
						$lek[$org][$value]['Revizak'] = true;
					}else{
                                            $lek[$org][$value]['Revizak'] = false;
                                        }
					$lek[$org][$value]['DG'] = $this->BaseModel->getPojistovny_DG($this->getParameter('ID_LEKY'), $org, $value);
					if (!in_array($value, $lek['POJ'])) {
						$values = $values . ", " . $value;
					}
				}
			}
		}
		if ($values) {
			$lek['POJ'] = explode(', ', trim($values, ", "));
		}

		$this->FormFactory->setLekyForm($form);
		$form->onError[] = function ($form) {
			self::processFormErrors($form, $this);
		};
		if (!empty($lek->ORGANIZACE)) {
			$lek->ORGANIZACE = array_filter($lek->ORGANIZACE);
		}
		$form->setDefaults($lek);
		$form->addGroup();
		$form->addSubmit('send', 'Uložit')
				->setHtmlAttribute('class ', 'btn btn-success button btn-block');

		//$form->onValidate[] = [$this, "lekyFormValid"];
		$form->onSuccess[] = [$this, "lekyFormSucceeded"];
		return $form;
	}

	public function renderDefault() {
		$this->template->nadpis = 'přehled';
	}

	public function renderNew() {
		$session = $this->getComponent('lekyDataGrid');
		$savedata = $this->getComponent('lekyForm');
		if ($this->user->getIdentity()->preferovana_organizace !== null && $session->getSessionData('ORGANIZACE')=== null) {
			$defaultHodnoty = array_intersect(explode(', ', $this->user->getIdentity()->preferovana_organizace), self::ORGANIZACE_VISIBLE);

		} else {
			$defaultHodnoty = $session->getSessionData('ORGANIZACE');
		}
		$savedata->setDefaults(array('ID_LEKY' => $session->getSessionData('ID_LEKY'), 'NAZ' => $session->getSessionData('NAZ'), 'ATC3' => $session->getSessionData('ATC3'), 'ATC1' => $session->getSessionData('ATC1'), 'ATC' => $session->getSessionData('ATC'), 'ORGANIZACE' => $defaultHodnoty));
		$this->template->nadpis = 'Přidání léku';
		$this->template->pojistovny = self::POJISTOVNY;
		$this->template->organizace = self::ORGANIZACE_VISIBLE;
		$this->setView('edit');
	}

	public function renderHromad($id) {
		$idparametr = explode(",", $this->getParameter('id'));
		$idparametr = preg_replace("/[^a-zA-Z 0-9]+/", "", $idparametr);
		$this->template->nadpis = 'hromadná změna stavu pojišťoven';
		$this->template->id = implode(',', $idparametr);
		$this->template->pojistovny = self::POJISTOVNY;
		$this->template->organizace = self::ORGANIZACE_VISIBLE;
	}

	public function renderHromadiag($id) {
		$idparametr = explode(",", $this->getParameter('id'));
		$idparametr = preg_replace("/[^a-zA-Z 0-9]+/", "", $idparametr);
		$this->template->nadpis = 'hromadná změna diagnostické skupiny';
		$this->template->id = implode(',', $idparametr);
		$this->template->pojistovny = self::POJISTOVNY;
		$this->template->organizace = self::ORGANIZACE_VISIBLE;
	}
	public function renderEdit() {
		$this->template->nadpis = 'editace léku';
		$this->template->pojistovny = self::POJISTOVNY;
		$this->template->organizace = self::ORGANIZACE_VISIBLE;
	}

	public function actionWeb($ID_LEKY) {
		$this->redirectUrl('https://prehledy.sukl.cz/prehled_leciv.html#/leciva/' . $ID_LEKY);
	}

	private function mulityValues($values, $get_org, $set_org) {
		foreach ($values[$set_org]['0']['POJISTOVNY'] as $poj) {
			foreach ($values[$set_org]['0'] as $key => $value) {
				$values[$get_org][$poj][$key] = $value;
			}
		}
		return $values;
	}

	public function lekyFormValid($form) {
		$validate = $form->getValues();
		if (isset($validate->ID_LEKY)) {
			if (is_array($validate->ID_LEKY)) {
				$vali[] = explode(",", $validate->ID_LEKY);
			} else {
				$vali[] = $validate->ID_LEKY;
			}
			foreach ($vali as $ID_LEKY) {
				if (!is_numeric($ID_LEKY)) {
					$form->addError("ID léku není číselná hodnota!");
				}
				if (strlen($ID_LEKY) != 7) {
					$form->addError("ID léku nemá dostatečný počet znaků! (7)");
				}
			}
		}
		if ($this->user->getIdentity()->modul_poj == 1) {
			foreach (self::ORGANIZACE_VISIBLE_NULL as $org) {
				foreach ($validate->$org as $key => $value) {
					if (isset($value->STAV)) {
						if ($value->STAV == 'Nasmlouváno' && !isset($value->NASMLOUVANO_OD)) {
							$form->addError("Je nasmlouvaný stav, ale není zadáno od kdy!!");
						}
					}
				}
			}
			if (isset($validate->ORGANIZACE) && isset($validate->POJ)) {
				if (!$validate['0']['0']['STAV']) {
					$form->addError("Stav není zadaný pro vybrané organizace a pojišťovny");
				}
				if (!$validate['0']['0']['ORG']) {
					$form->addError("Organizace nejsou specifikovaný!");
				}
				if (!$validate['0']['0']['POJISTOVNY']) {
					$form->addError("Pojišťovny nejsou specifikovaný!");
				}
				foreach ($validate->ORGANIZACE as $org) {
					if (!$validate[$org]['0']['STAV']) {
						$form->addError("Není vyplněný stav prosím vyplňte");
					}
					if (!$validate[$org]['0']['POJISTOVNY']) {
						$form->addError("Není pro jaké pojišťovny prosím vyplntě pojišťovny!");
					}
				}
			}
		}
	}

	public function lekyFormSucceeded($form) {
		$values = $form->getValues();

		$vali = explode(",", $values->ID_LEKY);
		$DOP = explode(",", $values->DOP);
		$BAL = explode(",", $values->BALENI);
		$dg[] = '';
		for ($i = 0; $i < count($vali); $i++) {
			if ($values->ORGANIZACE) {
				foreach ($values->ORGANIZACE as $org) {
					if ($values[$org]['0']['STAV']) {
						
						$values = $this->mulityValues($values, $org, $org);
						$values[$org]['0']['STAV'] = '';
					}
				}
			}
			if ($values['0']['0']['STAV']) {
				$values->ORGANIZACE = $values['0']['0']['ORG'];
				foreach ($values['0']['0']['ORG'] as $organizace) {
					$values = $this->mulityValues($values, $organizace, '0');
					$values['0']['0']['STAV'] = '';
				}
			}

			//$this->LogyModel->insertLog(\App\LekyModule\Model\Leky::AKESO_LEKY, $values, $this->user->getId()); 
			foreach (self::ORGANIZACE_VISIBLE as $org) {
				foreach ($values->$org as $key => $value) {
					if ($value->STAV) {
						foreach ($value->DG as $dg) {
							if ($dg->DG_NAZEV) {
								$dg->ID_LEKY = $vali[$i];
								$dg->ORGANIZACE = $org;
								$dg->POJISTOVNA = $key;
								$this->BaseModel->insert_edit_pojistovny_dg($dg);
							}
						}

						$value->ORGANIZACE = $org;
						$value->ID_LEKY = $vali[$i];
						if ($key == '0') {
							foreach ($value->POJISTOVNY as $poj) {
								$value->POJISTOVNA = $poj;
								$value->SMLOUVA = $this->setSmlouva($value->POJISTOVNA);
								$this->BaseModel->insert_edit_pojistovny($value);
                                                                $value->SMLOUVA = NULL;
							}
						} else {
							$value->POJISTOVNA = $key;
							$value->SMLOUVA = $this->setSmlouva($value->POJISTOVNA);
							$this->BaseModel->insert_edit_pojistovny($value);
						}
					}
				}
			}
			$values->ID_LEKY = $vali[$i];
			$values->DOP = $DOP[$i];
			$values->BALENI = $BAL[$i];
			if ($this->user->getIdentity()->modul_fin == 1) {
				$this->BaseModel->insertCena($values, 'AKESO_LEKY_CENAVYROBCE', 'CENA_VYROBCE', $values->CENA_VYROBCE);
				$this->BaseModel->insertCena($values, 'AKESO_LEKY_NAKUPNICENA', 'CENA_NAKUPNI', $values->CENA_NAKUPNI);
			}
			if(is_array($values->ORGANIZACE)){	
				foreach ($values->ORGANIZACE as $key => $value) {
					$values->ORGANIZACE = $value;
					$this->BaseModel->insertLeky($values);
				}
			}
			else{
				$this->BaseModel->insertLeky($values);
			}
		}
		$this->flashMessage('Záznam byl přidan.', "success");
		$this->redirect(':Leky:Leky:default');
	}

	private function setSmlouva($poj, $smlouva = null) {
		if ($smlouva) {
			return $smlouva;
		} else {
			if ($poj == '111' || $poj == '205' || $poj == '207') {
				return 0;
			} elseif ($poj == '209' || $poj == '213') {
				return 1;
			} elseif ($poj == '201') {
				return 2;
			} elseif ($poj == '211') {
				return 3;
			}
		}
	}

	public static function processFormErrors(\Nette\Forms\Container $form, \Nette\Application\UI\Presenter $presenter) {
		foreach ($form->getComponents() as $component) {
			if ($component instanceof \Nette\Forms\Controls\BaseControl && $component->hasErrors()) {
				$presenter->flashMessage($form->getParent()->getName() . "-" . $form->getName() . "-" . $component->getCaption() . " - " . implode("; ", $component->getErrors()));
			} elseif ($component instanceof \Nette\Forms\Container) {
				SELF::processFormErrors($component, $presenter);
			}
		}
	}

	public function handleDgskup($term) {
		$fristHalfItems = $this->BaseModel->getDg($term);
		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($fristHalfItems));
	}

	protected function createComponentHromadForm(string $name) {
		$form = new \Nette\Application\UI\Form($this, $name);
		$this->FormFactory->setMultiLekyForm($form);
		$value['ID'] = $this->getParameter('id');
		$form->setDefaults($value);
		$form->addGroup();
		$form->addSubmit('send', 'Uložit')
				->setHtmlAttribute('class ', 'btn btn-success button btn-block');
	//	$form->onValidate[] = [$this, "lekyFormValid"];
		$form->onSuccess[] = [$this, "hromadFormSucceeded"];
		return $form;
	}

	public function hromadFormSucceeded($form) {
		$values = $form->getValues();
		$id_lek = json_decode($values->ID);
		foreach ($values->ORGANIZACE as $org) {
			if ($values[$org]['0']['STAV']) {
				$values = $this->mulityValues($values, $org, $org);
				$values[$org]['0']['STAV'] = '';
			}
		}
		if ($values['0']['0']['STAV']) {
			$values->ORGANIZACE = $values['0']['0']['ORG'];
			foreach ($values['0']['0']['ORG'] as $organizace) {
				$values = $this->mulityValues($values, $organizace, '0');
			}
		}
		foreach ($id_lek as $primkey) {
			foreach (self::ORGANIZACE_VISIBLE as $org) {
				foreach ($values->$org as $key => $value) {
					foreach ($value->DG as $dg) {
						if ($dg->DG_NAZEV) {
							$dg->ID_LEKY = $primkey;
							$dg->ORGANIZACE = $org;
							$dg->POJISTOVNA = $key;
							$this->BaseModel->insert_edit_pojistovny_dg($dg);
						}
					}
					if ($value->STAV) {
						$value->ORGANIZACE = $org;
						$value->POJISTOVNA = $key;
						$value->ID_LEKY = $primkey;
                        $value->SMLOUVA = $this->setSmlouva($value->POJISTOVNA);
						$this->BaseModel->insert_edit_pojistovny($value);
					}
				}
			}
		}
		$this->flashMessage('Záznamy byly přidány.', "success");
		$this->redirect('default');
	}

	protected function createComponentHromadiagForm(string $name) {
		$form = new \Nette\Application\UI\Form($this, $name);
		$this->FormFactory->setHromadDiagForm($form);


		$value['ID'] = $this->getParameter('id');
		$org = $this->BaseModel->getDGSkupPK(json_decode($value['ID']),'ORGANIZACE');
		$poj = $this->BaseModel->getDGSkupPK(json_decode($value['ID']),'POJISTOVNA');
		$value['ORGANIZACE'] =  array_unique($org);
		$value['POJ'] =  array_unique($poj);		
		$DG['DG'] = $this->BaseModel->getDGSkup(json_decode($value['ID']));

		$uniqueValues = array();

		foreach ($DG['DG'] as $item) {
			$dgNazev = $item->DG_NAZEV;
			if (!in_array($dgNazev, $uniqueValues)) {
				$uniqueValues[] = $dgNazev;
				$value['DG'][] = $item;
			}
		}

		$form->setDefaults($value);
		$form->addGroup();
		$form->addSubmit('send', 'Uložit')
				->setHtmlAttribute('class ', 'btn btn-success button btn-block');
		$form->onSuccess[] = [$this, "hromadDiagFormSucceeded"];
		return $form;
	}

	public function hromadDiagFormSucceeded($form) {
		$values = $form->getValues();
		$id_lek = json_decode($values->ID);
		foreach ($id_lek as $primkey) {
			foreach ($values->ORGANIZACE as $org) {
				foreach ($values->POJ as $key) {
					foreach ($values->DG as $dg) {
						if ($dg->DG_NAZEV) {
							$dg->ID_LEKY = $primkey;
							$dg->ORGANIZACE = $org;
							$dg->POJISTOVNA = $key;
							$this->BaseModel->insert_edit_pojistovny_dg($dg);
						}
					}
				}
			}
		}
		$this->flashMessage('Záznamy byly přidány.', "success");
		$this->redirect('default');
	}

	public function handleMultiajax($values) {
		$values = explode(",", $values);
		$ajaxvlaue_singl = ['nazev' => 'NAZ', 'sila' => 'SILA', 'ATC' => 'ATC', 'ATC3' => 'ATC3', 'uhr1' => 'UHR1', 'uhr2' => 'UHR2', 'uhr3' => 'UHR3'];
		$ajaxvlaue_multi = ['dop' => 'DOP', 'baleni' => 'BALENI'];
		foreach ($ajaxvlaue_singl as $key => $table) {
			$val = $this->BaseModel->getHandle($values['0'], $table);
			$val = $this->NUMBER($val);
			$this->payload->$key = $val;
		}
		foreach ($ajaxvlaue_multi as $key => $table) {
			$this->payload->$key = $this->BaseModel->getHandleMulti($values, $table);
		}
		if (!empty($this->payload->ATC)) {
			$this->payload->BIOSIMOLAR = $this->BaseModel->getHandleBIOSIMOLAR($this->payload->ATC);
		}
		$this->Ucinnalatka($values['0']);
		$this->MAX($values['0']);
		$this->VYROBNI($values['0']);
		$this->NAKUPNI($values['0']);
		$this->redrawControl();
	}

	public function handleIdlek($typedText) {
		$Items = $this->BaseModel->getIdlek($typedText);
		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($Items));
	}

	private function Ucinnalatka($values) {
		$ucinalatka = new \UcinneLatky();
		$rozdel = $ucinalatka->getInfo($values);

		if (!empty($rozdel)) {
			$UCINNA_LATKA = $this->BaseModel->get_setUcinnalatka($values, implode(", ", $rozdel));
		} else {
			$UCINNA_LATKA = '';
		}
		$this->payload->ucinnalatka = $rozdel;
	}

	private function NUMBER($number) {
		if (isset($number['.0000'])) {
			if ($number['.0000'] === '.0000') {
				$number = '0';
			}
		}
		return $number;
	}

	private function MAX($values) {
		$MAX = $this->BaseModel->getHandleMAX($values);
		$MAX = $this->NUMBER($MAX);
		$this->payload->MAX = $MAX;
	}

	private function VYROBNI($values) {
		$VYROBCE = $this->BaseModel->getHandleMediox($values, 'VYROBNI_CENA');
		$VYROBCE = $this->NUMBER($VYROBCE);
		$this->payload->VYROBCE = $VYROBCE;
	}

	private function NAKUPNI($values) {
		$NAKUPNI = $this->BaseModel->getHandleMediox($values, 'NAKUP_CENA');
		$NAKUPNI = $this->NUMBER($NAKUPNI);
		$this->payload->NAKUPNI = $NAKUPNI;
	}
}
