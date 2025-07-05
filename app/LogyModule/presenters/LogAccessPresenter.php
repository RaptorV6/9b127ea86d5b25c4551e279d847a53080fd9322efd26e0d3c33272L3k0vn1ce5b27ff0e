<?php

namespace App\LogyModule\Presenters;

use Ublaboo\DataGrid\DataGrid;

/**
 * Presenter logu odeslaných zpráv
 */
class LogAccessPresenter extends \App\Presenters\SecurePresenter
{   
    /** @var \App\LogyModule\Model\LogAccessModel @inject */
    public $logAccessModel;
    
    public function createComponentLogAccessGrid($name)
    {
        $grid = new DataGrid($this,$name);
		$grid->setPrimaryKey('time'); 
        $fluent = $this->logAccessModel->findLogAccess();

        $grid->setDataSource($fluent);
        
        $grid->setDefaultSort(array('time' => 'DESC'));
        
        $grid->addColumnDateTime('time', 'Datum a čas')
			->setFormat('d.m.Y H:i:s')
            ->setSortable()
			->setFilterText();
        
        $grid->addColumnText('uri', 'URI')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('user', 'Uživatel')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('ip', 'IP adresa')
            ->setSortable()
            ->setFilterText();
        
    }
}
