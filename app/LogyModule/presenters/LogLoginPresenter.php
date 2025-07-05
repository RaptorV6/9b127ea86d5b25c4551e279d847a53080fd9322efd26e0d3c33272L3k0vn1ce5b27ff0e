<?php

namespace App\LogyModule\Presenters;

use Grido\Grid;
use Grido\Components\Filters\Filter;

/**
 * Homepage presenter.
 */
class LogLoginPresenter extends \App\Presenters\SecurePresenter
{
 /** @var \App\LogyModule\Model\LogLoginModel @inject */
  public $model;
    
    public function createComponentLogLoginGrid($name) {
        $grid = new DataGrid($this,$name);

        $fluent = $this->model->find();
       
        $grid->setModel($fluent);
        $grid->setDefaultSort(array('datumcas' => 'DESC'));
                
        $grid->addColumnDate('datumcas', 'Datum a čas', \Grido\Components\Columns\Date::FORMAT_DATETIME)
                ->setSortable()
                ->setFilterDate();

        $grid->addColumnText('ip', 'IP')
                ->setSortable()
                ->setFilterText()
                ->setSuggestion();

        $grid->addColumnText('username', 'Uživatel')
                ->setSortable()
                ->setFilterText()
                ->setSuggestion();
        
        $grid->addColumnText('admin_relogin', 'Relogin admin')
                ->setSortable()
                ->setFilterText()
                ->setSuggestion();
        
        $grid->setFilterRenderType(Filter::RENDER_INNER);

    }
    
}