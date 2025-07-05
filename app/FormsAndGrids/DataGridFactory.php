<?php
namespace App\Factory;

use     Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation,
        Ublaboo\DataGrid\DataGrid;
        
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DataGridFactory
 *
 * @author Administrator
 */
class DataGridFactory extends BaseDataGridFactory{


    public function setUserColunms(DataGrid $grid){
        $grid->setPrimaryKey('id');
                
        $grid->setColumnsHideable();

        $grid->addColumnNumber('id', '#')
                ->setFitContent()
                ->setSortable()
                ->setFilterText();

        $grid->addColumnText('name', 'Název')
                ->setSortable()
                ->setFilterText();

        $grid->addColumnText('tel', 'Telefon')
                ->setSortable()
                ->setFilterText();

        $grid->addColumnText('email', 'E-mail')
                ->setSortable()
                ->setFilterText();
        
        
        $grid->addInlineAdd()
             ->setClass('btn btn-primary');

        $grid->addInlineEdit()
             ->setClass('btn btn-primary');
        
        $grid->addActionCallback('del', 'Smazat')
                ->setClass('btn btn-primary')
                ->setConfirmation( new StringConfirmation("Opravdu smazat kontakt s názvem '%s'?", "name"));
          
        return $grid;
    }
}
