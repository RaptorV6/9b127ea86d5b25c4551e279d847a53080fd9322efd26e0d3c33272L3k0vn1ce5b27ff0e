<?php

namespace App\Presenters;

use Nette;

use Nette\Application\UI\Form;
use App\Model\CommonFunc;
use App\Model\Errors;
use App\Model\Auth;
use App\Model\Mail;
use App\Model\EZD;
use Nette\Application\ForbiddenRequestException;
/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var \App\Model\Auth @inject */
    public $Auth;
    
    protected $parametrs;
    
    /** @var \App\Factory\DataGridFactory @inject */
    public $GridFactory;
    

    Const JS_EUDATE_FORMAT = 'dd.mm.yyyy',
            PHP_EUDATE_FORMAT = 'd.m.Y',
            PHP_DBDATE_FORMAT = 'Y-m-d',
            FORM_M_MAX_LENGTH = "Maximální délka pole \'%label\' je %d znaků.",
            FORM_M_FILLED = 'Pole \'%label\' je povinné.';
    
        public function __construct(Array $parametrs = []) {
            parent::__construct();
            $this->parametrs = \Nette\Utils\ArrayHash::from($parametrs);
        }
        
        public function startup(){
            parent::startup();
            $this->template->title = 'S-Leky ';
            if($this->user->getId()) {
                $this->template->logged = $this->user->isLoggedIn();
                $this->template->prava = $this->user->getIdentity()->prava;
				$this->template->modul_poj = $this->user->getIdentity()->modul_poj;
				$this->template->modul_fin = $this->user->getIdentity()->modul_fin;
				$this->template->modul_lek = $this->user->getIdentity()->modul_lek;
                $this->template->preferovana_organizace = $this->user->getIdentity()->preferovana_organizace;
            } else {
                $this->template->logged = false;
                $this->template->prava = false;
            }
            if($this->Auth && $this->user->isLoggedIn()) {
                $this->template->user = $this->user->getIdentity()->jmeno . ' | ' . $this->user->getIdentity()->prijmeni . ' (' . $this->user->getId() . ') ' ;
            }
 
           // $this->template->appAdminEmail = 'smidv@nember.cz';//Config::APP_ADMIN_EMAIL;

        }
}
