<?php
namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

/**
 * @link http://www.gpwebpay.cz/en/Faq
 */
class LanguageCodes extends StrictObject implements Codes
{
    /** ISO 639-1 @link https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes */
    const CS = 'cs'; // czech - čeština
    const SK = 'sk'; // slovak - slovenčina
    const EN = 'en'; // english
    const AR = 'ar'; // arabic - العربية
    const DA = 'da'; // danish - dansk
    const FI = 'fi'; // finnish - suomi
    const FR = 'fr'; // french - français
    const NL = 'nl'; // dutch - nederlands
    const IT = 'it'; // italian - italiano
    const LT = 'lt'; // lithuanian - lietuvių kalba
    const HU = 'hu'; // hungarian - magyar
    const DE = 'de'; // german - deutsch
    const NO = 'no'; // norwegian - norsk
    const PL = 'pl'; // polish - polszczyzna
    const PT = 'pt'; // portuguese - português
    const RU = 'ru'; // russian - русский
    const ES = 'es'; // spanish - español
    const SV = 'sv'; // swedish - svenska
    const UK = 'uk'; // ukrainian - українська

    /**
     * @return array|string[]
     */
    public static function getLanguageCodes()
    {
        return [
            self::CS,
            self::SK,
            self::EN,
            self::AR,
            self::DA,
            self::FI,
            self::FR,
            self::NL,
            self::IT,
            self::LT,
            self::HU,
            self::DE,
            self::NO,
            self::PL,
            self::PT,
            self::RU,
            self::ES,
            self::SV,
            self::UK,
        ];
    }

    /**
     * @param string $languageCode
     * @return bool
     */
    public static function isLanguageSupported(string $languageCode)
    {
        return in_array($languageCode, self::getLanguageCodes(), true);
    }
}