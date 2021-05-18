<?php
class CurrencyUtil {
    private $LOCALE_ENGLISH = ['currencyIsoCode' => 'GBP', 'currencySymbol' => '£', 'currencyExchangeRateToEuro' => 1 / 1.16, 'decimalSymbol' => '.', 'printCurrencySymbolAfterAmount' => false];
    private $LOCALE_GERMAN = ['currencyIsoCode' => 'EUR', 'currencySymbol' => '€', 'currencyExchangeRateToEuro' => 1, 'decimalSymbol' => ',', 'printCurrencySymbolAfterAmount' => true];
    private $locale;

    function __construct() {
        $this->locale = $this->LOCALE_GERMAN;
    }
    
    /**
     * Initializes this object with the given locale. If not called, the German locale is the default.
     */
    function init($language) {
        $this->locale = $this->LOCALE_GERMAN;
        
        // users will never pay with anything but Euro, so other locales are never set for now
        if ($language == 'en') {
            $this->locale = $this->LOCALE_GERMAN;
        }
    }
    
    /**
     * Formats the amount in the current currency style (e.g. 4467 -> 44,67€).
     */
    function formatCentsToCurrency($amount) {
        $amount = $amount * $this->locale['currencyExchangeRateToEuro'];
        return intval($amount / 100) . $this->locale['decimalSymbol'] . intval($amount % 100) . ' ' . $this->locale['currencySymbol'];
    }
    
    /**
     * Returns the amount in cents from a currency string (e.g. 54,42€ -> 5442 or 3.7$ -> 370).
     */
    function getAmountFromCurrencyString($currencyString) {
        return 'NOT_IMPLEMENTED_YET';
    }
    
    /**
     * Checks if the given currency string conforms to the format of the current currency.
     */
    function isValidAmountInCurrentCurrency($currencyString) {
        return true;
    }
}
?>
