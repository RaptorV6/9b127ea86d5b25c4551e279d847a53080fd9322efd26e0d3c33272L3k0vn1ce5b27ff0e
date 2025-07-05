<?php

namespace App\LogyModule\Presenters;

use Ublaboo\DataGrid\DataGrid;

/**
 * Presenter logu výjimek
 */
class LogExceptionsPresenter extends \App\Presenters\SecurePresenter
{
    /** @var \App\LogyModule\Model\LogExceptionsModel @inject */
    public $LogExceptions;
    
    public function createComponentLogExceptionsGrid($name)
    {
        $grid = new DataGrid($this,$name);

        $exceptionsFluent = $this->LogExceptions->findLogExceptions();
        $exceptionTypesRows = $this->LogExceptions->findExceptionTypes()->fetchAll();
        
        $priorities = array("debug", "info", "warning", "error", "exception", "critical");
        $prioritiesArray = array_merge(array("" => "--vyberte--"), array_combine($priorities, $priorities));
        
        $exceptionTypes = array("" => "--vyberte--");
        foreach($exceptionTypesRows as $exceptionTypesRow) {
            $exceptionTypes[$exceptionTypesRow['type']] = $exceptionTypesRow['type'];
        }
		
        $grid->setPrimaryKey('time'); 
        $grid->setDataSource($exceptionsFluent);
		
        $grid->setDefaultSort(array('time' => 'DESC'));

        $grid->addColumnDateTime('time', 'Datum a čas')
			->setFormat('d.m.Y H:i:s')
            ->setSortable()
			->setFilterText();

        $grid->addColumnText('priority', 'Priorita')
            ->setSortable()
            ->setFilterSelect($prioritiesArray);
        
        $grid->addColumnText('type', 'Typ')
            ->setSortable()
            ->setFilterSelect($exceptionTypes);
        
        $grid->addColumnText('uri', 'URI')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('req_variables', 'Request proměnné')
  /*          ->setCustomRender(function($row) {    
                if($row['req_variables']!=null) {
                    return self::wrapDataToBoxInfo($row['req_variables']);
                } else {
                    return null;
                }
            })*/
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('message', 'Předmět zprávy')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('file', 'Soubor')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnNumber('line', 'Řádek')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('source_code', 'Zdrojový kód')
    /*        ->setCustomRender(function($row) {
                if($row['source_code']!=null) {
                    return self::wrapDataToBoxInfo($row['source_code']);
                } else {
                    return null;
                }
            })*/
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('user_name', 'Uživatel')
            ->setSortable()
            ->setFilterText();
        
        $grid->addColumnText('addit_info', 'Dopl. inf. o výjimce')
          /*  ->setCustomRender(function($row) {
                if($row['source_code']!=null) {
                    return self::wrapDataToBoxInfo($row['addit_info']);
                } else {
                    return null;
                }
            })*/
            ->setSortable()
            ->setFilterText();
    }
    
    public static function wrapDataToBoxInfo($data) {
        return '<div class="box-info-area btn btn-default">Zobrazit<div class="box-info">' . $data .'</div></div>';
    }
}
