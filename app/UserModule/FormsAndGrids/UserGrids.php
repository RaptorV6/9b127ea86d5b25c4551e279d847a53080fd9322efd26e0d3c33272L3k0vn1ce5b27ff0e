<?php
namespace App\UserModule\Grids;

use Ublaboo\DataGrid\AkesoGrid,
    Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

use App\UserModule\Model\User,
    App\Components\Model\Ciselnik;
use App\UserModule\Presenters\UserPresenter;

/**
 * Třída s Gridy pro autopark
 *
 * @author Vítek Šmíd
 */

class UserGridFactory extends \App\Factory\BaseDataGridFactory
{

    public function __construct(\Nette\Security\User $user, \Dibi\Connection $db, array $parameters) {
        //parent::__construct($user, $db, $parameters);
    }  
    public function setUserGrid(\Ublaboo\DataGrid\DataGrid $grid) : \Ublaboo\DataGrid\DataGrid{
        $grid->setPrimaryKey("id");
        $grid->setStrictSessionFilterValues(false);
        $grid->setColumnsHideable();
        
        $grid->addColumnText('osobni_cislo', 'Osobní číslo')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('jmeno', 'Jméno')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
        $grid->addColumnText('prijmeni', 'Příjmení')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
                
        $grid->addColumnText('prava', 'Práva')
             ->setFitContent()
             ->setSortable()
             ->setReplacement(UserPresenter::PRAVA)
             ->setFilterText();
        
        $grid->addColumnText('active', 'Aktivní uživatel')
             ->setFitContent()
             ->setSortable()
             ->setReplacement(UserPresenter::ANO_NE)
             ->setFilterText();
		
		$grid->addColumnText('modul_poj', 'Pojišťovny')
             ->setFitContent()
             ->setSortable()
             ->setReplacement(UserPresenter::ANO_NE)
             ->setFilterText();
				
		$grid->addColumnText('modul_fin', 'Finnance')
             ->setFitContent()
             ->setSortable()
             ->setReplacement(UserPresenter::ANO_NE)
             ->setFilterText();
						
		$grid->addColumnText('modul_lek', 'Lékař')
             ->setFitContent()
             ->setSortable()
             ->setReplacement(UserPresenter::ANO_NE)
             ->setFilterText();

          $grid->addColumnText('preferovana_organizace', 'Preferovaná organizace')
             ->setFitContent()
             ->setSortable()
             ->setFilterText();
        
       $grid->addToolbarButton("new")
            ->setText("Nový uživatel")
            ->setClass("btn btn-success")
            ->setIcon("plus")
            ->setTitle("Přidá nového uživatele");

        $grid->addAction('edit', 'Editace', 'edit')
             ->setClass("btn btn-secondary")   
             ->setIcon("pencil");
        
        return $grid;
    }
}
