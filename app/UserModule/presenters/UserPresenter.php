<?php
namespace App\UserModule\Presenters;

use Nette\Application\UI\Form,
    Ublaboo\DataGrid\DataGrid;

use    App\UserModule\Model\User;

    \Nette\Forms\Controls\BaseControl::$idMask = '%s';
    
use Nette\Security\Passwords;
use Ublaboo\DataGrid\AkesoGrid;

class UserPresenter extends \App\Presenters\SecurePresenter
{
    const PRAVA = array('1'=>'Nahlížet','2'=>'Editovat/Vytvářet','9'=>'Admin'),
          ORGANIZACE = array('RNB'=>'Rehabilitační Nemocnice Beroun','NH'=>'Nemocnice Hořovice','DCNH'=>'Diagnostické Centrum Nemocnice Hořovice','MUS'=>'Multiscan'),
          ANO_NE = array(true=>'Ano',false=>'Ne');

    /** @var \App\UserModule\Grids\UserGridFactory @inject */
    public $GridFactory;
    
    /** @var \App\UserModule\Forms\UserFormFactory @inject */
    public $FormFactory;
    
    /** @var \App\UserModule\Model\User @inject */
    public $BaseModel;
    
    /** @var \App\LogyModule\Model\Logy @inject */
    public $LogyModel;
    
    public function startup() {
        parent::startup();
    }
    
    protected function createComponentUserDataGrid(string $name){
        $grid = new AkesoGrid($this,$name);   
        $this->GridFactory->setUserGrid($grid);
        $grid->addActionCallback('delete', 'Smazat', function($id){
            $this->BaseModel->deleteUser($id);
            $this->redirect("this");
        })
             ->setClass("btn btn-danger")
             ->setIcon("trash")
             ->setConfirmation(
                    new \Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation('Opravdu chcete smazat uživatele?')
             );
        $grid->setDataSource($this->BaseModel->getDataSource());    
        return $grid;
    }
    protected function createComponentUserForm(string $name){
        $form = new \Nette\Application\UI\Form($this, $name);
        $user = $this->BaseModel->getUser($this->getParameter('id'));

        if (isset($user->preferovana_organizace)) {
            if($user->preferovana_organizace ===''){
                unset($user->preferovana_organizace);
            } else {
                $user->preferovana_organizace = explode(', ', $user->preferovana_organizace);
            }
        } 
        $this->FormFactory->setUserForm($form);
        $form->setDefaults($user);
        $form->addGroup();
        $form->addSubmit('send', 'Uložit')
             ->setHtmlAttribute('class ', 'btn btn-success button btn-block');
        $form->onSuccess[] = [$this, "userFormSucceeded"];
        return $form;
    }
    protected function createComponentPasswordForm(string $name){
        $form = new \Nette\Application\UI\Form($this, $name);
        $this->FormFactory->setPasswordForm($form, $this->user->getId());
        $user = $this->BaseModel->getUser($this->getParameter('id'));
        $form->onSuccess[] = [$this, "changePasswordFormSucceeded"];
    }
	
	public function renderDefault() {
		$this->template->nadpis = 'uživatelé';
	}




	public function renderNew() {
        $this->template->nadpis = 'přidání nového uživatele';
        $this->setView('edit');
    }    
    
    /**
    * Nový formulář autoparku - přidání do vozového parku
    */
    public function renderEdit() {
        $this->template->nadpis = 'editace stavajícího uživatele';
    }
    public function changePasswordFormSucceeded($form)
    {
        $values = $form->getValues();
        $values->id = $this->user->getIdentity()->identifikator;
        $values->heslo = password_hash($values->password,PASSWORD_BCRYPT);
        unset($values->password);
        unset($values->passwordVerify);
        $this->BaseModel->updateUser($values);
        $this->flashMessage('Záznam byl upraven.', "success");
    }
    public function userFormSucceeded($form)
    {
        $values = $form->getValues();
        if (isset($values->preferovana_organizace)) {
            $values->preferovana_organizace = implode(', ', $values->preferovana_organizace);
        }
        else{
            unset($values->preferovana_organizace);
        }
        // $this->LogyModel->insertUserLog(\App\UserModule\Model\User::USER, $values, $this->user->getId()); odkomentovat
        $select = $this->BaseModel->getUserKontrola($values->osobni_cislo);
		if($values->prava == '1'){
			$values->modul_poj = false;
			$values->modul_fin = false;
			$values->modul_lek = false;
		}
/*		if($values->prava == '9'){ odkomentovat
			$values->modul_poj = true;
			$values->modul_fin = true;
			$values->modul_lek = true;
		}*/
        if($values->password){
            $values->heslo = password_hash($values->password,PASSWORD_BCRYPT);
        }
        unset($values->password);
        if(!(int)($values->id))
        {
            if($select){
                $this->flashMessage('Záznam již existuje', "danger");
            }
            else {
                unset($values->id);
                $this->BaseModel->setUser($values);
                $this->flashMessage('Záznam byl přidan.', "success");
                $this->redirect("default");
            }
        }
        else {
            $this->BaseModel->updateUser($values);
            $this->flashMessage('Záznam byl upraven.', "success");
            $this->redirect("default");
        }
    }
}