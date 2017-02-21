<?php
namespace Granam\GpWebPay\Exceptions;

use Granam\GpWebPay\Codes\LanguageCodes;

class GpWebPayResponseHasAnError extends \RuntimeException implements Runtime
{
    const OK_CODE = 0;
    const ADDITIONAL_INFO_REQUEST_CODE = 200;

    /**
     * @param int $prCode
     * @return bool
     */
    public static function isErrorCode(int $prCode)
    {
        return $prCode !== self::OK_CODE && $prCode !== self::ADDITIONAL_INFO_REQUEST_CODE;
    }

    private static $prCodes = [
        LanguageCodes::CS => [
            'genericProblem' => 'Technický problém v GP webpay systému, kontaktujete obchodníka',
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
– tento návratový kód je zapříčiněn aktivitou držitele karty (například pokusem o přechod zpět, použití refresh...).',
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
            'genericProblem' => 'Technical problem in GP webpay system, contact the merchant.',
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

    private static $srCodes = [
        LanguageCodes::CS => [
            1 => 'ORDERNUMBER',
            2 => 'MERCHANTNUMBER',
            6 => 'AMOUNT',
            7 => 'CURRENCY',
            8 => 'DEPOSITFLAG',
            10 => 'MERORDERNUM',
            11 => 'CREDITNUMBER',
            12 => 'OPERATION',
            18 => 'BATCH',
            22 => 'ORDER',
            24 => 'URL',
            25 => 'MD',
            26 => 'DESC',
            34 => 'DIGEST',
            1001 => 'Zamítnuto v autorizačním centru, katra blokována',
            1002 => 'Zamítnuto v autorizačním centru, autorizace zamítnuta',
            1003 => 'Zamítnuto v autorizačním centru, problém karty',
            1004 => 'Zamítnuto v autorizačním centru, technický problém',
            1005 => 'Zamítnuto v autorizačním centru, Problém účtu',
            3000 => 'Neověřeno v 3D. Vydavatel karty není zapojen do 3D nebo karta nebyla aktivována.',
            3001 => 'Držitel karty ověřen.',
            3002 => 'Neověřeno v 3D. Vydavatel karty nebo karta není zapojena do 3D',
            3004 => 'Neověřeno v 3D. Vydavatel karty není zapojen do 3D nebo karta nebyla aktivována',
            3005 => 'Zamítnuto v 3D. Technický problém při ověření držitele karty',
            3006 => 'Zamítnuto v 3D. Technický problém při ověření držitele karty',
            3007 => 'Zamítnuto v 3D. Technický problém v systému zúčtující banky. Kontaktujte obchodníka',
            3008 => 'Zamítnuto v 3D. Použit nepodporavný karetní produkt',
        ],
        LanguageCodes::EN => [
            1 => 'ORDERNUMBER',
            2 => 'MERCHANTNUMBER',
            6 => 'AMOUNT',
            7 => 'CURRENCY',
            8 => 'DEPOSITFLAG',
            10 => 'MERORDERNUM',
            11 => 'CREDITNUMBER',
            12 => 'OPERATION',
            18 => 'BATCH',
            22 => 'ORDER',
            24 => 'URL',
            25 => 'MD',
            26 => 'DESC',
            34 => 'DIGEST',
            1001 => 'Declined in AC, Card blocked',
            1002 => 'Declined in AC, Declined',
            1003 => 'Declined in AC, Card problem',
            1004 => 'Declined in AC, Technical problem in authorization process',
            1005 => 'Declined in AC, Account problem',
            3000 => 'Not Authenticated in 3D. Cardholder not authenticated in 3D.',
            3001 => 'Authenticated',
            3002 => 'Not Authenticated in 3D. Issuer or Cardholder not participating in 3D.',
            3004 => 'Not Authenticated in 3D. Issuer not participating or Cardholder not enrolled.',
            3005 => 'Declined in 3D. Technical problem during Cardholder authentication.',
            3006 => 'Declined in 3D. Technical problem during Cardholder authentication.',
            3007 => 'Declined in 3D. Acquirer technical problem. Contact the merchant.',
            3008 => 'Declined in 3D. Unsupported card product.',
        ],
    ];

    /** @var int */
    private $prCode;
    /** @var int */
    private $srCode;
    /** @var string|null */
    private $resultText;

    /**
     * @param int $prCode
     * @param int $srCode
     * @param string $resultText
     * @param int $exceptionCode
     * @param \Exception $previousException
     */
    public function __construct(
        int $prCode,
        int $srCode,
        string $resultText = '',
        $exceptionCode = null, // intentionally without scalar type hint
        \Exception $previousException = null
    )
    {
        $this->prCode = $prCode;
        $this->srCode = $srCode;
        $this->resultText = $resultText ? trim($resultText) : '';
        parent::__construct(
            ($this->resultText
                ? "{$this->resultText} - "
                : ''
            ) . $this->getLocalizedMessage(LanguageCodes::EN) . "; error codes {$prCode}/{$srCode}",
            $exceptionCode, // will be internally converted to int
            $previousException
        );
    }

    /**
     * @return int
     */
    public function getPrCode()
    {
        return $this->prCode;
    }

    /**
     * @return int
     */
    public function getSrCode()
    {
        return $this->srCode;
    }

    /**
     * @return string|null
     */
    public function getResultText()
    {
        return $this->resultText;
    }

    /**
     * @param string $languageCode
     * @return string
     */
    public function getLocalizedMessage(string $languageCode = LanguageCodes::EN)
    {
        $languageCode = strtolower(trim($languageCode));
        if ($languageCode !== LanguageCodes::CS && $languageCode !== LanguageCodes::EN) {
            trigger_error(
                "Unsupported language for error message requested: '$languageCode'"
                . ', \'' . LanguageCodes::EN . '\' is used instead',
                E_USER_WARNING
            );
            $languageCode = LanguageCodes::EN;
        }
        $message = self::$prCodes[$languageCode]['genericProblem'];
        if (array_key_exists($this->prCode, self::$prCodes[$languageCode])) {
            $message = self::$prCodes[$languageCode][$this->prCode];
        }
        if (array_key_exists($this->srCode, self::$srCodes[$languageCode])) {
            $message .= ' (' . self::$srCodes[$languageCode][$this->srCode] . ')';
        }

        return $message;
    }
}