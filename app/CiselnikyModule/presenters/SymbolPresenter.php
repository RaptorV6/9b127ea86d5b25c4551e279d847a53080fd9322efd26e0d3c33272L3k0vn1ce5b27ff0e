<?php
namespace App\CiselnikyModule\Presenters;

use Nette\Application\UI\Form,
    Ublaboo\DataGrid\DataGrid;

use App\CiselnikyModule\Model\Ciselniky;
    \Nette\Forms\Controls\BaseControl::$idMask = '%s';

class SymbolPresenter extends SharePresenter
{
	const SYMBOL = 'AKESO_LEKY_SYMBOL';

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
		$this->template->nadpis = 'Symbol Léku';
	}
    protected function createComponentSymbolDataGrid(string $name){
        $grid = new \Ublaboo\DataGrid\AkesoGrid($this, $name);   
        $this->GridFactory->setSymbolGrid($grid);
        $grid->setDataSource($this->BaseModel->getDataSource(self::SYMBOL));    
        return $grid;
    }
    protected function createComponentSymbolForm(string $name){
        $form = new \Nette\Application\UI\Form($this, $name);
        $cis = $this->BaseModel->getEditView($this->getParameter('ID'),self::SYMBOL);
		if(!empty($cis)){
			foreach ($cis as $key => $value) {
				$cis[$key] = trim($value,"	");
			}
		}
        $this->FormFactory->setSymbolForm($form,1);
        $form->setDefaults($cis);
        $form->addGroup();
        $form->addSubmit('send', 'Uložit')
             ->setHtmlAttribute('class ', 'btn btn-success button btn-block');
		$form->onSuccess[] = function(Form $form) {
            $this->FormSucceeded($form, self::SYMBOL);
        };
        return $form;
    }
	
    
}