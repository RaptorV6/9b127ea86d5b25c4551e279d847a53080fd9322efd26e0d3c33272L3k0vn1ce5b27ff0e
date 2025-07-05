<?php
namespace App\CiselnikyModule\Grids;

use Ublaboo\DataGrid\AkesoGrid,
    Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

use App\CiselnikyModule\Model\Ciselniky,
    App\Components\Model\Ciselnik;
use Ublaboo\DataGrid\DataGrid;

/**
 * Třída s Gridy pro autopark
 *
 * @author Vítek Šmíd
 */

class CiselnikyGridFactory extends \App\Factory\BaseDataGridFactory
{

    public function __construct(\Nette\Security\User $user, \Dibi\Connection $db, array $parameters) {
        //parent::__construct($user, $db, $parameters);
    }  
	public function setBaseStart(DataGrid $grid) {
		$grid->setPrimaryKey("ID");
        $grid->setStrictSessionFilterValues(false);

        $grid->setColumnsHideable();
		
        $grid->setDefaultSort(array('ID' => 'DESC'));
		
		 $grid->addColumnText('ID', '#')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
	}
	public function setBaseEnd(DataGrid $grid) {

       $grid->addToolbarButton("new")
            ->setText("Nový záznam")
            ->setClass("btn btn-success")
            ->setIcon("plus")
            ->setTitle("Přidá nový");

        $grid->addAction('edit', 'Editace', 'edit')
             ->setClass("btn btn-warning")   
             ->setIcon("pencil");
        
	}

	public function setDetindGrid(DataGrid $grid) : DataGrid{
		$this->setBaseStart($grid); 
		 
        $grid->addColumnText('KOD', 'Kód')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('NAZ', 'Název')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('SILA', 'Síla')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
		        
        $grid->addColumnText('FORMA', 'Kód')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('BALENI', 'Název')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('CESTA', 'Síla')
             ->setFitContent()
             ->setSortable()
             ->setFilterText(); 
		
        $grid->addColumnText('ATC', 'ATC')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
		$grid->setItemsDetail(true, "ID")
             ->setClass("btn btn-outline-primary btn-sm ajax")
             ->setTitle("Zobrazit indikační omezení")
			 ->setText("Indikační omezení")
             ->setIcon("arrow-down")
			 ->setType("renderer")
			 ->setRenderer(function($row) {
					return \Nette\Utils\Html::el("font")->size('4')->setText($row->INDIKACNI_OMEZENI);
			});
				
        $this->setBaseEnd($grid);
		return $grid;
    }
	public function setAtcsGrid(DataGrid $grid) : DataGrid{
	    $this->setBaseStart($grid); 
		
        $grid->addColumnText('ID_SADY', 'Sada')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('ATC', 'Atc')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('ANGL_NAZEV', 'Anglický název')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
		        
        $grid->addColumnText('CESKY_NAZEV', 'Český')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
		
		$this->setBaseEnd($grid);
		 return $grid;
	}
	public function setSymbolGrid(DataGrid $grid) : DataGrid{
	    $this->setBaseStart($grid); 
		
        $grid->addColumnText('SYMBOL', 'Symbol')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('VYZNAM', 'Význam')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
		$this->setBaseEnd($grid);
		return $grid;
	}
	public function setDgGrid(DataGrid $grid) : DataGrid{
	    $this->setBaseStart($grid); 
		
        $grid->addColumnText('KOD_SKUP', 'Kód')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('NAZEV_SKUP', 'Název')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
		$this->setBaseEnd($grid);
		return $grid;
	}
}
