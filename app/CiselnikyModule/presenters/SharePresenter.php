<?php
namespace App\CiselnikyModule\Presenters;

use Nette\Application\UI\Form,
    Ublaboo\DataGrid\DataGrid;

use App\CiselnikyModule\Model\Ciselniky;

    \Nette\Forms\Controls\BaseControl::$idMask = '%s';
    
use Nette\Security\Passwords;

abstract class SharePresenter extends \App\Presenters\SecurePresenter
{

    public function startup() {
        parent::startup();
    }
	public function renderDefault() {
		$this->template->nadpis = 'menu';
	}
	public function renderNew() {
        $this->template->nadpis = 'přidání nového záznamu';
        $this->setView('edit');
    }    
    public function renderEdit() {
        $this->template->nadpis = 'editace stavajícího záznamu';
    }
	
	public function FormSucceeded($form,$table)
    {
        $values = $form->getValues();
        $this->LogyModel->insertUserLog(\App\UserModule\Model\User::USER, $values, $this->user->getId()); 
        if(!(int)($values->ID))
        {
			unset($values->ID);
			$this->BaseModel->setInsert($values,$table);
			$this->flashMessage('Záznam byl přidan.', "success");
			$this->redirect("default");
        }
        else {
			$ID = $values->ID;
			unset($values->ID);
            $this->BaseModel->setupdate($values,$table,$ID);
            $this->flashMessage('Záznam byl upraven.', "success");
            $this->redirect("default");
        }
    }
    
}