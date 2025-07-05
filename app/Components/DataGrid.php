<?php
namespace Ublaboo\DataGrid;

use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;

/**
 * Rozšíření Ublaboo DataGridu
 */
class AkesoGrid extends DataGrid {


    /** @var \App\Components\Model\Datagridmodul */
    private $Ciselnik = null;

    /**
     * Konstruktor rozšířeného Ublaboo DataGridu
     * @param mixed $parent
     * @param string $name
     */
    public function __construct($parent, string $name) {
        parent::__construct($parent, $name);
        $this->setTranslator($this->getCsTranslator());
        $this->template->originalTemplate = $this->getTemplateFile();
        $this->setTemplateFile(__DIR__."/templates/datagrid.latte");        
        $this->strictSessionFilterValues = false;
		$this->Ciselnik = $parent->getPresenter()->Ciselnik;
        $this->template->renderSaveSettBtn = true;        
        $this->loadDbSettings($parent);
    }


		/**
		 * Uloží nastavení gridu do databáze
		 * @return void
		 */
		public function handleSaveDbSettings() :void {
			if($this->template->renderSaveSettBtn) {
				$this->getColumns(); //init

				$data = ["per_page"=>$this->perPage, "sort"=>$this->sort, "columns"=>$this->columnsVisibility];
				$save = ["JSON"=>json_encode($data),"UZIVATEL_ID"=>$this->parent->user->getId(), "ID_U_ZALOZIL"=>$this->parent->user->getId(), "AKTIVNI"=>1, "DATUM"=>date("Y-m-d H:i:s")];
				$res = $this->Ciselnik->setDbGridUserSettings($this->parent->getName()."-".$this->getFullName(), $save);
				if($res) {
					$this->parent->flashMessage("Nastavení uloženo.", 'success');	
				} else {
					$this->parent->flashMessage("Nastavení se nepovedlo uložit.", 'warning');
				}

				$this->parent->redrawControl("flashes");	
			}
		}
		/**
		 * Načte uložené nastavení gridu z databáze
		 * @param mixed $presenter
		 * @return void
		 */
		private function loadDbSettings($presenter) : void {
			if (!$presenter->isAjax() && $this->getSessionData('_grid_hidden_columns') === null) { //první inicializace gridu / session
				$to_hide = [];

				$arr = $this->Ciselnik->getDbGridUserSettings($presenter->getName()."-".$this->getFullName());
				
				if($arr) {
					foreach($arr["columns"] as $key => $col) {
						if(!$col["visible"]) {
							$to_hide[] = $key;	
						}
					}
					if (count($to_hide) > 0) {
						$this->saveSessionData('_grid_hidden_columns', $to_hide);
						$this->saveSessionData('_grid_hidden_columns_manipulated', true);
					}
					
					if(is_array($arr["sort"])) {
						$this->saveSessionData('_grid_has_sorted', 1);
						$this->saveSessionData('_grid_sort', $this->sort = $arr["sort"]);
					}
					if($arr["per_page"]) {
						$this->saveSessionData('_grid_perPage', $arr['per_page']);
					}
				}
			}
		}

        /**
         * Summary of createComponentFilter
         * @return \Nette\Application\UI\Form
         */
		public function createComponentFilter() : Form  {
			$parent = parent::createComponentFilter();
			
			if(!$parent->isSubmitted()) {
				$this->getColumns();	
			}
			
			/** @var \Nette\Forms\Container $form*/
			$form = $parent->addContainer("visibility");
			
			$container = $form->addContainer("columns");
			
			foreach ($this->columns as $key => $value) {
				if($key) {
					$container->addCheckbox($key, ' - '.$value->getName())
							->setDefaultValue($this->columnsVisibility[$key]["visible"])
							->labelPrototype
								->onclick("event.stopPropagation()")
								->class("w-100 dropdown-item");	
				}
			}
        $form->addSubmit("submit", "Uložit") //<i class="fa fa-save"></i>
            ->setHtmlAttribute("class", 'btn btn-primary btn-sm dropdown-item')
            ->setValidationScope([]); //btn btn-sm btn-default 

			return $parent;
		}
        /**
         * Nastaví základní filtry + zabráníme přemazávání novými
         * @param array $defaultFilter
         * @param bool $useOnReset
         * @param bool $erase
         * @return \Ublaboo\DataGrid\DataGrid
         */
		public function setDefaultFilter(array $defaultFilter, bool $useOnReset = true, bool $erase = false): DataGrid { //musí vracet DataGrid, jinak chyba
			$old = $this->defaultFilter;
			
			parent::setDefaultFilter($defaultFilter, $useOnReset);
			
			$this->defaultFilter = $erase ? $defaultFilter : array_merge($old, $defaultFilter);

			return $this;
		}
        /**
         * Summary of filterSucceeded
         * @param \Nette\Forms\Form $form
         * @return void
         */
        public function filterSucceeded(\Nette\Forms\Form $form): void {
			//$this->cleanFormErrors($form);
			
			$container = $form->getComponent("visibility");
			
			$this->visibilitySucceeded($container);
			
			parent::filterSucceeded($form);
			
			$this->redrawControl("filterResetBtn");
		}

        /**
		 * Uložení formuláře visibilty
		 * @param \Nette\Forms\Container $form
		 * @return void
		 */
		private function visibilitySucceeded(\Nette\Forms\Container $form) : void{
			if (isset($form['submit']) && $form['submit']->isSubmittedBy()) {
				$sesData = $this->getSessionData('_grid_hidden_columns') ?? [];
				foreach ($form->getComponent("columns")->getComponents() as $key => $component) {
					if($component instanceof \Nette\Forms\Controls\Checkbox) {
						$in = array_search($key, $sesData);
						if($component->getValue()) {
							if($in !== false){
								unset($sesData[$in]);
							}
						} else {
							if($in === false){
								$sesData[] = $key;
							}
						}
					}
				}
				$this->saveSessionData('_grid_hidden_columns', $sesData);
				
				$this->redrawControl();

				$this->onRedraw();
			}
		}

     	/**
		 * Aby se upravená hlavička přenačítala správně
		 * @return void
		 */
		public function reloadTheWholeGrid(): void {
			$this->redrawControl("templateWrapper"); 
			$this->redrawControl("filterResetBtn");
			parent::reloadTheWholeGrid();
		}

		public function handlePage(int $page): void {
			$this->redrawControl("templateWrapper"); 
			parent::handlePage($page);
			$this->redrawControl("filterResetBtn");
		}
		

        /**
         * Český překlad
         * @return Localization\SimpleTranslator
         */
        private function getCsTranslator() { 
            return new Localization\SimpleTranslator([
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
