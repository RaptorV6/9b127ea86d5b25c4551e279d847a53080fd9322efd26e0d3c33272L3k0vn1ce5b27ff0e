<?php

namespace App\CiselnikyModule\Forms;

use Nette\Application\UI\Form,
    Nette\Forms\Container,
    Nette\Security\User,
    App\Components\Model\Ciselnik;

    \Nette\Forms\Controls\BaseControl::$idMask = '%s';    
// use Nette\Security\Passwords;

class CiselnikyFormFactory 
{
    
    public function __construct(User $user, \Dibi\Connection $db, Array $parameters) {
       // parent::__construct($user, $db, $parameters);
    }
        
    public function setCiselnikyForm(\Nette\Application\UI\Form $form) {

        $form->addHidden('ID','id');
                
        $form->addText('KOD', 'Kód')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 25)  
             ->setRequired('Zvolte "%label"');
        
        $form->addText('NAZ', 'Název')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 150)  
             ->setRequired('Zvolte "%label"');
        
        $form->addText('SILA', 'Síla')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 60)  
             ->setRequired('Zvolte "%label"');
        
        $form->addText('FORMA', 'Forma')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 100)  
			 ->setNullable();
		
		$form->addText('BALENI', 'Balení')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 90)  
			 ->setNullable();
		
		$form->addText('CESTA', 'Cesta')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 18)  
			 ->setNullable();
		
		$form->addText('ATC', 'ATC')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 50)  
			 ->setNullable();
		
		$form->addTextArea('INDIKACNI_OMEZENI', 'Indikační omezení')
			 ->setNullable();	
        
    }
	public function setAtcsForm(\Nette\Application\UI\Form $form, $SADA) {
		
		 $form->addHidden('ID','id');
                
        $form->addHidden('ID_SADY', 'SADA')
			 ->setDefaultValue($SADA);
        
        $form->addText('ATC', 'ATC')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 10)  
             ->setRequired('Zvolte "%label"');
        
        $form->addTextArea('ANGL_NAZEV', 'Anglický název')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 200)  
             ->setRequired('Zvolte "%label"');
		
		$form->addText('CESKY_NAZEV', 'Český název')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', 200)  
             ->setRequired('Zvolte "%label"');
	}
	public function setSymbolForm(\Nette\Application\UI\Form $form, $symbolvalue) {
		
		$form->addHidden('ID','id');
                       
        $form->addText('SYMBOL', 'Symbol')
			 ->addRule(Form::MAX_LENGTH,'Maximální délka pole "%label" je %d znak.', $symbolvalue)  
             ->setRequired('Zvolte "%label"');
        
        $form->addTextArea('VYZNAM', 'Význam')
             ->setRequired('Zvolte "%label"');

	}
	public function setDgForm(\Nette\Application\UI\Form $form) {
		
		$form->addHidden('ID','id');
                       
        $form->addText('KOD_SKUP', 'Kód')
             ->setRequired('Zvolte "%label"');
        
        $form->addTextArea('NAZEV_SKUP', 'Název')
             ->setRequired('Zvolte "%label"');

	}
	
	
  
}