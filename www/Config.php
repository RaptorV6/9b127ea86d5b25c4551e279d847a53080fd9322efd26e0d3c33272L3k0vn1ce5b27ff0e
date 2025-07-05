<?php
if(!defined('IN_SCRIPT')) {
    Header('Location: index.php');
}

class Config {
    const APP_URL = 'https://mailer.nember.cz/'; // používá se v e-mailu
    const APP_ADMIN_EMAIL = 'voparil@nember.cz';
    
    const CODE_VALID_TIME = 60; // výchozí platnost kódu pro session s i bez přihlášení v minutách
    const CODE_PROLONG_TIME = 60; // doba prodloužení platnosti kódu pro session s i bez přihlášení v minutách
    
    const DATA_MESSAGE_ATTACHMENTS_MAX_FILE_SIZE = 14000; // KiB
    const DATA_MESSAGE_ATTACHMENTS_MAX_FILE_NAME_LENGTH = 99; //CHAR
    const DATA_MESSAGE_PASSWORD_SENDING_RETRY_LOCK_MINUTES = 2; // heslo k datové zprávě lze odeslat jednou za zadaný počet minut
    
    const DB_DSN_TYPE = 'mysql';
    const DB_HOST = 'localhost';
    const DB_NAME = 'webmailer';
    const DB_CHARSET = 'utf8';
    const DB_USERNAME = 'root';
    const DB_PASSWORD = '';

//   
    const EMAIL_ADDRESS_MAX_LENGTH = 60; // nesmí překračovat nastavení příslušných sloupců v databázi
    const EMAIL_ATTACHMENTS_MAX_FILE_SIZE = 2000; // KiB
    const EMAIL_ATTACHMENTS_UPLOAD_DIR = '/webmailer/uploaded/'; // '/var/uploaded/';
    
    const ENC_CERT_SUBJ_C = 'CZ';
    const ENC_CERT_SUBJ_ST = 'Prague';
    const ENC_CERT_SUBJ_L = 'Prague';
    const ENC_CERT_SUBJ_O = 'Jessenia';
    const ENC_CERT_SUBJ_OU = 'IT';
    const ENC_CERT_SUBJ_CN = 'Webmailer';
    const ENC_CERT_VALID_DAYS = 7300;
   // const ENC_CERT_FILES_REL_DIRECTORY = 'certificates/'; // relativní cesta k certifikátům
    //const ENC_KEY_FILES_ABS_DIRECTORY = '/var/private-keys/'; // absolutní cesta ke klíčům

    const MAILER_SMTP_DEBUG = 0;
    const MAILER_SMTP_HOST = '10.40.20.5';
    const MAILER_SMTP_AUTH = false;
    //const MAILER_SMTP_USERNAME = '';
    //const MAILER_SMTP_PASSWORD = '';
    const MAILER_SMTP_SECURE = 'tls';
    const MAILER_SMTP_PORT = 25;
    const MAILER_SMTP_OPTIONS = array("ssl" => array("verify_peer" => false, "verify_peer_name" => false));
    const MAILER_EMAIL_CHARSET = 'UTF-8';
    const MAILER_EMAIL_FROM_ADDRESS = 'robot@nember.cz';
    const MAILER_EMAIL_FROM_NAME = 'Mailer - Nemocnice Beroun a Nemocnice Hořovice';
    const MAILER_EMAIL_SUBJ_BEGIN = 'Mailer - ';
    
    const PAGE_TITLE = 'Mailer - Nemocnice Beroun a Nemocnice Hořovice';
    
    const SMS_GATE_APP_ID = 'webmailer_';
    const SMS_GATE_DIRECTORY = '/sms/outgoing/';
    
    // cleaner
    const KEEP_MESSAGES_DAYS  = 60;
}

?>