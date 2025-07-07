<?php
namespace App\LekyModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Ublaboo\DataGrid\DataGrid;
use App\LekyModule\Model\LekyZjednoduseny;
use Ublaboo\DataGrid\AkesoGrid;
use App\LekyModule\Grids\CustomDataGrid;

class ZjednodusenePresenter extends \App\Presenters\SecurePresenter {

    /** @var \App\LekyModule\Grids\ZjednoduseneGridFactory @inject */
    public $GridFactory;
    
    /** @var \App\LekyModule\Forms\ZjednoduseneFormFactory @inject */
    public $FormFactory;
    
    /** @var \App\LekyModule\Model\LekyZjednoduseny @inject */
    public $BaseModel;
    
    /** @var \App\LogyModule\Model\Logy @inject */
    public $LogyModel;

    const ORGANIZACE = ["NH" => "Hořovice", "RNB" => "Beroun", "MUS" => "Pardubice", "DCNH" => "Diagnostické centrum nemocnice Hořovice"],
          POJISTOVNY = array('111' => '111', '201' => '201', '205' => '205', '207' => '207', '209' => '209', '211' => '211', '213' => '213'),
          TRUEORFALSE = [ "" => "Ne", "0" => "Ne", "1" => "Ano"],
          STAV = array(
              '' => '-- Vyberte stav --',
              'Nasmlouváno' => 'Ano/Nasmlouváno',
              'Čeká se' => 'Ne/Čeká se',
              'Nezadáno' => 'Ne/Nezadáno',
              'Zamítnuto' => 'Ne/Zamítnuto'
          ),
          ORGANIZACE_VISIBLE = ["NH", "RNB", "MUS", "DCNH"];

    public function startup() {
        parent::startup();
        $this->template->nadpis = 'zjednodušený přehled léků';
    }

