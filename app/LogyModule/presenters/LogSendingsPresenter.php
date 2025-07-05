<?php

namespace App\LogyModule\Presenters;

use Ublaboo\DataGrid\DataGrid;

/**
 * Presenter logu odeslaných zpráv
 */
class LogSendingsPresenter extends \App\Presenters\SecurePresenter
{
    /** @var \App\LogyModule\Model\LogSendingsModel @inject */
    public $LogSendings;
            
    public function createComponentLogSendingsGrid($name)
    {
        $grid = new DataGrid($this,$name);

        $fluent = $this->LogSendings->findLogSendings();
		$grid->setPrimaryKey('time');
		
        $grid->setDataSource($fluent);


        $grid->setDefaultSort(array('time' => 'DESC'));

        $grid->addColumnDateTime('time', 'Datum a čas')
			->setFormat('d.m.Y H:i:s')
            ->setSortable()
			->setFilterText();
        
        $grid->addColumnText('type', 'Typ')
            ->setSortable()
            ->setFilterSelect(array("" => "--vyberte--", "email" => "email", "sms" => "sms"));
        
        $grid->addColumnText('recipient', 'Adresát(i)')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('subject', 'Předmět zprávy')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('content', 'Obsah')
            ->setSortable()
            ->setFilterText();
    }
}
