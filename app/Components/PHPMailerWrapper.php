<?php
namespace App\Components;

use Nette\SmartObject;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Třída pro základní konfiguraci PHPMaileru
 */
class PHPMailerWrapper {
    use SmartObject;
    
    protected $config;
    
    protected $db;

    /** @var \PHPMailer */
    protected $phpMailer;
    
    protected $druh_id = NULL;

    /**
     * Vytvoří wrapper PHPMailera
     * Pořadí volání metod - setBasicMailerConfig() -> setFromMailerConfig() -> addRecipients($recipients) -> setContent($subject, $body, $altBody) -> send()
     * @param array $config nastavení PHPMailera
     * @param DibiConnection $db připojení k databázi
     */
    public function __construct($config, $db) {
        $this->config = $config;
        
        $this->db = $db;
        
        $this->phpMailer = new PHPMailer();
        
        $this->checkConfig();
    }
    
    /**
     * Otestuje úplnost výchozího nastavení PHPMailera
     */
    protected function checkConfig() {
        $this->configGetHost();
        if($this->configIsSMTPAuth()) {
            $this->configGetUsername();
            $this->configGetPassword();   
        }
        $this->configIsSMTPSecure();
        $this->configGetPort();
        $this->configGetSMTPOptions();
        $this->configGetFromEmail();
        $this->configGetFromName();
    }
    
    /**
     * Vrátí PHPMailer
     * @return PHPMailer
     */
    public function getPHPMailer() {
        return $this->phpMailer;
    }
    
    /**
     * Odešle a zaloguje zprávu
     */
    public function send() {
        $sendingsLog = new \App\Logy\Services\SendingsLogger($this->db);
        $sendingsLog->logEmail($this->phpMailer->getToAddresses(), $this->phpMailer->Subject, $this->phpMailer->Body, $this->druh_id);
        return $this->phpMailer->send();
    }
    
    /**
     * Set Host, SMTPAuth, Username, Password, SMTPSecure, Port, SMTPOptions, CharSet to PHPMailer object  
     */
    public function setBasicMailerConfig() {
        $mailer = $this->phpMailer;
        
        $mailer->isSMTP();                                            // Set mailer to use SMTP
        $mailer->Host = $this->configGetHost();                       // Specify main and backup SMTP servers
        $mailer->SMTPAuth = $this->configIsSMTPAuth();                // Enable SMTP authentication
        if($mailer->SMTPAuth) {
            $mailer->Username = $this->configGetUsername();           // SMTP username
            $mailer->Password = $this->configGetPassword();           // SMTP password
        }
        $mailer->SMTPSecure = $this->configIsSMTPSecure();            // Enable TLS encryption, `ssl` also accepted
        $mailer->Port = $this->configGetPort();                       // TCP port to connect to
        $mailer->SMTPOptions = $this->configGetSMTPOptions();         // SMTP options
        
        $mailer->CharSet = 'UTF-8';
    }
    
    /**
     * Set From e-mail, From name to PHPMailer object
     */
    public function setFromMailerConfig() {
        $this->phpMailer->setFrom($this->configGetFromEmail(), $this->configGetFromName());
    }
    
    /**
     * Nastaví adresáty (pouze adresy, beze jmen)
     * @param array | string $recipients pole adresátů nebo adresát či adresáti oddělení čárkou
     */
    public function setRecipients($recipients) {
        if(!is_array($recipients)) {
            $recipients = explode(',', $recipients);
        }
        $this->phpMailer->clearAddresses(); // vymazání seznamu adresátů
        foreach($recipients as $recipient){
            $this->phpMailer->addAddress($recipient);   // Add a recipient
        }
    }
    
    /**
     * Nastaví druh odeslané zprávy (Vyjímka, Expirace, ...)
     * @param int $druh
     * @return void
     */
    public function setDruh(int $druh = NULL) :void{
        $this->druh_id = $druh;
    }
    
    /**
     * Nastaví obsah zprávy
     * @param string $subject The Subject of the message.
     * @param string $body An HTML or plain text message body. If HTML then call isHTML(true).
     * @param string $altBody The plain-text message body. This body can be read by mail clients that do not have HTML email capability such as mutt & Eudora. Clients that can read HTML will view the normal Body.
     */
    public function setContent($subject, $body, $altBody) {
        $this->phpMailer->Subject = $subject;
        $this->phpMailer->Body = $body;
        $this->phpMailer->AltBody = $altBody;
    }
    
    /**
     * Main and backup SMTP servers
     * @return type
     */
    protected function configGetHost() {
        if(!isset($this->config['host'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'host'");
        }
        return $this->config['host'];
    }

    /**
     * // SMTP authentication
     * @return type
     */
    protected function configIsSMTPAuth() {
        if(!isset($this->config['smtpauth'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'smtpauth'");
        }
        return $this->config['smtpauth'];
    }

    /**
     * SMTP username
     * @return type
     */
    protected function configGetUsername() {
        if(!isset($this->config['username'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'username'");
        }
        return $this->config['username'];
    }
	
    /**
     * SMTP password
     * @return type
     */
   protected function configGetPassword() {
        if(!isset($this->config['password'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'password'");
        }
        return $this->config['password'];
    }
	
    /**
     * TLS encryption, `ssl` also accepted
     * @return type
     */
    protected function configIsSMTPSecure() {
        if(!isset($this->config['smtpsecure'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'smtpsecure'");
        }
        return $this->config['smtpsecure'];
    }
	
    /**
     * TCP port to connect to
     * @return type
     */
    protected function configGetPort() {
        if(!isset($this->config['port'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'port'");
        }
        return $this->config['port'];
    }
    
    /**
     * SMTP options
     * @return type
     */
    protected function configGetSMTPOptions() {
        if(!isset($this->config['smtpoptions'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'smtpoptions'");
        }
        return $this->config['smtpoptions'];
    }
    
    /**
     * From e-mail
     * @return type
     */
    protected function configGetFromEmail() {
        if(!isset($this->config['from'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'from'");
        }
        if(!isset($this->config['from']['email'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'from->email'");
        }
        return $this->config['from']['email'];
    }
    
    /**
     * From name
     * @return type
     */
    protected function configGetFromName() {
        if(!isset($this->config['from'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'from'");
        }
        if(!isset($this->config['from']['name'])) {
            throw new \Exception("Missing mailerWrapper parameter - 'from->name'");
        }
        return $this->config['from']['name'];
    }
}
?>