    protected function createComponentZjednoduseneDataGrid(string $name) {
    $grid = new AkesoGrid($this, $name);
    
    if ($this->user->getIdentity()->preferovana_organizace !== null && $grid->getSessionData('ORGANIZACE') === null) {
        $defaultHodnoty = array_intersect(explode(', ', $this->user->getIdentity()->preferovana_organizace), self::ORGANIZACE_VISIBLE);
    } else {
        $defaultHodnoty = $grid->getSessionData('ORGANIZACE');
    }

    $this->GridFactory->setZjednoduseneGrid($grid, $this->user->getIdentity()->prava, $this->user->getIdentity()->modul_poj, $defaultHodnoty, $this->BaseModel);

    $groupByName = $grid->getSessionData('group_by_name') ?? true;
    $fulltextSearch = $this->getParameter('fulltext');
    
    if ($fulltextSearch) {
        $grid->setDataSource($this->BaseModel->getDataSourceWithFulltextSearch(
            $fulltextSearch,
            $grid->getSessionData('lekarnaVyber') ?? null, 
            $grid->getSessionData('histori') ?? null
        ));
    } else {
        if ($groupByName) {
            $grid->setDataSource($this->BaseModel->getDataSourceGrouped(
                $grid->getSessionData('lekarnaVyber') ?? null, 
                $grid->getSessionData('histori') ?? null
            ));
        } else {
            $grid->setDataSource($this->BaseModel->getDataSourceZjednodusene(
                $grid->getSessionData('lekarnaVyber') ?? null, 
                $grid->getSessionData('histori') ?? null
            ));
        }
    }
    
    return $grid;
}

public function createComponentDGDataGrid(string $name): Multiplier{
    error_log("=== CREATING DG DATA GRID ===");
    
    return new Multiplier(function ($ID_LEKY) {
        error_log("=== MULTIPLIER CALLBACK FOR ID_LEKY: $ID_LEKY ===");

        $grid = new CustomDataGrid(null, $ID_LEKY);
        
        $this->GridFactory->setDGGrid($grid, $ID_LEKY, $this);
        $grid->setDataSource($this->BaseModel->getDataSource_DG($ID_LEKY));
        
        return $grid;
    });
}

public function processSignal(): void {
    $signal = $this->getSignal();
    
if ($signal && is_array($signal) && count($signal) >= 2 && 
    strpos($signal[0], 'dGDataGrid-') === 0 && 
    $signal[1] === 'submit' &&
    isset($_POST['inline_add'])) {
    
    $inlineData = $_POST['inline_add'];
    
    if ($inlineData && empty($inlineData['DG_NAZEV'])) {
        $this->flashMessage("DG Název je povinný", 'error');
        $this->redirect('this');
        return;
    }

    preg_match('/dGDataGrid-(.+)-filter/', $signal[0], $matches);
    $ID_LEKY = $matches[1] ?? null;
    
    if ($ID_LEKY) {
        try {
            // ✅ POUŽÍVÁ VYBRANOU POJIŠŤOVNU místo fixní 111
            $dgData = (object)[
                'ID_LEKY' => $ID_LEKY,
                'ORGANIZACE' => 'MUS',
                'POJISTOVNA' => $inlineData['POJISTOVNA'] ?? 111, // ✅ Z FORMULÁŘE
                'DG_NAZEV' => $inlineData['DG_NAZEV'],
                'VILP' => isset($inlineData['VILP']) && $inlineData['VILP'] === 'on' ? 1 : 0,
                'DG_PLATNOST_OD' => $inlineData['DG_PLATNOST_OD'] ?: null,
                'DG_PLATNOST_DO' => $inlineData['DG_PLATNOST_DO'] ?: null,
            ];
            
            $result = $this->BaseModel->insert_edit_pojistovny_dg($dgData);
            
            // ✅ PŘEJMENOVANÉ FIELDY (bez 111_)
            if ($inlineData['RL'] || $inlineData['POZNAMKA']) {
                $pojData = (object)[
                    'ID_LEKY' => $ID_LEKY,
                    'ORGANIZACE' => 'MUS',
                    'POJISTOVNA' => $inlineData['POJISTOVNA'] ?? 111, // ✅ Z FORMULÁŘE
                    'RL' => $inlineData['RL'] ?? '',           // ✅ PŘEJMENOVANÉ
                    'POZNAMKA' => $inlineData['POZNAMKA'] ?? '', // ✅ PŘEJMENOVANÉ
                    'STAV' => 'Nezadáno',
                    'SMLOUVA' => 0,
                    'NASMLOUVANO_OD' => null,
                ];
                
                try {
                    $this->BaseModel->insert_edit_pojistovny($pojData);
                } catch (\Exception $pojException) {
                }
            }
            
            $this->flashMessage("DG skupina byla úspěšně přidána/upravena", 'success');
            $this->redirect('this');
            return;
            
        } catch (\Exception $e) {
            $this->redirect('this');
            return;
        }
    } else {
        $this->flashMessage("Chybí ID léku pro přidání DG skupiny", 'error');
        $this->redirect('this');
        return;
    }
}
    
    if ($signal && is_array($signal) && count($signal) >= 2 && 
        strpos($signal[0], 'dGDataGrid-') === 0 && 
        $signal[1] === 'submit' &&
        isset($_POST['inline_edit'])) {
        
        $inlineData = $_POST['inline_edit'];
        
        $id = $inlineData['_id'] ?? null;
        if ($id) {
            preg_match('/dGDataGrid-(.+)-filter/', $signal[0], $matches);
            $ID_LEKY = $matches[1] ?? null;
            
            if ($ID_LEKY) {
                $originalRecords = $this->BaseModel->getDataSource_DG($ID_LEKY);
                $targetRow = null;
                foreach ($originalRecords as $row) {
                    if ($row->ID == $id) {
                        $targetRow = $row;
                        break;
                    }
                }
                
                if ($targetRow) {
                    // ✅ PŘEJMENOVANÉ FIELDY (bez 111_)
                    $editValues = [
                        'ID_LEKY' => $targetRow->ID_LEKY,
                        'ORGANIZACE' => $targetRow->ORGANIZACE,
                        'POJISTOVNA' => $targetRow->POJISTOVNA,
                        'ORIGINAL_DG_NAZEV' => $targetRow->DG_NAZEV,
                        'DG_NAZEV' => $inlineData['DG_NAZEV'] ?? $targetRow->DG_NAZEV,
                        'RL' => $inlineData['RL'] ?? '',           // ✅ PŘEJMENOVANÉ
                        'POZNAMKA' => $inlineData['POZNAMKA'] ?? '', // ✅ PŘEJMENOVANÉ
                        'VILP' => isset($inlineData['VILP']) && $inlineData['VILP'] === 'on' ? 1 : 0,
                        'DG_PLATNOST_OD' => $inlineData['DG_PLATNOST_OD'] ?? null,
                        'DG_PLATNOST_DO' => $inlineData['DG_PLATNOST_DO'] ?? null,
                    ];
                    
                    try {
                        $result = $this->BaseModel->set_pojistovny_dg_edit($editValues);
                        
                        if ($result) {
                            $this->flashMessage("Editace proběhla v pořádku", 'success');
                        } else {
                            $this->flashMessage("Žádné změny nebyly provedeny", 'warning');
                        }
                        
                        $this->redirect('this');
                        return;
                    } catch (\Exception $e) {
                        $this->redirect('this');
                        return;
                    }
                }
            }
        }
    }
    
    try {
        parent::processSignal();
    } catch (\Exception $e) {
        throw $e;
    }
}

public function handleInlineEdit($id) {
}

