<?php
class CurrencyUtil {
    private $locale;
    private $log;

    function __construct() {
        $this->locale = Constants::LOCALE_GERMAN;
    }
    
    /**
     * Initializes this object with the given locale. If not called, the German locale is the default.
     */
    function init($language) {
        $this->locale = Constants::LOCALE_GERMAN;
        
        if ($language == 'en') {
            // users will never pay with anything but Euro, so other locales are never set for now
            $this->locale = Constants::LOCALE_GERMAN;
        }
    }

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
    }
    
    /**
     * Formats the amount in the current currency style (e.g. 4467 -> 44,67€).
     */
    function formatCentsToCurrency($amount) {
        $currencyExchangeRateToEuro = $this->locale['currencyExchangeRateToEuro'];
        $decimalSymbol = $this->locale['decimalSymbol'];
        $currencySymbol = $this->locale['currencySymbol'];
        $amount = $amount * $currencyExchangeRateToEuro;
        $cents = sprintf('%02d', intval($amount % 100));
        if ($this->locale['printCurrencySymbolAfterAmount']) {
            return intval($amount / 100) . $decimalSymbol . $cents . ' ' . $currencySymbol;
        }
        return $currencySymbol . ' ' . intval($amount / 100) . $decimalSymbol . $cents;
    }
    
    /**
     * Returns the amount in cents from a currency string (e.g. 54,42€ -> 5442 or 3.7$ -> 370).
     */
    function getAmountFromCurrencyString($currencyString) {
        if ($currencyString == NULL || $currencyString == '') {
            return NULL;
        }
        
        $usedDecimalSeparator = '.';
        for ($i = strlen($currencyString) - 1; $i--; $i >= 0) {
            if ($currencyString[$i] == '.' || $currencyString[$i] == ',') {
                $usedDecimalSeparator = $currencyString[$i];
                break;
            }
        }
        
        $parts = explode($usedDecimalSeparator, $currencyString);
        if (count($parts) > 2 || count($parts) < 1) {
            return NULL;
        }
        if (count($parts) == 1) {
            $parts[] = '00';
        }
        
        $amount = preg_replace('/[^0-9]+/', '', $parts[0]);
        $cents = preg_replace('/[^0-9]+/', '', $parts[1]);
        if (strlen($cents) == 1) {
            $cents = $cents . '0';
        }
        $cents = $cents[0] . $cents[1];
        return intval($amount) * 100 + intval($cents);
    }
}
?>
