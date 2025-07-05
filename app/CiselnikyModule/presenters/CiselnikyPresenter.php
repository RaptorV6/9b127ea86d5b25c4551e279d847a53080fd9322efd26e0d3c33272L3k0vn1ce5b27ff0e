<?php
namespace App\CiselnikyModule\Presenters;

use Nette\Application\UI\Form,
    Ublaboo\DataGrid\DataGrid;

use App\CiselnikyModule\Model\Ciselniky;
    \Nette\Forms\Controls\BaseControl::$idMask = '%s';

class CiselnikyPresenter extends SharePresenter
{
	const DETIND = 'AKESO_LEKY_DETIND';

    /** @var \App\CiselnikyModule\Grids\CiselnikyGridFactory @inject */
    public $GridFactory;
    
    /** @var \App\CiselnikyModule\Forms\CiselnikyFormFactory @inject */
    public $FormFactory;
    
    /** @var \App\CiselnikyModule\Model\Ciselniky @inject */
    public $BaseModel;
    
    public function startup() {
        parent::startup();
		$this->template->nadpis = 'DETIND';
    }
    
    protected function createComponentCiselnikyDataGrid(string $name){
        $grid = new \Ublaboo\DataGrid\AkesoGrid($this, $name);   
        $this->GridFactory->setDetindGrid($grid);
        $grid->setDataSource($this->BaseModel->getDataSource(self::DETIND));    
        return $grid;
    }
    protected function createComponentCiselnikyForm(string $name){
        $form = new \Nette\Application\UI\Form($this, $name);
        $cis = $this->BaseModel->getEditView($this->getParameter('ID'),self::DETIND);
		if(!empty($cis)){
			foreach ($cis as $key => $value) {
				$cis[$key] = trim($value,"	");
			}
		}
        $this->FormFactory->setCiselnikyForm($form);
        $form->setDefaults($cis);
        $form->addGroup();
        $form->addSubmit('send', 'Uložit')
             ->setHtmlAttribute('class ', 'btn btn-success button btn-block');
		$form->onSuccess[] = function(Form $form) {
            $this->FormSucceeded($form, self::DETIND);
        };
        return $form;
    }    
}