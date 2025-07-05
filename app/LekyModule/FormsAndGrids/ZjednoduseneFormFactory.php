<?php

declare(strict_types=1);

namespace App\LekyModule\Forms;

use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Security\User;

\Nette\Forms\Controls\BaseControl::$idMask = '%s';

class ZjednoduseneFormFactory {

    public function __construct(User $user, \Dibi\Connection $db, Array $parameters) {
        // constructor
    }
public function setZjednoduseneForm(\Nette\Application\UI\Form $form) {
    
    $form->addHidden('ID_LEKY');

     $form->addText('ATC', 'ATC skupina')
         ->addRule(Form::MAX_LENGTH, 'Maximální délka pole "%label" je %d znaků.', 7)
         ->setNullable();


    $form->addText('NAZ', 'Název')
         ->setRequired('Musí být vyplněný "%label"')
         ->addRule(Form::MAX_LENGTH, 'Maximální délka pole "%label" je %d znaků.', 70);

    $form->addTextArea('POZNAMKA', 'Poznámka pro všechny ZP')
         ->setNullable();

    $form->addTextArea('UCINNA_LATKA', 'Učinná látka')
         ->setNullable();

    $form->addTextArea('BIOSIMOLAR', 'Biosimilar')
         ->setNullable();

   
    $form->addMultiSelect('ORGANIZACE', 'Organizace')
         ->setHtmlAttribute('class', 'multiselect')
         ->setItems(\App\LekyModule\Presenters\ZjednodusenePresenter::ORGANIZACE)
         ->setRequired('Musí být vybrána alespoň jedna organizace');

   
    $form->addMultiSelect('POJ', 'Pojišťovny')
         ->setHtmlAttribute('class', 'multiselect')
         ->setItems(\App\LekyModule\Presenters\ZjednodusenePresenter::POJISTOVNY)
         ->addCondition(Form::EQUAL, '')
         ->toggle('p0_pojistovna') 
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

 foreach (['NH', 'RNB', 'MUS', 'DCNH'] as $organizace) {
    $contOrg = $form->addContainer($organizace);
    foreach (['111', '201', '205', '207', '209', '211', '213'] as $pojistovna) {
        $this->setPojistovnaContainer($contOrg->addContainer($pojistovna), $organizace, $pojistovna);
    }
}
}

    private function setPojistovnaContainer(Container $container, $org, $poj) {

        $container->addSelect('STAV', 'Stav')
                ->setItems(\App\LekyModule\Presenters\ZjednodusenePresenter::STAV)
                ->addCondition(Form::EQUAL, 'Nasmlouváno')
                ->toggle('NASMLOUVANO_DATA' . $org . $poj)
                ->endCondition();

        $container->addText('NASMLOUVANO_OD', 'Nasmlouváno od')
                ->setType('date')
                ->setNullable();

        $container->addCheckbox('Revizak', '')
                ->setHtmlAttribute("class", "tgl-btn")
                ->addCondition(Form::EQUAL, true)
                ->toggle('revizak' . $org . $poj)
                ->endCondition();

        $container->addSelect('RL', 'Revizní lékař')
                ->setItems(["" => "-- Vyber te možnost -- ", "0" => "povolení RL- žádanka §16", "1" => "epikríza/info pro RL"]);

        $this->addAddingButton(
                $container->addDynamic('DG', function (Container $cont): void {

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

        $container->addText('POZNAMKA', 'Poznámka')
                ->addRule(Form::MAX_LENGTH, 'Maximální délka pole "%label" je %d znaků.', 255)
                ->setNullable();
    }

    private function addRemoveButton(Container $container, string $title = 'Odebrat', string $snippetName = null): \Nette\Forms\Controls\SubmitButton {
        $btn = $container->addSubmit('remove', $title)
                ->setHtmlAttribute('class', 'btn btn-danger removeBtn' . ($snippetName ? ' ajax' : ''))
                ->setValidationScope([])
                ->addRemoveOnClick();

        $snippetName ? $btn->setHtmlAttribute('data-ajax-confirm', 'Přejete si položku odebrat / smazat?') : $btn->setHtmlAttribute('onClick', 'return confirm("Přejete si položku odebrat / smazat?")');

        $form = $container->getForm(false);
        if ($form && $snippetName) {
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

    private function addAddingButton(\Kdyby\Replicator\Container $dynamic, string $title = 'Přidat další'): \Nette\Forms\Controls\SubmitButton {
        $dynamic->addSubmit('add', $title)
                ->setAttribute('class', 'btn btn-success btn-block addBtn')
                ->setValidationScope([])
                ->addCreateOnClick();

        $dynamic->getComponent("add")
        ->onClick[] = function () use ($dynamic) {
            $presenter = $dynamic->getForm()->getPresenter();
            if ($presenter->isAjax()) {
                $presenter->redrawControl("form");
            }
        };
        return $dynamic->getComponent("add");
    }

    public function setHromadDiagForm(\Nette\Application\UI\Form $form) {
        $form->addHidden('ID')
             ->setRequired('Musí být zadaný "%label"');

        $form->addMultiSelect('ORGANIZACE', 'Organizace')
             ->setHtmlAttribute('class', 'multiselect')
             ->setItems(\App\LekyModule\Presenters\ZjednodusenePresenter::ORGANIZACE)
             ->setRequired('Musí být zadaný "%label"');

        $form->addMultiSelect('POJ', 'Pojišťovny')
             ->setHtmlAttribute('class', 'multiselect')
             ->setItems(\App\LekyModule\Presenters\ZjednodusenePresenter::POJISTOVNY)
             ->setRequired('Musí být zadaný "%label"');

        $form->addText('DG_NAZEV', 'DG Název')
             ->setHtmlAttribute('data-autocomplete-dg')
             ->setNullable();

        $form->addCheckbox('VILP', 'VILP')
             ->setHtmlAttribute('class', 'checkbox_style');

        $form->addText('DG_PLATNOST_OD', 'Platnost od')
             ->setHtmlType('date')
             ->setNullable();

        $form->addText('DG_PLATNOST_DO', 'Platnost do')
             ->setHtmlType('date')
             ->setNullable();
    }
}