<?php
namespace App\Logy\Services;

/**
 * Logování odeslaných zpráv
 */
class SendingsLogger
{
    const LOG_TABLE = "log.log_sendings";
    
    const RECIPIENT_MAX_LENGTH = 255; // dle velikosti sloupce v DB
    const SUBJECT_MAX_LENGTH = 200; // dle velikosti sloupce v DB
    
    /**
     * Konstruktor logování odeslaných zpráv
     * @param \Dibi\Connection $db databázový přístup
     */
    public function __construct(\Dibi\Connection $db)
    {
        $this->db = $db;
    }
    
    /**
     * Logování e-mailů
     * @param array $recipientsArray pole s e-mailovými adresy příjemců
     * @param string $subject předmět zprávy k zalogování
     * @param string $content obsah k zalogování
     */
    public function logEmail($recipientsArray, $subject, $content, $druh = NULL) {
        $recipients = '';
        foreach($recipientsArray as $recipient) {
            $recipients .= $recipient[0] . ';';
        }
        $recipients = substr($recipients, 0, -1);
        $this->logSending("email", $recipients, $subject, $content, $druh);
    }
    
    /**
     * Logování SMS
     * @param string $recipient telefonní číslo příjemce
     * @param string $content obsah k zalogování
     */
    public function logSMS($recipient, $content, $druh = NULL) {
        $this->logSending("sms", $recipient, '', $content, $druh);
    }
    
    /**
     * Logování zpráv
     * @param string $type typ zprávy (email nebo sms)
     * @param string $recipient příjemce
     * @param string $subject předmět zprávy k zalogování
     * @param string $content obsah k zalogování
     */
    private function logSending($type, $recipient, $subject, $content, $druh = NULL) {
        $recipient = substr($recipient, 0, self::RECIPIENT_MAX_LENGTH);
        $subject = substr($subject, 0, self::SUBJECT_MAX_LENGTH);
        $data  = array("time" => date('Y-m-d H:i:s'), "type" => $type, "recipient" => "$recipient", "subject" => "$subject", "content" => "$content");
        $this->db->insert(self::LOG_TABLE, $data)->execute();
    }
}