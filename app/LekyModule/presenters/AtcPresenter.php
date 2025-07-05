<?php
namespace App\LekyModule\Presenters;

use Nette\Application\UI\Form,
    Ublaboo\DataGrid\DataGrid;

use App\LekyModule\Model\Leky;
    \Nette\Forms\Controls\BaseControl::$idMask = '%s';
    


class AtcPresenter extends \App\Presenters\SecurePresenter
{
    /** @var \App\LekyModule\Grids\LekyGridFactory @inject */
    public $GridFactory;
    
    /** @var \App\LekyModule\Forms\LekyFormFactory @inject */
    public $FormFactory;
    
    /** @var \App\LekyModule\Model\Leky @inject */
    public $BaseModel;
    
    /** @var \App\LogyModule\Model\Logy @inject */
    public $LogyModel;
                  
    public function startup() {
        parent::startup();
        $this->template->nadpis = 'léky';
    }
    
    protected function createComponentLekyAtcSkupDataGrid(string $name){
        $grid = new DataGrid($this,$name);   
        $this->GridFactory->setLekyAtcSkupGrid($grid, $this->user->getIdentity()->prava);
        $grid->setDataSource($this->BaseModel->getAtc_skup_data());    
        return $grid;
    }
    
    protected function createComponentLekyAtcSkupForm(string $name){
        $form = new \Nette\Application\UI\Form($this, $name);
        $lek = $this->BaseModel->getAtc_skup($this->getParameter('ATC1'));
        $this->FormFactory->setLekyAtcSkupForm($form);
        $form->setDefaults($lek);
        $form->addGroup();
        $form->addSubmit('send', 'Uložit')
             ->setHtmlAttribute('class ', 'btn btn-success button btn-block');
        $form->onSuccess[] = [$this, "lekyAtcSkupFormSucceeded"];
        return $form;
    }
    public function renderNew() {;
        $this->template->nadpis = 'přidání indukační skupiny';
        $this->setView('edit');
    }    
    public function renderEdit() {
        $this->template->nadpis = 'editace indukační skupiny';
    }	
	public function actionWeb($ATC1)
    {
		$this->redirectUrl('https://www.sukl.cz/modules/medication/atc_tree.php?current='.$ATC1);
	}		
    public function lekyAtcSkupFormSucceeded($form)
    {
        $values = $form->getValues();
		$this->LogyModel->insertLog(\App\LekyModule\Model\Leky::DG_SKUP , $values, $this->user->getId()); 
        $this->BaseModel->insertAtc($values);
        $this->flashMessage('Záznam byl přidan.', "success");
        $this->redirect(':Leky:Atc:default');
    }
}