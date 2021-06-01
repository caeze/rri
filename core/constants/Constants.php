<?php
class Constants {
    // debug and development options
    const VERSION = '0.0.1';
    const ERROR_REPORTING_IN_WEBPAGE = true;
    const SHOW_PHP_INFO = false;
    const WRITE_MAILS_TO_DISK_INSTEAD_OF_SENDING = true;
    const MAIL_TO_DISK_PATH = 'debug_mail_content/mailContents.txt';

    // log levels
    const LOG_LEVELS = [0 => 'DEBUG', 1 => 'INFO', 2 => 'WARNING', 3 => 'ERROR', 4 => 'CRITICAL'];
    const LOG_TO_DATABASE_FROM_LEVEL = 0;
    const ALERT_ADMIN_FROM_LEVEL = 3;
    
    // SQL
    const POSTGRES_HOST = 'localhost';
    const POSTGRES_PORT = '5432';
    const POSTGRES_DB_NAME = 'rri';
    const POSTGRES_DB_NAME_UNIT_TESTS = 'rriunittests';
    const POSTGRES_USER = 'postgres';
    const POSTGRES_PASSWORD = Passwords::POSTGRES_PASSWORD;
    
    // registration
    const PASSWORD_COST = 10;
    
    // files
    const UPLOADED_IMAGES_DIRECTORY = 'uploaded_images';
    const ALLOWED_FILE_EXTENSION_UPLOAD = ['png', 'jpg', 'jpeg', 'gif'];
    
    // user settings
    const DEFAULT_LANGUAGE = 'de';
    
    // system
    const DEFAULT_TIMEZONE = 'Europe/Berlin';
    const DATE_FORMAT = 'Y-m-d H:i:s.v';
    const DATE_FORMAT_GERMAN = 'd.m.Y H:i';
    const DATE_FORMAT_ENGLISH = 'm-d-Y H:i';
    const MAX_UPLOAD_FILE_SIZE_BYTES = 4194304; // 4MB
    const SUCCESS_COLOR = '#66FF66';
    const FAILED_COLOR = '#AA0000';
    const CLEANUP_LOG_TO_NUMBER_OF_EVENTS = 10000;
    const LOCALE_ENGLISH = ['currencyIsoCode' => 'GBP', 'currencySymbol' => '£', 'currencyExchangeRateToEuro' => 1 / 1.16, 'decimalSymbol' => '.', 'printCurrencySymbolAfterAmount' => false];
    const LOCALE_GERMAN = ['currencyIsoCode' => 'EUR', 'currencySymbol' => '€', 'currencyExchangeRateToEuro' => 1, 'decimalSymbol' => ',', 'printCurrencySymbolAfterAmount' => true];
    
    // recurring tasks
    const RECURRING_TASKS = ['cleanupLogs' => 'CLEANUP_LOGS', 'removeToBeDeletedUsers' => 'REMOVE_TO_BE_DELETED_USERS'];
    const RECURRING_TASKS_TIMEFRAMES = ['cleanupLogs' => '1', 'removeToBeDeletedUsers' => '1'];
    const RECURRING_TASKS_UNITS = ['cleanupLogs' => 'WEEKS', 'removeToBeDeletedUsers' => 'WEEKS'];

    // enum constants
    const USER_ROLES = ['admin' => 'ADMIN', 'user' => 'USER', 'notActivated' => 'NOT_ACTIVATED', 'blocked' => 'BLOCKED', 'toBeDeleted' => 'TO_BE_DELETED'];
    const ARTICLE_STATUS = ['active' => 'ACTIVE', 'expired' => 'EXPIRED', 'toBeDeleted' => 'TO_BE_DELETED'];
    
    // email
    const EMAIL_USER_DOMAIN = '@student.uni-tuebingen.de';
    const EMAIL_SENDER_DOMAIN = '@fsi.uni-tuebingen.de';
    const EMAIL_SENDER_NAME = 'RRI';
    const EMAIL_SENDER_ADDRESS = 'rri' . Constants::EMAIL_SENDER_DOMAIN;
    
    // admin
    const USERNAME_ADMIN = 'admin';
    const PASSWORD_ADMIN = Passwords::PASSWORD_ADMIN;
    const NUMBER_OF_ENTRIES_PER_PAGE = 50;
    const EMAIL_ADMIN = 'rri@fsi.uni-tuebingen.de';
}
?>
