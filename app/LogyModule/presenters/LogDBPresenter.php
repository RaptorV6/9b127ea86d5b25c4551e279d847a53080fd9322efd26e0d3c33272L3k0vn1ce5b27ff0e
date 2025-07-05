<?php

namespace App\LogyModule\Presenters;

\Nette\Forms\Controls\BaseControl::$idMask = '%s';

use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;


class LogDBPresenter extends \App\Presenters\SecurePresenter
{
    /** @var \App\LogyModule\Model\LogDB @inject */
    public $LogDB;


    public function createComponentLogDBGrid($name)
    {
        $grid = new DataGrid($this,$name);
		
		$grid->setPrimaryKey('id');
		
        $fluent = $this->LogDB->findLogDB();
        $grid->setDataSource($fluent);


        $grid->setDefaultSort(array('datum' => 'DESC'));

		$grid->addColumnDateTime('datum', 'Datum a čas')
			->setFormat('d.m.Y H:i:s')
            ->setSortable()
			->setFilterText();

		$grid->addColumnText('userid', 'Uživatele ID')
            ->setSortable()
            ->setFilterText();
		
        $grid->addColumnText('user', 'Uživatel')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('tablename', 'Tabulka')
            ->setSortable()
            ->setFilterText();

		$grid->addColumnText('datavaluesold', 'Stará data')
            ->setSortable()
            ->setFilterText();
		
        $grid->addColumnText('datavaluesnew', 'Nová data')
            ->setSortable()
            ->setFilterText();

/*		$typzmeny = array('' => 'Vše', '1' => 'insert', '2' => 'delete', '3' => 'update');
        $grid->addColumnText('typ', 'Typ změny')
            ->setSortable()
            ->setFilterSelect($typzmeny);
*/
        $grid->addColumnText('ip', 'IP adresa')
            ->setSortable()
            ->setFilterText();
        
 /*       $grid->addActionHref('ShowDetail', 'Zobrazit detail')
            ->setIcon('pencil');       
   */     
    }

    public function actionShowDetail($id)
    {
		$typzmeny = array('' => 'Vše', '1' => 'insert', '2' => 'delete', '3' => 'update');
        $this->template->display = "dispBlock";
        $LogDB = $this->LogDB->findLogDBByID($id)->fetch();
        $this->template->showNotesList = True;
        $this->template->datumcas = $LogDB["datum"];
        $this->template->username = $LogDB["username"];
        $this->template->tablename = $LogDB["tablename"];
        $this->template->typzmeny = $LogDB["typ"];
        $this->template->typtxt = $typzmeny[$LogDB["typ"]];
        
        $zmeny = array();

        $old = json_decode($LogDB["datavaluesold"], true);
        if (!(empty($old))) 
        {
        foreach ($old as $field=>$value) 
          {
			if (strlen($value)>100) {$zmeny[$field]["old"] = substr($value,0,100).'....';} else {$zmeny[$field]["old"] = $value;}
//            $zmeny[$field]["old"] = $value;
          }
        }

        $new = json_decode($LogDB["datavaluesnew"], true);
        if (!(empty($new))) 
        {
          foreach ($new as $field=>$value) 
          {
			if (strlen($value)>100) {$zmeny[$field]["new"] = substr($value,0,100).'....';} else {$zmeny[$field]["new"] = $value;}
//            $zmeny[$field]["new"] = $value;
          }
        }
        
        $this->template->zmeny = $zmeny;
        
        $this->setView('default');
    }
    
    
public function createComponentShowDetail()
    {
        $form = new Form;
        $LogDB = $this->LogDB->findLogDBByID($this->getParameter('id'))->fetch();
        echo $LogDB["username"];
        return $form;
    }    

}
