<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\CurrencyCodes;
use Granam\GpWebPay\Codes\LanguageCodes;
use Granam\GpWebPay\Codes\PayMethodCodes;
use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\Strict\Object\StrictObject;

class CardPayRequestValues extends StrictObject
{
    // required values
    /** @var int */
    private $orderNumber;
    /** @var int */
    private $amount;
    /** @var int */
    private $currency;
    /** @var int */
    private $depositFlag;
    // optional values
    /** @var string|null merchant data (note) */
    private $md;
    /** @var string|null */
    private $description;
    /** @var int|null */
    private $merOrderNum;
    /** @var string|null */
    private $lang;
    /** @var string|null */
    private $payMethod;
    /** @var string|null */
    private $disabledPayMethod;
    /** @var array|string[] */
    private $payMethods = [];
    /** @var string|null */
    private $email;
    /** @var string|null */
    private $referenceNumber;
    /** @var string|null */
    private $addInfo;
    /** @var int|null */
    private $fastPayId;

    /**
     * @param int $orderNumber with max length of 15
     * @param float $price real price of the order (purchase) like 3.74 EUR
     * @param int $currencyNumericCode ISO 4217
     * @param bool $depositFlag false = instant payment not required, true = requires immediate payment
     * @param CurrencyCodes $currencyCodes list of supported currencies in ISO 4217
     * @param string $merchantNote = null
     * @param string $description = null
     * @param int $merchantOrderIdentification = null
     * @param string $lang = LanguageCodes::EN
     * @param string $payMethod = null
     * @param string $disabledPayMethod = null
     * @param array $payMethods = []
     * @param string $cardHolderEmail = null
     * @param string $referenceNumber = null
     * @param string $addInfo = null
     * @param string $fastPayId = null
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     * @throws \Granam\GpWebPay\Exceptions\UnknownCurrency
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     * @throws \Granam\GpWebPay\Exceptions\InvalidAsciiRange
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedLanguage
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedPayMethod
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     * @throws \Granam\GpWebPay\Exceptions\InvalidEmail
     */
    public function __construct(
        int $orderNumber,
        float $price,
        int $currencyNumericCode,
        bool $depositFlag, // false = instant payment not required, true = requires immediate payment
        CurrencyCodes $currencyCodes,
        string $merchantNote = null,
        string $description = null,
        int $merchantOrderIdentification = null,
        string $lang = LanguageCodes::EN,
        string $payMethod = null,
        string $disabledPayMethod = null,
        array $payMethods = [],
        string $cardHolderEmail = null,
        string $referenceNumber = null,
        string $addInfo = null,
        string $fastPayId = null
    )
    {
        $this->setOrderNumber($orderNumber);
        $this->setPrice($price, $currencyNumericCode, $currencyCodes);
        $this->setCurrency($currencyNumericCode, $currencyCodes);
        $this->depositFlag = (int)$depositFlag; // 0 / 1
        $this->setMd($merchantNote);
        $this->setDescription($description);
        $this->setMerOrderNum($merchantOrderIdentification);
        $this->setLang($lang);
        $this->setPayMethod($payMethod);
        $this->setDisabledPayMethod($disabledPayMethod);
        $this->setPayMethods($payMethods);
        $this->setEmail($cardHolderEmail);
        $this->setReferenceNumber($referenceNumber);
        $this->setAddInfo($addInfo);
        $this->setFastPayId($fastPayId);
    }

    const MAXIMAL_LENGTH_OF_ORDER_NUMBER = 15;

    /**
     * @param int $orderNumber
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    private function setOrderNumber(int $orderNumber)
    {
        $this->guardMaximalLength($orderNumber, self::MAXIMAL_LENGTH_OF_ORDER_NUMBER, RequestDigestKeys::ORDERNUMBER);
        $this->orderNumber = $orderNumber;
    }

    /**
     * @param int|float|string $value
     * @param int $maximalLength
     * @param string $name
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    private function guardMaximalLength($value, int $maximalLength, string $name)
    {
        if (strlen((string)$value) > $maximalLength) {
            throw new Exceptions\ValueTooLong(
                "Maximal length of {$name} is {$maximalLength}, got one with length of "
                . strlen((string)$value) . " and value '{$value}'"
            );
        }
    }

    /**
     * @param float $price
     * @param int $currencyCode
     * @param CurrencyCodes $currencyCodes
     * @throws \Granam\GpWebPay\Exceptions\UnknownCurrency
     */
    private function setPrice(float $price, int $currencyCode, CurrencyCodes $currencyCodes)
    {
        $this->amount = (int)round($price * $currencyCodes->getCurrencyPrecision($currencyCode));
    }

