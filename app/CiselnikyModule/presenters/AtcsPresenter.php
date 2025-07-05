<?php
namespace App\CiselnikyModule\Presenters;

use Nette\Application\UI\Form,
    Ublaboo\DataGrid\DataGrid;

use App\CiselnikyModule\Model\Ciselniky;
    \Nette\Forms\Controls\BaseControl::$idMask = '%s';

class AtcsPresenter extends SharePresenter
{
	const ATC = 'AKESO_LEKY_ATC';

    /** @var \App\CiselnikyModule\Grids\CiselnikyGridFactory @inject */
    public $GridFactory;
    
    /** @var \App\CiselnikyModule\Forms\CiselnikyFormFactory @inject */
    public $FormFactory;
    
    /** @var \App\CiselnikyModule\Model\Ciselniky @inject */
    public $BaseModel;
    
    public function startup() {
        parent::startup();
		$this->template->nadpis = 'ATC';
    }
    
    protected function createComponentAtcsDataGrid(string $name){
        $grid = new \Ublaboo\DataGrid\AkesoGrid($this, $name);   
        $this->GridFactory->setAtcsGrid($grid);
        $grid->setDataSource($this->BaseModel->getDataSource(self::ATC));    
        return $grid;
    }
    protected function createComponentAtcsForm(string $name){
        $form = new \Nette\Application\UI\Form($this, $name);
        $cis = $this->BaseModel->getEditView($this->getParameter('ID'),self::ATC);
		if(!empty($cis)){
			foreach ($cis as $key => $value) {
				$cis[$key] = trim($value,"	");
			}
		}
        $this->FormFactory->setAtcsForm($form, $this->BaseModel->getsada());
        $form->setDefaults($cis);
        $form->addGroup();
        $form->addSubmit('send', 'Uložit')
             ->setHtmlAttribute('class ', 'btn btn-success button btn-block');
		$form->onSuccess[] = function(Form $form) {
            $this->FormSucceeded($form, self::ATC);
        };
        return $form;
    }
	
    
}