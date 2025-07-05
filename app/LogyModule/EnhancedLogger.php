<?php
namespace Tracy;

use App\Components\PHPMailerWrapper;
/**
 * Logování chyb a výjimek
 */
class EnhancedLogger extends Logger
{
    const LOG_TABLE = 'log.log_exceptions';
    
    const DEFAULT_LOGGER_ON = true; // jestli volat i původní logger
    
    const EXCEPTION_MESSAGE_SUBJECT = 'Výjimka v aplikaci %appName%';
    // HTML podoba e-mailu
    const EXCEPTION_MESSAGE_BODY = 'Výjimka v aplikaci %appName%<br><br>'
            . '<table>'
            . '<tr><td>Čas:</td><td>%time%</td></tr>'
            . '<tr><td>Priorita:</td><td>%priority%</td></tr>'
            . '<tr><td>Typ:</td><td>%type%</td></tr>'
            . '<tr><td>URL:</td><td>%url%</td></tr>'
            . '<tr><td valign="top">Request proměnné:</td><td>%req_variables%</td></tr>'
            . '<tr><td>Obsah zprávy výjimky:</td><td>%message%</td></tr>'
            . '<tr><td>Soubor:</td><td>%file%</td></tr>'
            . '<tr><td>Řádek v souboru:</td><td>%line%</td></tr>'
            . '<tr><td valign="top">Zdrojový kód:</td><td>%source_code%</td></tr>'
            . '<tr><td>ID přihlášeného uživatele:</td><td>%user_id%</td></tr>'
            . '</table>'
            . ''
            . 'Doplňující informace o výjimce: <div style="padding: 10px">%addit_info%</div>';
    // Textová podoba e-mailu
    const EXCEPTION_MESSAGE_ALTBODY = 'Výjimka v aplikaci %appName%:\r\n'
                                    . 'Čas: %time%\r\n'
                                    . 'Priorita: %priority%\r\n'
                                    . 'Typ: %type%\r\n'
                                    . 'URL: %url%\r\n'
                                    . 'Request proměnné: %req_variables%\r\n'
                                    . 'Obsah zprávy výjimky: %message%\r\n'
                                    . 'Soubor: %file%\r\n'
                                    . 'Řádek v souboru: %line%\r\n'
                                    . 'Zdrojový kód: %source_code%\r\n'
                                    . 'ID přihlášeného uživatele: %user_id%\r\n'
                                    . 'Soubor: %file%\r\n'
                                    . 'ID přihlášeného uživatele: %user_id%\r\n'
                                    . 'Doplňující informace o výjimce: %addit_info%';
    
    /** @var \Dibi\Connection */
    protected $db;
    
    /** @var \Nette\Security\User */
    protected $user;
    
    protected $appName;
    
    protected $mailerconfig;
    
    protected $exceptionMessageRecipients;
    
    public function __construct(\Nette\Security\User $user , \Dibi\Connection $db, $appName, $mailerconfig, $exceptionMessageRecipients)
    {
        $this->db = $db;
        $this->user = $user;
        $this->appName = $appName;
        
        $this->mailerconfig = $mailerconfig;
        $this->exceptionMessageRecipients = $exceptionMessageRecipients;
        
        // inicializace základního loggeru
        if(self::DEFAULT_LOGGER_ON) {
            parent::__construct(\Tracy\Debugger::$logDirectory, \Tracy\Debugger::$email, \Tracy\Debugger::getBlueScreen());
        }
            
        // nastavení zachytávání pro fatální chyby
        array_push(\Tracy\Debugger::$onFatalError, array($this, 'logError'));
    }
    
    /**
     * Logování fatálních chyb
     * @param Exception|Error|TypeError|Nette\MemberAccessException $error fatální chyba
     */
    public function logError($error) { //\Exception

        $message = $error->getMessage();
        $file = $error->getFile();
        $line = $error->getLine();
                
        $additInfo = 'Caused by: ' . Helpers::getClass($error);
        $additInfo .= '<br>Code: ' . $error->getCode();
        $additInfo .= '<br>Trace: ' . $error->getTraceAsString();
        $sourceCode = null;
        if($file && $line) {
            $sourceCode = BlueScreen::highlightFile($file, $line);
        }
        
        $severity = 'fatal_error';
        if($error instanceof \ErrorException) {
            $severity .= ' (' . $error->getSeverity() . ')';
        }
        
        $this->saveAndSend(self::CRITICAL, $severity, $message, $file, $line, $sourceCode, $additInfo);
    }
    