    /**
     * @param int $currencyCode
     * @param CurrencyCodes $currencyCodes
     * @throws \Granam\GpWebPay\Exceptions\UnknownCurrency
     */
    private function setCurrency(int $currencyCode, CurrencyCodes $currencyCodes)
    {
        if ($currencyCodes->isCurrencyNumericCode($currencyCode)) {
            throw new Exceptions\UnknownCurrency(
                'Unknown ' . RequestDigestKeys::CURRENCY
                . " code given, got '{$currencyCode}', expected one of those defined by ISO 4217"
            );
        }
        $this->currency = $currencyCode;
    }

    const MAXIMAL_LENGTH_OF_MD = 255;

    /**
     * @param string $merchantNote with maximal length of 255 and ASCII characters in range of 0x20–0x7E
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     * @throws \Granam\GpWebPay\Exceptions\InvalidAsciiRange
     */
    private function setMd(string $merchantNote = '')
    {
        $merchantNote = trim($merchantNote);
        $this->guardMaximalLength($merchantNote, self::MAXIMAL_LENGTH_OF_MD, RequestDigestKeys::MD . ' (merchant note)');
        $this->guardAsciiRange($merchantNote, RequestDigestKeys::MD . ' (merchant note)');
        $this->md = $merchantNote;
    }

    /**
     * @param string $value
     * @param string $name
     * @throws \Granam\GpWebPay\Exceptions\InvalidAsciiRange
     */
    private function guardAsciiRange(string $value, string $name)
    {
        if (preg_match('~(?<outOfRange>[^0x20–0x7E])~', $value, $matches)) {
            throw new Exceptions\InvalidAsciiRange(
                $name . ' can contains only ASCII characters in range of 0x20 – 0x7E'
                . ', got a value with ' . count($matches['outOfRange'])
                . " non-matching characters in string '{$value}'"
            );
        }
    }

    const MAXIMAL_LENGTH_OF_DESCRIPTION = 255;

    /**
     * @param string $description with maximal length of 255 and ASCII characters in range of 0x20–0x7E
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     * @throws \Granam\GpWebPay\Exceptions\InvalidAsciiRange
     */
    private function setDescription(string $description)
    {
        $description = trim($description);
        $this->guardMaximalLength($description, self::MAXIMAL_LENGTH_OF_DESCRIPTION, RequestDigestKeys::DESCRIPTION);
        $this->guardAsciiRange($description, RequestDigestKeys::DESCRIPTION);
        $this->description = $description;
    }

    /*
     * Some banks uses shorter MEMORDERNUM (rest is truncated)
     * Komerční banka 16
     * Raiffiesen bank 10
     * UniCredit bank 12
     * (others are unknown - see GP_webpay_HTTP_EN.pdf / GP_webpay_HTTP.pdf)
     */
    const MAXIMAL_LENGTH_OF_MERORDERNUM = 30;

    /**
     * @param int $merchantOrderIdentification with maximal length of 30 characters
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    private function setMerOrderNum(int $merchantOrderIdentification)
    {
        $this->guardMaximalLength($merchantOrderIdentification, self::MAXIMAL_LENGTH_OF_MERORDERNUM, RequestDigestKeys::MERORDERNUM);
        $this->merOrderNum = $merchantOrderIdentification;
    }

    /**
     * @param string $lang
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedLanguage
     */
    private function setLang(string $lang)
    {
        $lang = trim($lang);
        if (!LanguageCodes::isLanguageSupported($lang)) {
            throw new Exceptions\UnsupportedLanguage(
                "Given language code is not supported '{$lang}', use on of "
                . implode(',', LanguageCodes::getLanguageCodes())
            );
        }
        $this->lang = $lang;
    }

    /**
     * @param string $payMethod supported val: CRD – payment card | MCM – MasterCard Mobile | MPS – MasterPass | BTNCS - PLATBA 24
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedPayMethod
     */
    private function setPayMethod(string $payMethod)
    {
        $payMethod = trim($payMethod);
        $payMethod = strtoupper($payMethod);
        if (PayMethodCodes::isSupportedPaymentMethod($payMethod)) {
            throw new Exceptions\UnsupportedPayMethod(
                'Given ' . RequestDigestKeys::PAYMETHOD . " '{$payMethod}' is not supported, use one of "
                . implode(',', PayMethodCodes::getPayMethodCodes())
            );
        }
        $this->payMethod = $payMethod;
    }

