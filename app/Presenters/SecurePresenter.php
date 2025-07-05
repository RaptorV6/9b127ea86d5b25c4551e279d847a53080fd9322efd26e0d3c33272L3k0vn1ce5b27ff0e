<?php
namespace App\Presenters;

abstract class SecurePresenter extends BasePresenter {

    /** @var \App\Components\Model\Datagridmodul @inject*/
    public $Ciselnik;

    public function startup() {
        parent::startup();
        
        if($this->user->isLoggedIn()) {
            $this->Auth->insertAcessLog($this->user->getId());
            $this->template->logged = $this->user->isLoggedIn();
            $this->template->prava = $this->user->getIdentity()->prava;
        } else {
            $this->rememberCurrentModule();
            
            $this->template->logged = false;
            $this->template->prava = false;
            $this->redirect(':Homepage:default');
        }
    }
    
    /**
     * Zapamatuje si aktuální modul/presenter pro pozdější přesměrování
     */
    private function rememberCurrentModule() {
        $presenterName = $this->getName();
        $authSession = $this->getSession('auth');
        
        // Zjisti z názvu presenteru, kam se má vrátit
        if (strpos($presenterName, 'Zjednodusene') !== false) {
            $authSession->returnModule = 'zjednodusene';
        } else {
            $authSession->returnModule = 'leky';
        }
    }
}