<?php
namespace App\Factory;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DataGridFactory
 *
 * @author Ing. Vítek Šmíd
 */
class BaseDataGridFactory
{
    /**
     * Čeština
     * @return \Ublaboo\DataGrid\Localization\SimpleTranslator
     */
    protected function getCsTranslator(){
        return new \Ublaboo\DataGrid\Localization\SimpleTranslator([
            'ublaboo_datagrid.no_item_found_reset' => 'Žádné položky nenalezeny. Filtr můžete vynulovat',
            'ublaboo_datagrid.no_item_found' => 'Žádné položky nenalezeny.',
            'ublaboo_datagrid.here' => 'zde',
            'ublaboo_datagrid.items' => 'Položky',
            'ublaboo_datagrid.all' => 'všechny',
            'ublaboo_datagrid.from' => 'z',
            'ublaboo_datagrid.reset_filter' => 'Resetovat filtr',
            'ublaboo_datagrid.group_actions' => 'Hromadné akce',
            'ublaboo_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
            'ublaboo_datagrid.show_default_columns' => 'Výchozí nastavení sloupců',
            'ublaboo_datagrid.hide_column' => 'Skrýt sloupec',
            'ublaboo_datagrid.action' => 'Akce',
            'ublaboo_datagrid.previous' => 'Předchozí',
            'ublaboo_datagrid.next' => 'Další',
            'ublaboo_datagrid.choose' => 'Vyberte',
            'ublaboo_datagrid.execute' => 'Provést',
            'ublaboo_datagrid.filter_submit_button' => 'Filtrovat',
            'ublaboo_datagrid.perPage_submit' => 'Zobrazit',
            'ublaboo_datagrid.save' => 'Uložit',
            'ublaboo_datagrid.cancel' => 'Zrušit',
        ]); 
    }
}