    /**
     * Explicitly disable use of a payment method, even if is technically possible.
     *
     * @param string $disabledPayMethod supported val: CRD – payment card | MCM – MasterCard Mobile | MPS – MasterPass | BTNCS - PLATBA 24
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedPayMethod
     */
    private function setDisabledPayMethod(string $disabledPayMethod)
    {
        $disabledPayMethod = trim($disabledPayMethod);
        if (!PayMethodCodes::isSupportedPaymentMethod($disabledPayMethod)) {
            throw new Exceptions\UnsupportedPayMethod(
                'Can not disable ' . RequestDigestKeys::DISABLEPAYMETHOD . " by unknown pay method '{$disabledPayMethod}',"
                . ' use one of ' . implode(',', PayMethodCodes::getPayMethodCodes())
            );
        }
        $this->disabledPayMethod = $disabledPayMethod;
    }

    /**
     * Sets allowed pay methods, therefore disable use of non-listed payment methods, even if they are technically possible.
     * If DISABLEPAYMETHOD is set as well than an intersection of both rules is used.
     *
     * @param array|string[] $payMethods supported val: CRD – payment card | MCM – MasterCard Mobile | MPS – MasterPass | BTNCS - PLATBA 24
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedPayMethod
     */
    private function setPayMethods(array $payMethods)
    {
        foreach ($payMethods as &$payMethod) {
            $payMethod = strtoupper(trim($payMethod));
        }
        unset($payMethod);
        $unknownPayMethods = array_diff($payMethods, PayMethodCodes::getPayMethodCodes());
        if (count($unknownPayMethods) > 0) {
            throw new Exceptions\UnsupportedPayMethod(
                implode(',', $unknownPayMethods) . ' as given pay methods are not supported, use only '
                . implode(',', PayMethodCodes::getPayMethodCodes())
            );
        }
        $this->payMethods = implode(',', $payMethods);
    }

    const MAXIMAL_LENGTH_OF_EMAIL = 255;

    /**
     * @param string $email with maximal length of 255
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     * @throws \Granam\GpWebPay\Exceptions\InvalidEmail
     */
    private function setEmail(string $email)
    {
        $email = trim($email);
        $this->guardMaximalLength($email, self::MAXIMAL_LENGTH_OF_EMAIL, RequestDigestKeys::EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new Exceptions\InvalidEmail("Given email '{$email}' has invalid format");
        }
        $this->email = $email;
    }

    const MAXIMAL_LENGTH_OF_REFERENCENUMBER = 20;

    /**
     * Merchant internal ID of an order
     *
     * @param string $referenceNumber with maximal length of 20
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    private function setReferenceNumber(string $referenceNumber)
    {
        $referenceNumber = trim($referenceNumber);
        $this->guardMaximalLength($referenceNumber, self::MAXIMAL_LENGTH_OF_REFERENCENUMBER, RequestDigestKeys::REFERENCENUMBER);
        $this->referenceNumber = $referenceNumber;
    }

    const MAXIMAL_LENGTH_OF_ADDINFO = 24000;

    /**
     * XML schema
     *
     * @param string $addInfo with maximal length of 24000
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    private function setAddInfo(string $addInfo)
    {
        $addInfo = trim($addInfo);
        $this->guardMaximalLength($addInfo, self::MAXIMAL_LENGTH_OF_ADDINFO, RequestDigestKeys::ADDINFO);
        $this->addInfo = $addInfo;
    }

    const MAXIMAL_LENGTH_OF_FASTPAYID = 15;

    /**
     * @param int $fastPayId with maximal length of 15
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    private function setFastPayId(int $fastPayId)
    {
        $this->guardMaximalLength($fastPayId, self::MAXIMAL_LENGTH_OF_FASTPAYID, RequestDigestKeys::FASTPAYID);
        $this->fastPayId = $fastPayId;
    }

    /**
     * @return int
     */
    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function getCurrency(): int
    {
        return $this->currency;
    }

    /**
     * @return int
     */
    public function getDepositFlag(): int
    {
        return $this->depositFlag;
    }

    /**
     * @return null|string
     */
    public function getMd()
    {
        return $this->md;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int|null
     */
    public function getMerOrderNum()
    {
        return $this->merOrderNum;
    }

    /**
     * @return null|string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return null|string
     */
    public function getPayMethod()
    {
        return $this->payMethod;
    }

    /**
     * @return null|string
     */
    public function getDisabledPayMethod()
    {
        return $this->disabledPayMethod;
    }

    /**
     * @return array|\string[]
     */
    public function getPayMethods()
    {
        return $this->payMethods;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return null|string
     */
    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    /**
     * @return null|string
     */
    public function getAddInfo()
    {
        return $this->addInfo;
    }

    /**
     * @return int|null
     */
    public function getFastPayId()
    {
        return $this->fastPayId;
    }
}