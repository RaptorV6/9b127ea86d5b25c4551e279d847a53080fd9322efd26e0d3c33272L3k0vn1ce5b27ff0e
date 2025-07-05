<?php

namespace App\UserModule\Forms;

use Nette\Application\UI\Form,
    Nette\Forms\Container,
    Nette\Security\User,
    App\Components\Model\Ciselnik;

    \Nette\Forms\Controls\BaseControl::$idMask = '%s';    
// use Nette\Security\Passwords;

class UserFormFactory 
{
    
    public function __construct(User $user, \Dibi\Connection $db, Array $parameters) {
       // parent::__construct($user, $db, $parameters);
    }
        
    public function setUserForm(\Nette\Application\UI\Form $form) {
          //password_hash($this->getParameter('ID_LEKY'),PASSWORD_BCRYPT)
        $form->addHidden('id','id');
                
        $form->addText('osobni_cislo', 'Osobní číslo')
             ->setRequired('Zvolte "%label"');
        
        $form->addText('jmeno', 'Jméno')
             ->setRequired('Zvolte "%label"');
        
        $form->addText('prijmeni', 'Příjmení')
             ->setRequired('Zvolte "%label"');
        
        $form->addPassword('password', 'Heslo:')
             ->setHtmlId("password")
             ->addRule(Form::MIN_LENGTH, 'Heslo musí mít minimálně %d znaků', 5);
        //     ->addRule(Form::PATTERN, 'Heslo nesmí obsahovat osobní číslo', "^((?!$osc).)*$");
                
    /*    $form->addText('passwordVerify', 'Heslo pro kontrolu:')
             ->setHtmlId("passwordVerify")
             ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);
    */    
        $form->addSelect('prava', 'Práva')
             ->setItems(\App\UserModule\Presenters\UserPresenter::PRAVA)
			 ->addCondition(Form::IS_NOT_IN, '1')
			 ->toggle('poj')
			 ->toggle('fin')
			 ->toggle('lek')
             ->endCondition();
       
		
		$form->addCheckbox('modul_poj', '') 
             ->setHtmlAttribute('class', 'checkbox_style'); 

		$form->addCheckbox('modul_fin', '')   
             ->setHtmlAttribute('class', 'checkbox_style');
		
		$form->addCheckbox('modul_lek', '')
             ->setHtmlAttribute('class', 'checkbox_style'); 
		
        $form->addCheckbox('active', '')
             ->setDefaultValue(true)   
             ->setHtmlAttribute('class', 'checkbox_style');   
                
          $form->addMultiSelect('preferovana_organizace', 'Preferovaná organizace')
             ->setHtmlAttribute('class', 'multiselect')
             ->setHtmlAttribute('data-toggle', 'tooltip')
             ->setHtmlAttribute('data-placement', 'top')
             ->setHtmlAttribute('title', 'Preferovaná Organizace')
             ->setItems(\App\UserModule\Presenters\UserPresenter::ORGANIZACE);
        
    }


    public function setPasswordForm(\Nette\Application\UI\Form $form, $osc) {
        
        $form->addPassword('password', 'Heslo:')
             ->setRequired('Musí být vyplněný heslo')
             ->setHtmlId("password")
             ->addRule(Form::MIN_LENGTH, 'Heslo musí mít minimálně %d znaků', 5)
             ->addRule(Form::PATTERN, 'Heslo nesmí obsahovat osobní číslo', "^((?!$osc).)*$");
        
        $form->addPassword('passwordVerify', 'Heslo pro kontrolu:')
             ->setHtmlId("passwordVerify")
             ->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
             ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);
        
        $form->addSubmit('send', 'Uložit')
             ->setHtmlAttribute('class ', 'btn btn-success button btn-block');
    }
  
}