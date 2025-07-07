<?php
namespace App\LekyModule\Grids;

use Ublaboo\DataGrid\AkesoGrid;
use \Nette\Utils\Html;
use \Nette\Forms\Container;
use Ublaboo\DataGrid\DataGrid;

class ZjednoduseneGridFactory extends \App\Factory\BaseDataGridFactory {

    public function __construct(\Nette\Security\User $user, \Dibi\Connection $db, array $parameters) {
        //parent::__construct($user, $db, $parameters);
    }

   // ✅ PŘIDEJ PARAMETR $baseModel
public function setZjednoduseneGrid(AkesoGrid $grid, $prava, $poj, $defaultHodnoty, $baseModel = null): AkesoGrid {
    $grid->setPrimaryKey("ID_LEKY");
    $grid->setStrictSessionFilterValues(false);
    $grid->setColumnsHideable();
    
    //$grid->setDefaultSort(array('ID_LEKY' => 'DESC'));

    $grid->addColumnText('ORGANIZACE', 'Organizace')
         ->setSortable()
         ->setDefaultHide()  
         ->setFilterMultiSelect(\App\LekyModule\Presenters\ZjednodusenePresenter::ORGANIZACE)
         ->addAttribute('class', 'multiselect');

    $grid->addColumnText('NAZ', 'Název')
         ->setSortable()
         ->setRenderer(function ($item) {
             return $item->NAZ;
         })
         ->setFilterText()
         ->setSplitWordsSearch(true);

    $grid->addColumnText('BIOSIMOLAR', 'Biosimilar')
         ->setSortable()
         ->setFilterText();

    $grid->addColumnText('ATC', 'ATC')
         ->setSortable()
         ->setReplacement(["" => "-"])
         ->setFilterText()
         ->setSplitWordsSearch(true);

    $grid->addColumnText('UCINNA_LATKA', 'Učinná látka')
         ->setSortable()
         ->setFilterText();

    $grid->addColumnText('POZNAMKA', 'Poznámka pro všechny ZP')
         ->setSortable()
         ->setFilterText();

    $pojistovnyNazvy = [
        '111' => '111 - VZP',
        '201' => '201 - VoZP', 
        '205' => '205 - ČPZP',
        '207' => '207 - OZP',
        '209' => '209 - ZPŠ',
        '211' => '211 - ZPMV',
        '213' => '213 - RBP'
    ];
   
    $pojistovny = ['111', '201', '205', '207', '209', '211', '213'];

    foreach ($pojistovny as $value) {
        $nazev = $pojistovnyNazvy[$value] ?? $value; 
        
        $grid->addColumnText($value . '_STAV', $nazev . ' ')  
             ->setSortable()
             // ✅ UPRAVENÝ RENDERER S DG SKUPINAMI
             ->setRenderer(function ($item) use ($value, $baseModel) {
                 $el = Html::el('div');
                 $el->class($item["poj" . $value . "_BARVA"]);
                 
                 // Základní stav a datum
                 if ($item[$value . "_STAV"] == 'Nasmlouváno') {
                     $item[$value . "_NASMLOUVANO_OD"] = $item[$value . "_NASMLOUVANO_OD"] ?? "Nezadáno";
                     $stavText = $item[$value . "_STAV"] . ' ' . $item[$value . "_NASMLOUVANO_OD"];
                 } else {
                     $stavText = $item[$value . "_STAV"] ?? "Nezadáno";
                 }
                 
                 // ✅ NAČTI DG SKUPINY PRO TENTO LÉK A POJIŠŤOVNU
                 if ($baseModel && !empty($item->ID_LEKY)) {
                     try {
                         $dgSkupiny = $baseModel->getDgSkupinyProLekAPojistovnu($item->ID_LEKY, $value);
                         if (!empty($dgSkupiny)) {
                             $dgList = implode(', ', $dgSkupiny);
                            $stavText .= '<br><hr style="margin: 2px 0; border: none; border-top: 1px solid #ddd;"><small">' . $dgList . '</small>';
                         }
                     } catch (\Exception $e) {
                         // Pokud selže načítání DG, pokračuj bez nich
                     }
                 }
                 
                 // Poznámka jako tooltip
                 if (!empty($item[$value . '_POZNAMKA'])) {
                     $el->title($item[$value . '_POZNAMKA']);
                     echo $el->addHtml(Html::el('img')->width('12%')->src('/Lekovnice/www/img/mark.png')->style('float:left;'));
                 }
                 
                 $el->setHtml($stavText); // ✅ ZMĚNA z setText na setHtml kvůli <br>
                 return $el;
             })
             ->setFilterText();
    }

    // Zbytek metody zůstává stejný...
    $grid->setItemsDetail(true, "ID_LEKY")
         ->setClass("btn btn-primary btn-sm ajax")
         ->setTitle("Detail")
         ->setText("Detail (Info)")
         ->setIcon("arrow-down")
         ->setTemplateParameters(["ID_LEKY"=>"ID_LEKY"])
         ->setType("template")
         ->setTemplate(__DIR__."/../templates/Zjednodusene/itemDetail.latte");

    if ($prava == '9' || $prava == '2') {
        $grid->addToolbarButton("new")
             ->setText("Nový lék")
             ->setClass("btn btn-success")
             ->setIcon("plus")
             ->setTitle("Přidá nový lék");
    }

    $grid->addAction('sukl', 'Sukl', 'web')
         ->addAttributes(["target" => "_blank"])
         ->setClass("btn btn-info")
         ->setIcon("info");

    if ($prava == '9' || $prava == '2') {
        $grid->addAction('edit', 'Editace', 'edit')
             ->setClass("btn btn-warning")
             ->setIcon("pencil");
    }

    return $grid;
}

public function setDGGrid(\Ublaboo\DataGrid\DataGrid $grid, $ID_LEKY, $presenter = null) {
    error_log("=== SET DG GRID START ===");
    
    $grid->setPrimaryKey("ID");
    $grid->setTranslator($this->getCsTranslator());
    $grid->setColumnsHideable();
    $grid->setStrictSessionFilterValues(false);

    // ✅ JEDNODUCHÝ onRender bez hackování
    $grid->onRender[] = function() {
        error_log("=== DG GRID ON RENDER ===");
    };

    $grid->addColumnText('ID', '#')
         ->setSortable()
         ->setDefaultHide()
         ->setFilterText();

             $grid->addColumnText('POJISTOVNA', 'Pojišťovna')
         ->setSortable()
         ->setReplacement([
             '111' => '111 - VZP',
             '201' => '201 - VoZP', 
             '205' => '205 - ČPZP',
             '207' => '207 - OZP',
             '209' => '209 - ZPŠ',
             '211' => '211 - ZPMV',
             '213' => '213 - RBP'
         ])
         ->setFilterText();

    $grid->addColumnText('ID_LEKY', 'ATC')
         ->setSortable()
         ->setDefaultHide()  
         ->setFilterText();

    $grid->addColumnText('ORGANIZACE', 'Organizace')
         ->setSortable()
         ->setDefaultHide()  
         ->setFilterText();

    $grid->addColumnText('LEK_NAZEV', 'Lék')
         ->setSortable()
         ->setFilterText();

    $grid->addColumnText('DG_NAZEV', 'Název DG')
         ->setSortable()
         ->setFilterText();

    // ✅ PŘEJMENOVANÉ SLOUPCE (bez 111_)
    $grid->addColumnText('RL', 'Revizní lékař')
         ->setSortable()
         ->setReplacement([
             '' => 'Ne',
             '0' => 'povolení RL- žádanka §16', 
             '1' => 'epikríza/info pro RL'
         ])
         ->setFilterText();

    $grid->addColumnText('POZNAMKA', 'Poznámka')
         ->setSortable()
         ->setReplacement(["" => "-"])
         ->setFilterText();

    $grid->addColumnText('VILP', 'VILP')
         ->setSortable()
         ->setReplacement(\App\LekyModule\Presenters\ZjednodusenePresenter::TRUEORFALSE)
         ->setFilterText();

    $grid->addColumnDateTime('DG_PLATNOST_OD', 'Platnost od')
         ->setFitContent()
         ->setFormat("d.m.Y")
         ->setSortable()
         ->setAlign('left')
         ->setFilterDate()
         ->setAttribute("data-date-language", "cs");

    $grid->addColumnDateTime('DG_PLATNOST_DO', 'Platnost do')
         ->setFitContent()
         ->setFormat("d.m.Y")
         ->setSortable()
         ->setAlign('left')
         ->setFilterDate()
         ->setAttribute("data-date-language", "cs");

    // ✅ INLINE ADD s výběrem pojišťovny
    $grid->addInlineAdd()
        ->setPositionTop()
        ->onControlAdd[] = function (Container $container) use($ID_LEKY){
            error_log("=== INLINE ADD CONTROL ADD ===");
            
            // ✅ SELECT PRO POJIŠŤOVNY
            $container->addSelect('POJISTOVNA', 'Pojišťovna')
                      ->setItems([
                          '111' => '111 - VZP',
                          '201' => '201 - VoZP', 
                          '205' => '205 - ČPZP',
                          '207' => '207 - OZP',
                          '209' => '209 - ZPŠ',
                          '211' => '211 - ZPMV',
                          '213' => '213 - RBP'
                      ])
                      ->setDefaultValue('111')
                      ->setRequired('Vyberte pojišťovnu');
            
            $container->addText("DG_NAZEV", "Název DG")
                      ->setHtmlAttribute('data-autocomplete-dg');

            // ✅ PŘEJMENOVANÉ FIELDY (bez 111_)
            $container->addSelect('RL', 'Revizní lékař')
                      ->setItems([
                          '' => 'Ne',
                          '0' => 'povolení RL- žádanka §16', 
                          '1' => 'epikríza/info pro RL'
                      ])
                      ->setDefaultValue('');

            $container->addText('POZNAMKA', 'Poznámka');

            $container->addCheckbox('VILP', 'VILP')
                      ->setHtmlAttribute('class', 'checkbox_style')
                      ->setDefaultValue(false);

            $container->addText('DG_PLATNOST_OD','Platnost od')
                      ->setHtmlType('date')
                      ->setDefaultValue(date("Y-m-d"));

            $container->addText('DG_PLATNOST_DO','Platnost do')
                      ->setHtmlType('date')
                      ->setDefaultValue(date("Y-m-d", strtotime('+1 year')));
                      
            error_log("=== INLINE ADD CONTROLS CREATED ===");
        };
    
    // ✅ INLINE EDIT s výběrem pojišťovny
    $grid->addInlineEdit()
        ->onControlAdd[] = function(Container $container): void {
            error_log("=== INLINE EDIT CONTROL ADD ===");
            
            $container->addText("LEK_NAZEV", "Lék")
                      ->setHtmlAttribute('readonly');
            
            // ✅ SELECT PRO POJIŠŤOVNY (readonly v edit módu)
            $container->addSelect('POJISTOVNA', 'Pojišťovna')
                      ->setItems([
                          '111' => '111 - VZP',
                          '201' => '201 - VoZP', 
                          '205' => '205 - ČPZP',
                          '207' => '207 - OZP',
                          '209' => '209 - ZPŠ',
                          '211' => '211 - ZPMV',
                          '213' => '213 - RBP'
                      ])
                      ->setHtmlAttribute('readonly');
                
            $container->addText("DG_NAZEV", "DG Název");

            // ✅ PŘEJMENOVANÉ FIELDY (bez 111_)
            $container->addSelect('RL', 'Revizní lékař')
                      ->setItems([
                          '' => 'Ne',
                          '0' => 'povolení RL- žádanka §16', 
                          '1' => 'epikríza/info pro RL'
                      ]);

            $container->addText('POZNAMKA', 'Poznámka');

            $container->addCheckbox('VILP', 'VILP')
                      ->setHtmlAttribute('class', 'checkbox_style');

            $container->addText('DG_PLATNOST_OD','Platnost od')
                      ->setHtmlType('date');

            $container->addText('DG_PLATNOST_DO','Platnost do')
                      ->setHtmlType('date');
                      
            $container->addHidden('ID_LEKY');
            $container->addHidden('ORGANIZACE');
            
            error_log("=== INLINE EDIT CONTROLS CREATED ===");
        };

    // ✅ DEFAULTS PRO EDIT s novými field názvy
    $grid->getInlineEdit()->onSetDefaults[] = function (Container $container, $item): void {
        $PLATNOST_OD = $item['DG_PLATNOST_OD'] !== null ? \DateTime::createFromFormat('d.m.Y', $item['DG_PLATNOST_OD'])->format('Y-m-d') : NULL;
        $PLATNOST_DO = $item['DG_PLATNOST_DO'] !== null ? \DateTime::createFromFormat('d.m.Y', $item['DG_PLATNOST_DO'])->format('Y-m-d') : NULL;
        
        $container->setDefaults([
            'LEK_NAZEV' => $item['LEK_NAZEV'],
            'POJISTOVNA' => $item['POJISTOVNA'],
            'DG_NAZEV' => $item['DG_NAZEV'],
            'RL' => $item['RL'],           // ✅ PŘEJMENOVANÉ
            'POZNAMKA' => $item['POZNAMKA'], // ✅ PŘEJMENOVANÉ
            'VILP' => $item['VILP'],
            'DG_PLATNOST_OD' => $PLATNOST_OD,
            'DG_PLATNOST_DO' => $PLATNOST_DO,
            'ID_LEKY' => $item['ID_LEKY'],
            'ORGANIZACE' => $item['ORGANIZACE'],
        ]);
    };

    error_log("=== SET DG GRID END ===");
    return $grid;
}



}
