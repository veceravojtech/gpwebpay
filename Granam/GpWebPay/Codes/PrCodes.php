<?php
namespace Granam\GpWebPay\Codes;

use Granam\Strict\Object\StrictObject;

class PrCodes extends StrictObject implements Codes
{
    private static $prCodes = [
        LanguageCodes::CS => [
            'genericProblem' => 'Technický problém v GP webpay systému, kontaktujte obchodníka',
            0 => 'OK',
            1 => 'Pole příliš dlouhé',
            2 => 'Pole příliš krátké',
            3 => 'Chybný obsah pole',
            4 => 'Pole je prázdné',
            5 => 'Chybí povinné pole',
            11 => 'Neznámý obchodník',
            14 => 'Duplikátní číslo platby',
            15 => 'Objekt nenalezen',
            17 => 'Částka k zaplacení překročila povolenou (autorizovanou) částku',
            18 => 'Součet vracených částek překročil zaplacenou částku',
            20 => 'Objekt není ve stavu odpovídajícím této operaci
Info: Pokud v případě vytváření objednávky (CREATE_ORDER) obdrží obchodník tento návratový kód,
vytvoření objednávky již proběhlo a objednávka je v určitém stavu
– tento návratový kód je zapříčiněn aktivitou držitele karty (například pokusem o přechod zpět, použití refresh...)',
            25 => 'Uživatel není oprávněn k provedení operace',
            26 => 'Technický problém při spojení s autorizačním centrem',
            27 => 'Chybný typ objednávky',
            28 => 'Zamítnuto v 3D Info: důvod zamítnutí udává SRCODE Declined in 3D',
            30 => 'Zamítnuto v autorizačním centru',
            31 => 'Chybný podpis (digest)',
            35 => 'Expirovaná session (nastává při vypršení webové session při zadávání karty)',
            50 => 'Držitel karty zrušil platbu',
            200 => 'Žádost o doplňující informace',
            1000 => 'Technický problém',
        ],
        LanguageCodes::EN => [
            'genericProblem' => 'Technical problem in GP webpay system, contact the merchant',
            0 => 'OK',
            1 => 'Field too long',
            2 => 'Field too short',
            3 => 'Incorrect content of field',
            4 => 'Field is null',
            5 => 'Missing required field',
            11 => 'Unknown merchant',
            14 => 'Duplicate order number',
            15 => 'Object not found',
            17 => 'Amount to deposit exceeds approved amount',
            18 => 'Total sum of credited amounts exceeded deposited amount',
            20 => 'Object not in valid state for operation',
            25 => 'Operation not allowed for user',
            26 => 'Technical problem in connection to authorization center',
            27 => 'Incorrect order type',
            28 => 'Declined in 3D',
            30 => 'Declined in authorization centre',
            31 => 'Wrong digest',
            35 => 'Session expired (happens on web session expiration when entering a card)',
            50 => 'The cardholder canceled the payment',
            200 => 'Additional info request',
            1000 => 'Technical problem',
        ],
    ];

    /**
     * @return array|string[][][]
     */
    public static function getPrCodes()
    {
        return self::$prCodes;
    }

    const LANGUAGE_CS = LanguageCodes::CS;
    const LANGUAGE_EN = LanguageCodes::EN;

    /**
     * @param int $prCode
     * @param string $languageCode
     * @return string
     */
    public static function getLocalizedMainMessage(int $prCode, string $languageCode = self::LANGUAGE_EN)
    {
        $languageCode = strtolower($languageCode);
        if (array_key_exists($languageCode, self::$prCodes) && array_key_exists($prCode, self::$prCodes[$languageCode])) {
            return self::$prCodes[$languageCode][$prCode];
        }
        if ($languageCode !== self::LANGUAGE_EN && array_key_exists($prCode, self::$prCodes[self::LANGUAGE_EN])) {
            trigger_error(
                "Unsupported language for main error code requested: '{$languageCode}', " . self::LANGUAGE_EN . ' used instead',
                E_USER_NOTICE
            );

            return self::$prCodes[self::LANGUAGE_EN][$prCode]; // fallback
        }

        trigger_error("Unknown PR error code: '{$prCode}', a generic text used instead", E_USER_WARNING);

        return self::$prCodes[$languageCode]['genericProblem'];
    }
}