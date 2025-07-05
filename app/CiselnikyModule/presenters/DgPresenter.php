<?php
namespace App\CiselnikyModule\Presenters;

use Nette\Application\UI\Form,
    Ublaboo\DataGrid\DataGrid;

use App\CiselnikyModule\Model\Ciselniky;
    \Nette\Forms\Controls\BaseControl::$idMask = '%s';

class DgPresenter extends SharePresenter
{
	const DG = 'AKESO_LEKY_DG_SKUP';

    /** @var \App\CiselnikyModule\Grids\CiselnikyGridFactory @inject */
    public $GridFactory;
    
    /** @var \App\CiselnikyModule\Forms\CiselnikyFormFactory @inject */
    public $FormFactory;
    
    /** @var \App\CiselnikyModule\Model\Ciselniky @inject */
    public $BaseModel;
    
    public function startup() {
        parent::startup();
    }
	public function renderDefault() {
		$this->template->nadpis = 'dg skupina';
	}
    
    protected function createComponentDgDataGrid(string $name){
        $grid = new \Ublaboo\DataGrid\AkesoGrid($this, $name);   
        $this->GridFactory->setDgGrid($grid);
        $grid->setDataSource($this->BaseModel->getDataSource(self::DG));    
        return $grid;
    }
    protected function createComponentDgForm(string $name){
        $form = new \Nette\Application\UI\Form($this, $name);
        $cis = $this->BaseModel->getEditView($this->getParameter('ID'),self::DG);
        $this->FormFactory->setDgForm($form);
        $form->setDefaults($cis);
        $form->addGroup();
        $form->addSubmit('send', 'Uložit')
             ->setHtmlAttribute('class ', 'btn btn-success button btn-block');
		$form->onSuccess[] = function(Form $form) {
            $this->FormSucceeded($form, self::DG);
        };
        return $form;
    }
	
    
}