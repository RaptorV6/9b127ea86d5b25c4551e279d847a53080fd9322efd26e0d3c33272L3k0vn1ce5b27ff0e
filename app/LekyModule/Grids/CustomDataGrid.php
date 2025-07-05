<?php

namespace App\LekyModule\Grids;

use Ublaboo\DataGrid\DataGrid;
use Nette\Forms\Form;

class CustomDataGrid extends DataGrid {
    
    public function filterSucceeded(Form $form): void {
        error_log("=== CUSTOM FILTER SUCCEEDED START ===");
        error_log("Form valid: " . ($form->isValid() ? 'YES' : 'NO'));
        
        if (!$form->isValid()) {
            error_log("=== FORM NOT VALID - FIXING ===");
            
            // Force clear all errors
            $form->cleanErrors();
            foreach ($form->getComponents(true) as $component) {
                if ($component instanceof \Nette\Forms\Controls\BaseControl) {
                    $component->cleanErrors();
                }
            }
            
            error_log("=== ERRORS CLEARED ===");
        }
        
        try {
            parent::filterSucceeded($form);
            error_log("=== PARENT FILTER SUCCEEDED OK ===");
        } catch (\Exception $e) {
            error_log("=== PARENT FILTER ERROR: " . $e->getMessage() . " ===");
            // Pokračuj i při chybě
        }
    }
}
