<?php

namespace App\LekyModule\Grids;

use Ublaboo\DataGrid\AkesoGrid,
	\Nette\Forms\Container;
use \Nette\Utils\Html;
use App\LekyModule\Presenters\LekyPresenter;

/**
 * Třída s Gridy pro autopark
 *
 * @author Vítek Šmíd
 */
class LekyGridFactory extends \App\Factory\BaseDataGridFactory {

	public function __construct(\Nette\Security\User $user, \Dibi\Connection $db, array $parameters) {
		//parent::__construct($user, $db, $parameters);
	}

	public function setLekyGrid(AkesoGrid $grid, $prava, $poj,$defaultHodnoty): AkesoGrid {
		$grid->setPrimaryKey("ID_LEKY");
		$grid->setStrictSessionFilterValues(false);
		$grid->setColumnsHideable();

		$grid->addColumnText('ID_LEKY', 'Identifikace léků')
				->setFitContent()
				->setSortable()
				->setFilterText();

		$grid->addColumnText('ORGANIZACE', 'Organizace')
				->setSortable()
				->setFilterMultiSelect(\App\LekyModule\Presenters\LekyPresenter::ORGANIZACE)
				->addAttribute('class', 'multiselect');

		$grid->addColumnText('NAZ', 'Název')
				->setSortable()
				->setFilterText()
				->setSplitWordsSearch(true);

		$grid->addColumnText('DOP', 'DOP')
				->setSortable()
				->setFilterText();

		$grid->addColumnText('SILA', 'Síla')
				->setSortable()
				->setFilterText();

		$grid->addColumnText('BALENI', 'Balení')
				->setSortable()
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('POZNAMKA', 'Poznámka')
				->setSortable()
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('UCINNA_LATKA', 'Učinná látka')
				->setSortable()
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('BIOSIMOLAR', 'Biosimolar')
				->setSortable()
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('ATC', 'ATC')
				->setSortable()
				->setDefaultHide()
				->setReplacement(["" => "-"])
				->setFilterText()
				->setSplitWordsSearch(true);

		/* $grid->addColumnText('ATC1', 'Indikační\DG skupina')  smazat v databazi 
		  ->setSortable()
		  ->setReplacement([""=>"-"])
		  ->setFilterText()
		  ->setSplitWordsSearch(true);
		 */


/*		$grid->addColumnText('POPIS', 'DG popis')
				->setSortable()
				->setDefaultHide()
				->setReplacement(["" => "-"])
				->setFilterText();

		$grid->addColumnText('LECBA', 'DG léčba')
				->setSortable()
				->setDefaultHide()
				->setReplacement(["" => "-"])
				->setFilterText();
*/
		$grid->addColumnText('ATC3', 'ATC3')
				->setSortable()
				->setDefaultHide()
				->setReplacement(["" => "-"])
				->setFilterText()
				->setSplitWordsSearch(true);

		$pojistovny = ['111', '201', '205', '207', '209', '211', '213'];

		foreach ($pojistovny as $value) {

			$grid->addColumnText($value . '_STAV', $value . ' Stav')
					->setSortable()
					//->setReplacement([""=>"Nezadáno"])
					->setRenderer(function ($item) use ($value) {
						$el = Html::el('div');
						$el->class($item["poj" . $value . "_BARVA"]);
						if ($item[$value . "_STAV"] == 'Nasmlouváno') {
							$item[$value . "_NASMLOUVANO_OD"] = $item[$value . "_NASMLOUVANO_OD"] ?? "Nezadáno";
							$item[$value . "_STAV"] = $item[$value . "_STAV"] . ' ' . $item[$value . "_NASMLOUVANO_OD"] ?? "Nezadáno";
						} else {
							$item[$value . "_STAV"] = $item[$value . "_STAV"] ?? "Nezadáno";
						}
						if (!empty($item[$value . '_POZNAMKA'])) {
							$el->title($item[$value . '_POZNAMKA']);
							echo $el->addHtml(Html::el('img')->width('12%')->src('/Lekovnice/www/img/mark.png')->style('float:left;'));
						}
						$el->setText($item[$value . "_STAV"]);
						return $el;
					})
					->setFilterText();

			$grid->addColumnText($value . '_RL', $value . ' Revizní lékař')
					->setSortable()
					->setDefaultHide()
					->setReplacement(LekyPresenter::TRUEORFALSE)
					->setFilterText();

			$grid->addColumnText($value . '_SMLOUVA', $value . ' Smlouva')
					->setSortable()
					->setDefaultHide()
					->setReplacement(["" => "-", 0 => "VZP/ ČPZP / OZP Zvláštní smlouva na dobu neurčitou – spektrum léčiv a limitace sjednávána nově na aktuální rok", 1 => "ZPŠ/ RBP/ Zvláštní smlouva na dobu určitou (rok) s automatickou prolongací o další 1 rok; limitace nesjednávána, spektrum léčiv  zachováváno po dobu platnosti ZS", 2=> "VOZP / Zvláštní smlouva na dobu určitou (rok) s automatickou prolongací o další 1 rok – spektrum léčiv a limitace sjednávána nově na aktuální rok", 3=>"ZPMV / Zvláštní smlouva formou dodatku k Smlouvě na dobu určitou (rok) obsahuje spektrum léčiv i limitace (Kč)- vše sjednáváno nově na aktuální rok"])
					->setFilterText();

			/* $grid->addColumnText($value.'_VILP', $value.' VILP')
			  ->setSortable()
			  ->setDefaultHide()
			  ->setReplacement(\App\LekyModule\Presenters\LekyPresenter::TRUEORFALSE)
			  ->setFilterText();
			
			$grid->addColumnText($value . '_TYP_NADORU', $value . ' DG skupina') // Přejmenvat typ nádoru 
					->setSortable()
					->setDefaultHide()
					->setReplacement(LekyPresenter::TRUEORFALSE)
					->setFilterText();
					*/
			$grid->addColumnText($value . '_POZNAMKA', $value . ' Poznámka')
					->setSortable()
					->setDefaultHide()
					->setReplacement(["" => "-"])
					->setFilterText();

			$grid->addColumnText('DG_' . $value, $value . ' DG skupina')
					->setSortable()
					->setDefaultHide()
					->setReplacement(["" => "-"])
					->setFilterText();
		}

		$grid->addColumnText('UHR1', 'UHR1')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('UHR2', 'UHR2')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('UHR3', 'UHR3')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('CENA_FAKTURACE', 'Cena fakturace')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('CENA_MAX', 'Maximální cena')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('CENA_VYROBCE', 'Cena výrobce')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('CENA_NAKUPNI', 'Nákupní cena')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('CENA_VYROBCE_BEZDPH', 'Cena výrobce bez DPH')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('CENA_SENIMED_BEZDPH', 'SENIMED(Distribuce)NC bez DPH')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('CENA_MUS_PHARMA', 'MUS Pharma NC bez DPH')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('CENA_MUS_NC_BEZDPH', 'MULTISCAN NC bez DPH')
				->setSortable()
				->setReplacement(["" => "-", ".00" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('CENA_MUS_NC', 'MULTISCAN NC s DPH')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('UHRADA', 'Úhrada')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('KOMPENZACE', 'Kompenzace')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		$grid->addColumnText('BONUS', 'Bonus')
				->setSortable()
				->setReplacement(["" => "-", ".0000" => "0"])
				->setDefaultHide()
				->setFilterText();

		if (($prava == '9' || $prava == '2') && $poj == 1) {
			$grid->addGroupButtonAction('Hromadná změna pojišťoven')
					->setClass("btn btn-success")
					->setAttribute("style", "float:initial !important;")
			->onClick[] = function ($id) use ($grid) {
				$grid->presenter->redirect(':Leky:Leky:Hromad', json_encode($id));
			};
			$grid->addGroupButtonAction('Hromadná změna dg skupiny')
				->setClass("btn btn-primary")
				->setAttribute("style", "float:initial !important;")
				->onClick[] = function ($id) use ($grid) {
					$grid->presenter->redirect(':Leky:Leky:Hromadiag', json_encode($id));
				};
		} else {
			$grid->addGroupButtonAction("")
					->setAttribute("style", "display:none;");
		}
		$container = $grid->getComponent("filter-group_action");
		/** @var \Nette\Forms\Container $container */
		/* 		$container->addSelect("lekarnaVyber", "Lékárna:")
		  ->setItems(\App\LekyModule\Presenters\LekyPresenter::ORGANIZACE_SELECT)
		  ->setDefaultValue($grid->getSessionData("lekarnaVyber"))
		  ->setHtmlAttribute("class", "form-control")
		  ->setHtmlAttribute("style", "width:25% !important; height: 0%; margin-right: 25px")
		  ->setHtmlAttribute("data-autosubmit");
		 */
		$container->addCheckbox("histori", "Akord léky: ")
				->setDefaultValue($grid->getSessionData("histori"))
				->setHtmlId('histor')
				->setHtmlAttribute("class", "checkbox")
				->getControlPrototype()
				->setAttribute("onchange", "$(this).parents('form').submit();");
		
		$container->getForm()
		->onSubmit[] = function ($form) use ($grid) {
			$values = $form->getValues();
			$values->group_action->lekarnaVyber = $values->group_action->lekarnaVyber ?? null;
			$values->group_action->histori = $values->group_action->histori ?? null;
			if ($values->group_action->lekarnaVyber) {
				$grid->setDataSource($grid->presenter->BaseModel->getDataSource($values->group_action->lekarnaVyber));
				$grid->getSessionData()->lekarnaVyber = $values->group_action->lekarnaVyber;
			} elseif (!$values->group_action->lekarnaVyber) {
				unset($grid->getSessionData()->lekarnaVyber);
				$values->group_action->lekarnaVyber = NULL;
				$grid->setDataSource($grid->presenter->BaseModel->getDataSource($values->group_action->lekarnaVyber));
			}
			if ($values->group_action->histori) {
				$grid->setDataSource($grid->presenter->BaseModel->getDataSource($values->group_action->lekarnaVyber, $values->group_action->histori));
				$grid->getSessionData()->histori = $values->group_action->histori;
			} elseif (!$values->group_action->lekarnaVyber) {
				unset($grid->getSessionData()->histori, $values->group_action->histori);
				$values->group_action->histori = NULL;
				$grid->setDataSource($grid->presenter->BaseModel->getDataSource($values->group_action->lekarnaVyber, $values->group_action->histori));
			}
			$grid->reload();
		};

		if ($prava == '9' || $prava == '2') {
			$grid->addToolbarButton("new")
					->setText("Nový lék")
					->setClass("btn btn-success")
					->setIcon("plus")
					->setTitle("Přidá nový lék");
		}
		$grid->addAction('sukl', 'Sukl', 'web')
				->addAttributes(["target" => "_blank"])
				->setClass("btn btn-info")
				->setIcon("info");

/*		$grid->setItemsDetail(true, "ID_LEKY")
				->setClass("btn btn-primary btn-sm ajax")
				->setText("DG zobrazit/skrýt")
				->setTitle("Detail")
				->setIcon("arrow-down")
				->setType("renderer")
				->setRenderer(function ($row) use ($grid) {

					$values = $grid->presenter->BaseModel->getDataSource_DG($row->ID_LEKY);
					$el = Html::el('table');
					$el->addHtml('<tr>');
					$el->addHtml('<td>')->addText('Lék');
					$el->addHtml('<td>')->addText('Organizace');
					$el->addHtml('<td>')->addText('Pojišťovna/y');
					$el->addHtml('<td>')->addText('DG nazev');
					$el->addHtml('<td>')->addText('VILP');
					$el->addHtml('<td>')->addText('Platnost od');
					$el->addHtml('<td>')->addText('Platnost do');
					foreach ($values as $key => $value) {
						$el->addHtml('<tr>');
						$i = 0;
						foreach ($value as $k => $val) {
							if ($i == 2 && $val == 0) {
								$val = 'Všechny';
							}
							if ($i == 4 && $val == 0) {
								$val = 'Ne';
							}
							if ($i == 4 && $val == 1) {
								$val = 'Ano';
							}
							$el->addHtml('<td>')->addText($val);
							$i++;
						}
					}
					return $el;
				});
*/
		if ($prava == '9' || $prava == '2') {
			$grid->addAction('edit', 'Editace', 'edit')
					->setClass("btn btn-warning")
					->setIcon("pencil");
		}
		//$grid->setDefaultFilter(['ORGANIZACE' => $defaultHodnoty]);
		return $grid;
	}

	public function setLekyAtcSkupGrid(\Ublaboo\DataGrid\DataGrid $grid, $prava): \Ublaboo\DataGrid\DataGrid {
		$grid->setPrimaryKey("ATC1");
		$grid->setStrictSessionFilterValues(false);
		$grid->setColumnsHideable();

		$grid->addColumnText('ATC1', 'Indikační skupina')
				->setFitContent()
				->setSortable()
				->setFilterText();

		$grid->addColumnText('POPIS', 'Popis')
				->setFitContent()
				->setSortable()
				->setFilterText();

		$grid->addColumnText('LECBA', 'Léčba')
				->setFitContent()
				->setSortable()
				->setFilterText();

		$grid->addColumnDateTime('PLATNOST_OD', 'Platnost od')
				->setFitContent()
				->setSortable()
				->setFilterText();

		$grid->addColumnDateTime('PLATNOST_DO', 'Platnost do')
				->setFitContent()
				->setSortable()
				->setFilterText();

		$grid->addAction('sukl', 'Sukl', 'Web')
				->addAttributes(["target" => "_blank"])
				->setClass("btn btn-outline-info")
				->setIcon("info");

		$grid->addAction('edit', 'Editace', 'edit')
				->setClass("btn btn-outline-secondary")
				->setIcon("pencil");

		if ($prava == '9' || $prava == '2') {
			$grid->addToolbarButton("new")
					->setText("Nová Atc skupina")
					->setClass("btn btn-success")
					->setIcon("plus")
					->setTitle("Nová indukční skupina");
		}

		return $grid;
	}
	public function setDGSkup(\Ublaboo\DataGrid\DataGrid $grid)
	{

		$grid->addColumnText('ID_LEKY', 'Lék')
				->setFitContent()
				->setSortable()
				->setFilterText();

		$grid->addColumnText('ORGANIZACE', 'Organizace')
			->setFitContent()
			->setSortable()
			->setFilterMultiSelect(\App\LekyModule\Presenters\LekyPresenter::ORGANIZACE);

		$grid->addColumnText('POJISTOVNA', 'Pojišťovna')
				->setFitContent()
				->setSortable()
				->setFilterText();

		$grid->addColumnText('VILP', 'VILP')
				->setFitContent()
				->setSortable()
				->setFilterText();

		$grid->addColumnDateTime('DG_PLATNOST_OD', 'Platnost od')
				->setFitContent()
				->setSortable()
				->setAlign('left')
				->setFilterDate()
				->setAttribute("data-date-language", "cs")				
				->setCondition(function (\Dibi\Fluent $fluent, string $value) {
					if ($value) {
						$dateTime = \DateTime::createFromFormat('d. m. Y', $value);
						if ($dateTime) {
							$date = $dateTime->format('d.m.Y');
							$fluent->where("DG_PLATNOST_OD = %d", $date);
						}
					}
				});
		$grid->addColumnDateTime('DG_PLATNOST_DO', 'Platnost od')
				->setFitContent()
				->setSortable()
				->setAlign('left')
				->setFilterDate()
				->setAttribute("data-date-language", "cs")				
				->setCondition(function (\Dibi\Fluent $fluent, string $value) {
					if ($value) {
						$dateTime = \DateTime::createFromFormat('d. m. Y', $value);
						if ($dateTime) {
							$date = $dateTime->format('d.m.Y');
							$fluent->where("DG_PLATNOST_DO = %d", $date);
						}
					}
				});
	}
	public function setDGGrid(\Ublaboo\DataGrid\DataGrid $grid, $ID_LEKY) {
		$grid->setPrimaryKey("ID");
		$grid->setTranslator($this->getCsTranslator());
		$grid->setColumnsHideable();

		$grid->addColumnText('ID', '#')
				->setSortable()
				->setFilterText();

		$grid->addColumnText('ID_LEKY', 'Lék')
				->setSortable()
				->setFilterText();

		$grid->addColumnText('ORGANIZACE', 'Organizace')
			->setSortable()
			->setFilterMultiSelect(\App\LekyModule\Presenters\LekyPresenter::ORGANIZACE);

		$grid->addColumnText('POJISTOVNA', 'Pojišťovna')
				->setSortable()
				->setReplacement(['0'=>'Všechny'])
				->setFilterText();

		$grid->addColumnText('DG_NAZEV', 'Název DG')
				->setSortable()
				->setFilterText();

		$grid->addColumnText('VILP', 'VILP')
				->setSortable()
				->setReplacement(LekyPresenter::TRUEORFALSE)
				->setFilterText();

		$grid->addColumnDateTime('DG_PLATNOST_OD', 'Platnost od')
				->setFitContent()
				->setFormat("d.m.Y")
				->setSortable()
				->setAlign('left')
				->setFilterDate()
				->setAttribute("data-date-language", "cs")
				->setCondition(function (\Dibi\Fluent $fluent, string $value) {
					if ($value) {
						$dateTime = \DateTime::createFromFormat('d. m. Y', $value);
						if ($dateTime) {
							$date = $dateTime->format('d.m.Y');
							$fluent->where("DATUM = %d", $date);
						}
					}
				});

		$grid->addColumnDateTime('DG_PLATNOST_DO', 'Platnost do')
				->setFitContent()
				->setFormat("d.m.Y")
				->setSortable()
				->setAlign('left')
				->setFilterDate()
				->setAttribute("data-date-language", "cs")
				->setCondition(function (\Dibi\Fluent $fluent, string $value) {
					if ($value) {
						$dateTime = \DateTime::createFromFormat('d. m. Y', $value);
						if ($dateTime) {
							$date = $dateTime->format('d.m.Y');
							$fluent->where("DATUM = %d", $date);
						}
					}
				});

		$grid->addInlineAdd()
			->setPositionTop()
			->onControlAdd[] = function (Container $container) use($ID_LEKY){
						$container->addText("ID_LEKY", "Lék")
						->setDefaultValue($ID_LEKY)
						->setHtmlAttribute('readonly');
							
						$container->addSelect('ORGANIZACE','ORGANIZACE')
								->setItems(LekyPresenter::ORGANIZACE);

						$container->addSelect('POJISTOVNA','Pojišťovna')
								->setItems([0=>'Všechny']+LekyPresenter::POJISTOVNY);
						
						$container->addText("DG_NAZEV", "DG Název")
							->setHtmlAttribute('data-autocomplete-dg')
							->setNullable();
					
						$container->addCheckbox('VILP', 'VILP')
							->setHtmlAttribute('class', 'checkbox_style');

                        $container->addText('DG_PLATNOST_OD','Platnost od')
                         	->setHtmlType('date')
                            ->setDefaultValue(date("Y-m-d"))
                            ->setNullable();

						$container->addText('DG_PLATNOST_DO','Platnost do')
                            ->setHtmlType('date')
                            ->setDefaultValue(date("Y-m-d"))
                            ->setNullable();
						
                };

				$grid->addInlineEdit()
					->onControlAdd[] = function(Container $container): void {
						$container->addText("ID_LEKY", "Lék")
									->setHtmlAttribute('readonly');
							
						$container->addSelect('ORGANIZACE','ORGANIZACE')
									->setHtmlAttribute('readonly')
									->setItems(LekyPresenter::ORGANIZACE);

						$container->addSelect('POJISTOVNA', 'Pojišťovna')
									->setHtmlAttribute('readonly')
									->setItems([0=>'Všechny']+LekyPresenter::POJISTOVNY);
						
						$container->addText("DG_NAZEV", "DG Název")
							->setHtmlAttribute('readonly')
							->setNullable();
					
						$container->addCheckbox('VILP', 'VILP')
							->setHtmlAttribute('class', 'checkbox_style');

						$container->addText('DG_PLATNOST_OD','Platnost od')
							->setHtmlType('date')
							->setNullable();

						$container->addText('DG_PLATNOST_DO','Platnost do')
							->setHtmlType('date')
							->setNullable();
				};
	
				$grid->getInlineEdit()->onSetDefaults[] = function (Container $container, $item): void {
					$ID_LEKY = $item['ID_LEKY'];
					$ORGANIZACE = $item['ORGANIZACE'];
					$POJISTOVNA = $item['POJISTOVNA'];
					$PLATNOST_OD =$item['DG_PLATNOST_OD'] !== null ? \DateTime::createFromFormat('d.m.Y', $item['DG_PLATNOST_OD'])->format('Y-m-d') : NULL;
					$PLATNOST_DO =$item['DG_PLATNOST_DO'] !== null ? \DateTime::createFromFormat('d.m.Y', $item['DG_PLATNOST_DO'])->format('Y-m-d') : NULL;
					$container->setDefaults([
						'ID_LEKY' => $ID_LEKY,
						'ORGANIZACE' => $ORGANIZACE,
						'POJISTOVNA' => $POJISTOVNA,
						'VILP' => $item['VILP'],
						'DG_NAZEV' => $item['DG_NAZEV'],
						'DG_PLATNOST_OD' => $PLATNOST_OD,
						'DG_PLATNOST_DO' => $PLATNOST_DO,
					]);
		};


		return $grid;
	}

}
