<?php

declare(strict_types=1);

namespace App\LekyModule\Forms;

use Nette\Application\UI\Form,
	Nette\Forms\Container,
	Nette\Security\User,
	App\Components\Model\Ciselnik;
use App\LekyModule\Presenters\LekyPresenter;

\Nette\Forms\Controls\BaseControl::$idMask = '%s';

class LekyFormFactory {

	const POJ = array('111' => '111', '201' => '201', '205' => '205', '207' => '207', '209' => '209', '211' => '211', '213' => '213'),
			ORGANIZACE = ["NH", "RNB", "MUS", "DCNH"],
			ORGANIZACE_foreach = [ "0", "NH", "RNB", "MUS", "DCNH"];

	public function __construct(User $user, \Dibi\Connection $db, Array $parameters) {
		// parent::__construct($user, $db, $parameters);
	}

	public function setLekyForm(\Nette\Application\UI\Form $form) {
		$form->addText('ID_LEKY', 'ID léky')
				->setRequired('Musí být zadaný "%label"')
				->setHtmlAttribute('class', 'ID_LEKY')
				//->setHtmlAttribute('data-autocomplete-idleky')
				//->setHtmlAttribute('multiple')
				//->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znaků.', 8)
				//->addRule(Form::MIN_LENGTH,'Minimální délka pole "%label" je %d znaků.', 7)
				->setHtmlAttribute('data-role', 'tagsinput')
				//->addRule(Form::NUMERIC, "Pole '%label' musí být číselného typu.")
				->setNullable();

		$form->addText('NAZ', 'Název')
				->setRequired('Musí být vyplněný "%label"')
				->addRule(Form::MAX_LENGTH, 'Maximální délka pole "%label" je %d znaků.', 70)
				->setNullable();

		$form->addText('DOP', 'Dop')
				->setRequired('Musí být vyplněný "%label"')
				//->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znaků.', 75)    
				->setNullable();

		$form->addText('SILA', 'Síla')
				->setRequired('Musí být vyplněný "%label"')
				->addRule(Form::MAX_LENGTH, 'Maximální délka pole "%label" je %d znaků.', 24)
				->setNullable();

		$form->addText('BALENI', 'Balení')
				//->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znaků.', 22)   
				->setNullable();

		$form->addTextArea('POZNAMKA', 'Poznámka')
				->setNullable();

		$form->addTextArea('UCINNA_LATKA', 'Učinná látka')
				->setNullable();

		$form->addTextArea('BIOSIMOLAR', 'Biosimolar')
				->setNullable();

		$form->addMultiSelect('ORGANIZACE', 'Organizace')
				->setHtmlAttribute('class', 'multiselect')
				->setHtmlAttribute('data-toggle', 'tooltip')
				->setHtmlAttribute('data-placement', 'top')
				->setHtmlAttribute('title', 'Jednotlivé zádaní organizace')
				->setItems(LekyPresenter::ORGANIZACE)
				->addCondition(Form::EQUAL, '')
				->toggle('vse') // všechno i organizace
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('RNB', 'MUS', 'DCNH'))
				->toggle('NH')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('NH', 'MUS', 'DCNH'))
				->toggle('RNB')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('NH', 'RNB', 'DCNH'))
				->toggle('MUS')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('NH', 'RNB', 'MUS'))
				->toggle('DCNH')
				->endCondition();

		$form->addText('ATC', 'ATC skupina')
				->addRule(Form::MAX_LENGTH, 'Maximální délka pole "%label" je %d znaků.', 7)
				->setNullable();

		/*        $form->addText('ATC1', 'Indikační skupina')
		  ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znaků.', 1)
		  ->setNullable();
		 */
		$form->addText('ATC3', 'ATC3 skupina')
				->addRule(Form::MAX_LENGTH, 'Maximální délka pole "%label" je %d znaků.', 3)
				->setNullable();

		$form->addText('UHR1', 'UHR1(Kč)')
				->addRule(Form::FLOAT, 'Špatný format UHR1')
				->setNullable();

		$form->addText('UHR2', 'UHR2(Kč)')
				->addRule(Form::FLOAT, 'Špatný format UHR2')
				->setNullable();

		$form->addText('UHR3', 'UHR3(Kč)')
				->addRule(Form::FLOAT, 'Špatný format UHR3')
				->setNullable();

		$form->addText('CENA_FAKTURACE', 'Cena Fakturace v(Kč)')
				->addRule(Form::FLOAT, 'Špatný format fakturace')
				->setNullable();

		$form->addText('CENA_MAX', 'Cena max v(Kč)')
				->addRule(Form::FLOAT, 'Špatný format maximální ceny')
				->setNullable();

		$form->addText('CENA_VYROBCE', 'Cena výrobce v(Kč)')
				->addRule(Form::FLOAT, 'Špatný format ceny od výrobce')
				->setNullable();

		$form->addText('CENA_NAKUPNI', 'Nákupní cena v(Kč)')
				->addRule(Form::FLOAT, 'Špatný format nakupní ceny')
				->setNullable();

		$form->addText('CENA_VYROBCE_BEZDPH', 'Cena výrobce bez DPH')
				->addRule(Form::FLOAT, 'Špatný format "%label"')
				->setNullable();

		$form->addText('CENA_SENIMED_BEZDPH', 'SENIMED(Distribuce)NC bez DPH')
				->addRule(Form::FLOAT, 'Špatný format "%label"')
				->setNullable();

		$form->addText('CENA_MUS_PHARMA', 'MUS Pharma NC bez DPH')
				->addRule(Form::FLOAT, 'Špatný format "%label"')
				->setNullable();

		$form->addText('CENA_MUS_NC_BEZDPH', 'MULTISCAN NC bez DPH')
				->addRule(Form::FLOAT, 'Špatný format "%label"')
				->setNullable();

		$form->addText('CENA_MUS_NC', 'MULTISCAN NC s DPH')
				->addRule(Form::FLOAT, 'Špatný format "%label"')
				->setNullable();
		$form->addText('UHRADA', 'Úhrada')
				->addRule(Form::FLOAT, 'Špatný format "%label"')
				->setNullable();
		$form->addText('KOMPENZACE', 'Kompenzace')
				->addRule(Form::FLOAT, 'Špatný format "%label"')
				->setNullable();
		$form->addText('BONUS', 'Bonus')
				->addRule(Form::FLOAT, 'Špatný format "%label"')
				->setNullable();

		/* 		$form->addText('UHRADA', 'Úhrada v(Kč)')
		  ->addRule(Form::FLOAT, 'Špatný format úhrady')
		  ->setNullable();

		  $form->addText('PLATNOST_OD', 'Platnost od')
		  ->setType('date')
		  ->setDefaultValue((new \DateTime)->format('Y-m-d'));

		  $form->addText('PLATNOST_DO', 'Platnost do')
		  ->setType('date')
		  ->setNullable();
		 */
		$form->addMultiSelect('POJ', 'Pojišťovny')
				->setHtmlAttribute('class', 'multiselect')
				->setHtmlAttribute('data-toggle', 'tooltip')
				->setHtmlAttribute('data-placement', 'top')
				->setHtmlAttribute('title', 'Jednotlivé zadání pojišťoven musí zadáno organizace')
				->setItems(LekyPresenter::POJISTOVNY)
				->addCondition(Form::EQUAL, '')
				->toggle('all') // všechno je pro pojišťovny
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('201', '205', '207', '209', '211', '213'))
				->toggle('p111_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '205', '207', '209', '211', '213'))
				->toggle('p201_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '207', '209', '211', '213'))
				->toggle('p205_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '205', '209', '211', '213'))
				->toggle('p207_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '205', '207', '211', '213'))
				->toggle('p209_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '205', '207', '209', '213'))
				->toggle('p211_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '205', '207', '209', '211'))
				->toggle('p213_pojistovna')
				->endCondition();

		foreach (self::ORGANIZACE as $organizace) {
			$contOrg = $form->addContainer($organizace);
			foreach (self::POJ as $pojistovna) {
				$this->setPojistovnaContainer($contOrg->addContainer($pojistovna), $organizace, $pojistovna);
			}
			$this->setPojistovnaContainer($contOrg->addContainer('0'), $organizace, '0');
		}
		$this->setPojistovnaContainer($form->addContainer(0)->addContainer(0), '0', '0');
	}

	/**
	 * Summary of setPojistovnaContainer
	* @param \Nette\Forms\Container $container
	* @param mixed $org
	* @param mixed $poj
	* @return void
	*/
	private function setPojistovnaContainer(Container $container, $org, $poj) {

		$container->addSelect('STAV', 'Stav')
				->setHtmlAttribute('data-toggle', 'tooltip')
				->setHtmlAttribute('data-placement', 'top')
				->setHtmlAttribute('title', 'Stav pro všechny/jednotlivé pojišťovny (dle nadpisu)')
				->setItems(LekyPresenter::STAV)
				->addCondition(Form::EQUAL, 'Nasmlouváno')
				->toggle('NASMLOUVANO_DATA' . $org . $poj)
				->endCondition();

		$container->addText('NASMLOUVANO_OD', 'Nasmlouváno od')
				->setType('date')
				->setNullable();

		$container->addMultiSelect('ORG', 'Organizace')
				->setHtmlAttribute('class', 'multiselect')
				->setHtmlAttribute('data-toggle', 'tooltip')
				->setHtmlAttribute('data-placement', 'top')
				->setHtmlAttribute('title', 'Hromadné zadání organizace propíše se sama')
				->setItems(LekyPresenter::ORGANIZACE);

		$container->addMultiSelect('POJISTOVNY', 'Pojistovny')
				->setHtmlAttribute('class', 'multiselect')
				->setHtmlAttribute('data-toggle', 'tooltip')
				->setHtmlAttribute('data-placement', 'top')
				->setHtmlAttribute('title', 'Hromadné zadání pojišťovny')
				->setItems(LekyPresenter::POJISTOVNY);

		$container->addCheckbox('Revizak', '')
				->setHtmlAttribute("class", "tgl-btn")
				->addCondition(Form::EQUAL, true)
				->toggle('revizak' . $org . $poj)
				->endCondition();

		$container->addSelect('RL', 'Revizní lékař')
				->setItems(LekyPresenter::REVIZAK);
		//->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znaků.', 25)
		//->setNullable();

	/*	$container->addSelect('SMLOUVA', 'Smlouva')
				->setHtmlAttribute('data-toggle', 'tooltip')
				->setHtmlAttribute('data-placement', 'top')
				->setHtmlAttribute('title', 'Nevyplňovat pokud chcete automaticky vyplniť smlouvy dle pojišťoven')
				->setItems(LekyPresenter::SMLOUVY);
		*/
		$this->addAddingButton(
				$container->addDynamic('DG', function (Container $cont): void {

					$cont->addText("DG_NAZEV", "DG Název")
							->setHtmlAttribute('data-autocomplete-dg')
							//->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znaků.', 1)  
							->setNullable();

					$cont->addCheckbox('VILP', '')
							//->setHtmlAttribute("onchange","enabledisablevilp(this)")
							->setHtmlAttribute('class', 'checkbox_style');

					/* 	$cont->addCheckbox('DG', '')  
					  ->setHtmlAttribute('class', 'checkbox_style');
					 */
					$cont->addText('DG_PLATNOST_OD', 'DG skupina od')
							->setType('date')
							->setNullable();

					$cont->addText('DG_PLATNOST_DO', 'DG skupina do')
							->setType('date')
							->setNullable();

					$this->addRemoveButton($cont);
				})
		);

		$container->addText('POZNAMKA', 'Poznámka')
				->addRule(Form::MAX_LENGTH, 'Maximální délka pole "%label" je %d znaků.', 255)
				->setNullable();

		/* 			$container->addCheckbox('VILP', '')
		  ->setHtmlAttribute("onchange","enabledisablevilp(this)")
		  ->setHtmlAttribute('class', 'checkbox_style');

		  $container->addText('VILP_PLATNOST_OD', 'VILP od')
		  ->setType('date')
		  ->setDisabled()
		  ->setNullable();

		  $container->addText('VILP_PLATNOST_DO', 'VILP do')
		  ->setType('date')
		  ->setDisabled()
		  ->setNullable();
		 */
	}

	/**
	 * Přidá odebírací tlačítko do Containeru
	 * @param Container $container
	 * @param string $title
	 * @param Form $form
	 * @param string $name - název dynamické položky
	 */
	private function addRemoveButton(Container $container, string $title = 'Odebrat', string $snippetName = null): \Nette\Forms\Controls\SubmitButton {
		$btn = $container->addSubmit('remove', $title)
				// ->setIcon("trash")
				->setHtmlAttribute('class', 'btn btn-danger removeBtn' . ($snippetName ? ' ajax' : ''))
				->setValidationScope([])
				->addRemoveOnClick();

		$snippetName ? $btn->setHtmlAttribute('data-ajax-confirm', 'Přejete si položku odebrat / smazat?') : $btn->setHtmlAttribute('onClick', 'return confirm("Přejete si položku odebrat / smazat?")');

		$form = $container->getForm(false);
		if ($form && $snippetName) { // A zároveň musí být nastven snippet $name a snippetArea form !!! +  ->setHtmlAttribute('class', 'btn btn-outline-danger removeBtn ajax')
			$container->getComponent("remove")
			->onClick[] = function (\Nette\Forms\Controls\SubmitButton $button) use ($form, $snippetName, $container) {
				$presenter = $form->getPresenter();
				if ($presenter->isAjax()) {
					$presenter->redrawControl("form");
					$presenter->redrawControl($snippetName);
				}
			};
		}
		return $container->getComponent("remove");
	}

	/**
	 * Pomocná funkce pro přidání add tlačítka pro dynamické prvky/Containery
	 * @param \Kdyby\Replicator\Container $dynamic
	 */
	private function addAddingButton(\Kdyby\Replicator\Container $dynamic, string $title = 'Přidat další'): \Nette\Forms\Controls\SubmitButton {
		$dynamic->addSubmit('add', $title)
				->setAttribute('class', 'btn btn-success btn-block addBtn') //ajax
				->setValidationScope([])
				->addCreateOnClick();

		$dynamic->getComponent("add")
		//->setAttribute('class', 'btn btn-outline-success addBtn ajax')
		->onClick[] = function () use ($dynamic) {
			$presenter = $dynamic->getForm()->getPresenter();
			if ($presenter->isAjax()) {
				$presenter->redrawControl("form");
				$presenter->redrawControl($this->getBaseReplicatorName($dynamic));
			}
		};
		return $dynamic->getComponent("add");
	}
	/**
	 * Summary of setMultiLekyForm
	 * @param \Nette\Application\UI\Form $form
	 * @return void
	 */
	public function setMultiLekyForm(Form $form) {
		$form->addHidden('ID')
				->setRequired('Musí být zadaný "%label"');

		$form->addMultiSelect('ORGANIZACE', 'Organizace')
				->setHtmlAttribute('class', 'multiselect')
				->setItems(LekyPresenter::ORGANIZACE)
				->addCondition(Form::EQUAL, '')
				->toggle('vse') // všechno i organizace
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('RNB', 'MUS', 'DCNH'))
				->toggle('NH')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('NH', 'MUS', 'DCNH'))
				->toggle('RNB')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('NH', 'RNB', 'DCNH'))
				->toggle('MUS')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('NH', 'RNB', 'MUS'))
				->toggle('DCNH')
				->endCondition();

		$form->addMultiSelect('POJ', 'Pojišťovny')
				->setHtmlAttribute('class', 'multiselect')
				->setItems(LekyPresenter::POJISTOVNY)
				->addCondition(Form::EQUAL, '')
				->toggle('p0_pojistovna') // všechno je pro pojišťovny
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('201', '205', '207', '209', '211', '213'))
				->toggle('p111_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '205', '207', '209', '211', '213'))
				->toggle('p201_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '207', '209', '211', '213'))
				->toggle('p205_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '205', '209', '211', '213'))
				->toggle('p207_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '205', '207', '211', '213'))
				->toggle('p209_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '205', '207', '209', '213'))
				->toggle('p211_pojistovna')
				->endCondition()
				->addCondition(Form::IS_NOT_IN, array('111', '201', '205', '207', '209', '211'))
				->toggle('p213_pojistovna')
				->endCondition();

		foreach (self::ORGANIZACE as $organizace) {
			$contOrg = $form->addContainer($organizace);
			foreach (self::POJ as $pojistovna) {
				$this->setPojistovnaContainer($contOrg->addContainer($pojistovna), $organizace, $pojistovna);
			}
			$this->setPojistovnaContainer($contOrg->addContainer('0'), $organizace, '0');
		}
		$this->setPojistovnaContainer($form->addContainer(0)->addContainer(0), '0', '0');
	}

 /**
  * Summary of setLekyAtcSkupForm
  * @param \Nette\Application\UI\Form $form
  * @return void
  */
	public function setLekyAtcSkupForm(Form $form) {

		$form->addText('ATC1', 'Indikační skupina')
				->setRequired('Musí být vyplněný "%label"')
				->addRule(Form::MAX_LENGTH, 'Maximální délka pole "%label" je %d znak.', 1)
				->setNullable();

		$form->addText('POPIS', 'Popis')
				->setNullable();

		$form->addText('LECBA', 'Léčba')
				->setNullable();

		$form->addText('PLATNOST_OD', 'Platnost od')
				->setRequired('Musí být vyplněný "%label"')
				->setType('date')
				->setDefaultValue((new \DateTime)->format('Y-m-d'));

		$form->addText('PLATNOST_DO', 'Platnost do')
				->setType('date')
				->setNullable();

		$form->onValidate[] = function ($form) {
			$validate = $form->values;
			if ($validate->PLATNOST_DO) {
				if ($validate->PLATNOST_OD > $validate->PLATNOST_DO) {
					$form->addError("Není validní Platnost do");
				}
			}
		};
	}

	public function setHromadDiagForm(Form $form) {
		$form->addHidden('ID')
				->setRequired('Musí být zadaný "%label"');

		$form->addMultiSelect('ORGANIZACE', 'Organizace')
				->setHtmlAttribute('class', 'multiselect')
				->setItems(LekyPresenter::ORGANIZACE)
				->setRequired('Musí být zadaný "%label"');

		$form->addMultiSelect('POJ', 'Pojišťovny')
				->setHtmlAttribute('class', 'multiselect')
				->setItems(LekyPresenter::POJISTOVNY)
				->setRequired('Musí být zadaný "%label"');

		$this->addAddingButton(
				$form->addDynamic('DG', function (Container $cont): void {

				$cont->addText("DG_NAZEV", "DG Název")
					->setHtmlAttribute('data-autocomplete-dg')
					->setNullable();

				$cont->addCheckbox('VILP', '')
					->setHtmlAttribute('class', 'checkbox_style');

				$cont->addText('DG_PLATNOST_OD', 'DG skupina od')
					->setType('date')
					->setNullable();

				$cont->addText('DG_PLATNOST_DO', 'DG skupina do')
					->setType('date')
					->setNullable();

				$this->addRemoveButton($cont);


			})
		);
	}

}