    /**
     * Logování zachytávaných výjimek
     * @param string $message zpráva výjimky
     * @param string $priority priorita výjimky
     * @return string logged error filename nebo null
     */
    public function log($message, $priority = self::INFO)
    {   
        $this->saveAndSend($priority, 'exception', $message, null, null, null, null);
        if(self::DEFAULT_LOGGER_ON) {
            // zpracování základním loggerem
            return parent::log($message, $priority);
        }
    }
    
    /**
     * Uloží informace do databáze a odešle na zadané e-mailové adresy
     * @param string $priority priorita výjimky
     * @param string $type typ výjimky
     * @param string $message zpráva výjimky
     * @param string $file soubor, kde výjimka vznikla
     * @param string $line řádek v souboru, kde výjimka vznikla
     * @param string $sourceCode část kódu, kde výjimka vznikla
     * @param string $additInfo doplňující informace o výjimce
     */
    private function saveAndSend($priority, $type, $message, $file, $line, $sourceCode, $additInfo) {     
        $time = date('Y-m-d H:i:s');
        $userId = $this->getLoggedUserUserId();
        if(isset($_SERVER['HTTP_HOST'])) {
            $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        } else {
            $url = "Spuštěno přes CLI";
        }
        $reqVariables = 'GET: ' . json_encode($_GET) . ';<br>'
                      . 'POST: ' . json_encode($_POST) . ';<br>'
                      . 'COOKIE: ' . json_encode($_COOKIE);
        
        $dbData = array('time' => $time,
                        'priority' => $priority,
                        'type' => $type,
                        'message' => ''.$message, // připojení prázdného stringu nějak zázračně opraví ukládání dat do databáze, které jinak neprojde - patrně nějaký problém s konverzí, ale je to prasárna
                        'file' => substr($file, 0, 100),
                        'addit_info' => $additInfo,
                        'user' => $userId,
                        'uri' => $url,
                        'req_variables' => $reqVariables,
                        'line' => $line,
                        'source_code' => $sourceCode);
        
        $this->db->insert(self::LOG_TABLE, $dbData)->execute(); 

        if(!empty($this->exceptionMessageRecipients)) {
            $mailerWrapper = new \App\Components\PHPMailerWrapper($this->mailerconfig, $this->db);
            $mailerWrapper->setBasicMailerConfig();
            $mailerWrapper->setFromMailerConfig();
            $mailerWrapper->setRecipients($this->exceptionMessageRecipients);
            
            $emailData = array( '%appName%' => $this->appName,
                                '%time%' => $time,
                                '%priority%' => $priority,
                                '%type%' => $type,
                                '%url%' => $url,
                                '%req_variables%' => $reqVariables,
                                '%message%' => $message,
                                '%file%' => $file,
                                '%line%' => $line,
                                '%source_code%' => $sourceCode,
                                '%user_id%' => $userId,
                                '%addit_info%' => $additInfo);

            $subject = $this->fillEMailData(self::EXCEPTION_MESSAGE_SUBJECT, $emailData);
            $body = $this->fillEMailData(self::EXCEPTION_MESSAGE_BODY, $emailData);
            $altBody = $this->fillEMailData(self::EXCEPTION_MESSAGE_ALTBODY, $emailData);
            $mailerWrapper->setDruh(99);  
            $mailerWrapper->setContent($subject, $body, $altBody);
			$mailerWrapper->send(); 
        }    
    }
    
    /**
     * Vyplní e-mailová data
     * @param string $message řetězec pro vyplnění
     * @param array $data pole s daty
     * @return string vyplněný řetězec
     */
    private function fillEMailData($message, $data) {
        foreach($data as $name=>$value) {
            $message = str_replace($name, $value, $message);
        }
        
        return $message;
    }

    /**
     * Vrátí ID aktuálního uživatele nebo -1
     * @return int
     */
    public function getLoggedUserUserId() {
        $userId = $this->user->getId();
        return is_null($userId) ? -1 : $userId;
    }
    
}
