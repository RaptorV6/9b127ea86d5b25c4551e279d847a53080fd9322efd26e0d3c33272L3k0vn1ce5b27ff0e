<?php

namespace App\Presenters;

use App\Model\Errors;
use App\Model\Auth;
use App\Model\EZD;
use Nette\Application\UI\Form;
use Nette\Application\UI;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;


/**
 * Základní stránka - výběr Dokumnetace k odeslání
 */

final class HomepagePresenter extends BasePresenter{
        
        private $userId;
        private $userPassword;
        
        public function startup() {
            parent::startup();
			$this->template->nadpis = 'přihlášení';
            $this->template->userId = $this->user->getId();
        }
        
        public function actionlogOut() {
            $this->Auth->logout();
            $this->redirect(':Homepage:default');
        }
        
     public function createComponentLogInForm(string $name) : Form{
    $form = new Form($this, $name);
    
    $form->addText("userId", 'Osobní číslo')
            ->setNullable()
            ->setHtmlAttribute("class", "form-control login")
            ->addRule(Form::FILLED, 'Vyplňte osobní číslo')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka emailu je %d znaků.', 60);
    
    $form->addPassword("userPassword", 'Heslo')
            ->setNullable()
            ->setHtmlAttribute("class", "form-control login")
            ->addRule(Form::FILLED, 'Vyplňte heslo.');
    
    $form->addSubmit("loginSubmit", "Přihlásit se")
         ->setHtmlAttribute("style","margin-bottom: 2%;")
         ->setHtmlAttribute("class", "btn btn-primary");
    
    $form->onSuccess[] = function(Form $form){
        $values = $form->getValues();
        if($this->Auth->login($values->userId, $values->userPassword)) {
            
            $authSession = $this->getSession('auth');
            $returnModule = $authSession->returnModule ?? 'leky'; // výchozí = leky
            
            if ($returnModule === 'zjednodusene') {
                $this->redirect(":Leky:Zjednodusene:default");
            } else {
                $this->redirect(":Leky:Leky:default");
            }
            
        } else {
            $form->getComponent("userId")->addError("Chyba");
            $form->getComponent("userPassword")->addError("Špatné jméno nebo heslo");
        }
    };
    
    return $form;
}
     
}