   protected function createComponentZjednoduseneForm(string $name) {
    $form = new \Nette\Application\UI\Form($this, $name);
    
    $id_leky = $this->getParameter('ID_LEKY');
        $lek = null;
    
    if ($id_leky) {
        $lek = $this->BaseModel->getLeky($id_leky);
        
        if ($lek && isset($lek->ORGANIZACE)) {
            $lek['ORGANIZACE'] = explode(', ', $lek->ORGANIZACE);
        } else {
            $lek['ORGANIZACE'] = ['MUS']; 
        }
        
        $lek['POJ'] = [];
        $values = '';

     $organizace_leku = is_array($lek['ORGANIZACE']) ? $lek['ORGANIZACE'] : ['MUS'];
        
        foreach ($organizace_leku as $org) {
            $lek[$org] = \Nette\Utils\ArrayHash::from($this->BaseModel->getPojistovny($id_leky, $org));
            if (isset($lek[$org])) {
                foreach ($lek[$org] as $value => $key) {
                if ($lek[$org][$value]['RL'] === '') {
                    $lek[$org][$value]['RL'] = '';
                } elseif ($lek[$org][$value]['RL'] == 1 || $lek[$org][$value]['RL'] == 0) {
                    $lek[$org][$value]['RL'] = (string)$lek[$org][$value]['RL'];
                    $lek[$org][$value]['Revizak'] = true;
                } else {
                    $lek[$org][$value]['Revizak'] = false;
                }
                $lek[$org][$value]['DG'] = $this->BaseModel->getPojistovny_DG($id_leky, $org, $value);
                if (!in_array($value, $lek['POJ'])) {
                    $values = $values . ", " . $value;
                }
            }
        }
    }
        if ($values) {
            $lek['POJ'] = explode(', ', trim($values, ", "));
        }
    } else {
        $lek = [];
    }

    $this->FormFactory->setZjednoduseneForm($form);
    $form->onError[] = function ($form) {
        \App\LekyModule\Presenters\LekyPresenter::processFormErrors($form, $this);
    };
    $form->setDefaults($lek);
    
    $form->addGroup();
    $form->addSubmit('send', 'Uložit')
         ->setHtmlAttribute('class', 'btn btn-success button btn-block');
    
    $form->onSuccess[] = [$this, "zjednoduseneFormSucceeded"];
    return $form;
}

    public function renderDefault() {
        $this->template->nadpis = 'zjednodušený přehled léků';
    }

 public function renderNew() {
    $savedata = $this->getComponent('zjednoduseneForm');
    
    if ($this->user->getIdentity()->preferovana_organizace !== null) {
        $defaultHodnoty = array_intersect(explode(', ', $this->user->getIdentity()->preferovana_organizace), self::ORGANIZACE_VISIBLE);
        $savedata->setDefaults(array('ORGANIZACE' => $defaultHodnoty));
    }
    
    $this->template->nadpis = 'přidání nového léku - zjednodušené';
    $this->setView('edit');
}

    public function renderEdit() {
        $this->template->nadpis = 'editace léku - zjednodušené';
    }

    public function actionWeb($ID_LEKY) {
        $this->redirectUrl('https://prehledy.sukl.cz/prehled_leciv.html#/leciva/' . $ID_LEKY);
    }

public function zjednoduseneFormSucceeded($form) {
    $values = $form->getValues();
    
    $editMode = $this->getAction() === 'edit';
    
    if (!$editMode && empty($values->ID_LEKY) && !empty($values->ATC)) {
        $values->ID_LEKY = $values->ATC;
    }
    
    $values->DOP = $values->DOP ?? '';
    $values->SILA = $values->SILA ?? '';
    $values->BALENI = $values->BALENI ?? '';
    $values->ATC3 = $values->ATC3 ?? '';
    $values->UHR1 = $values->UHR1 ?? null;
    $values->UHR2 = $values->UHR2 ?? null;
    $values->UHR3 = $values->UHR3 ?? null;
    $values->CENA_FAKTURACE = $values->CENA_FAKTURACE ?? null;
    $values->CENA_MAX = $values->CENA_MAX ?? null;
    $values->CENA_VYROBCE_BEZDPH = $values->CENA_VYROBCE_BEZDPH ?? null;
    $values->CENA_SENIMED_BEZDPH = $values->CENA_SENIMED_BEZDPH ?? null;
    $values->CENA_MUS_PHARMA = $values->CENA_MUS_PHARMA ?? null;
    $values->CENA_MUS_NC_BEZDPH = $values->CENA_MUS_NC_BEZDPH ?? null;
    $values->CENA_MUS_NC = $values->CENA_MUS_NC ?? null;
    $values->UHRADA = $values->UHRADA ?? null;
    $values->KOMPENZACE = $values->KOMPENZACE ?? null;
    $values->BONUS = $values->BONUS ?? null;
    
    $this->LogyModel->insertLog(\App\LekyModule\Model\Leky::AKESO_LEKY, $values, $this->user->getId());
    
    foreach ($values->ORGANIZACE as $org) {
        if (isset($values[$org])) {
            foreach (['111', '201', '205', '207', '209', '211', '213'] as $pojKey) {
                if (isset($values[$org][$pojKey]) && isset($values[$org][$pojKey]['STAV'])) {
                    $pojData = $values[$org][$pojKey];
                    
                    if (isset($pojData['Revizak']) && $pojData['Revizak']) {
                        $pojData['RL'] = $pojData['RL'] ?? '0';
                    } else {
                        $pojData['RL'] = '';
                    }
                    
                    if (isset($pojData['DG'])) {
                        foreach ($pojData['DG'] as $dg) {
                            if (!empty($dg->DG_NAZEV)) {
                                $dg->ID_LEKY = $values->ID_LEKY;
                                $dg->ORGANIZACE = $org;
                                $dg->POJISTOVNA = $pojKey;
                                $this->BaseModel->insert_edit_pojistovny_dg($dg);
                            }
                        }
                    }
                    
                    $pojData['ORGANIZACE'] = $org;
                    $pojData['ID_LEKY'] = $values->ID_LEKY;
                    $pojData['POJISTOVNA'] = $pojKey;
                    $pojData['SMLOUVA'] = 0;
                    $this->BaseModel->insert_edit_pojistovny($pojData);
                }
            }
        }
    }
    
    if (is_array($values->ORGANIZACE)) {
        foreach ($values->ORGANIZACE as $org) {
            $tempValues = clone $values;
            $tempValues->ORGANIZACE = $org;
            $this->BaseModel->insertLeky($tempValues);
        }
    } else {
        $this->BaseModel->insertLeky($values);
    }
    
    if ($editMode) {
        $this->flashMessage('Lék byl upraven.', "success");
    } else {
        $this->flashMessage('Lék byl přidán.', "success");
    }
    
    $this->redirect("default");
}

private function setSmlouva($poj, $smlouva = null) {
    if ($smlouva) {
        return $smlouva;
    } else {
        if ($poj == '111' || $poj == '205' || $poj == '207') {
            return 0;
        } elseif ($poj == '209' || $poj == '213') {
            return 1;
        } elseif ($poj == '201') {
            return 2;
        } elseif ($poj == '211') {
            return 3;
        }
    }
}

    public function handleDgskup($term) {
        $fristHalfItems = $this->BaseModel->getDg($term);
        $this->sendResponse(new \Nette\Application\Responses\JsonResponse($fristHalfItems));
    }

}