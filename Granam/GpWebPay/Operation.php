<?php
namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

class Operation extends StrictObject
{
    /** @var int $orderNumber */
    private $orderNumber;
    /** @var int $amount */
    private $amount;
    /** @var int $currency */
    private $currency;
    /** @var null|string $gatewayKey */
    private $gatewayKey;
    /** @var string|null $md as merchant data (note) */
    private $md;
    /** @var null|string $responseUrl */
    private $responseUrl;
    /** @var string|null $description */
    private $description;
    /** @var int|null $merchantOrderNumber */
    private $merchantOrderNumber;
    /** @var string|null $lang */
    private $lang;
    /** @var string|null $userParam1 */
    private $userParam1;
    /** @var string|null $payMethod */
    private $payMethod;
    /** @var string|null $disablePayMethod */
    private $disablePayMethod;
    /** @var array|string[] $payMethods */
    private $payMethods = [];
    /** @var string|null $email */
    private $email;
    /** @var string|null $referenceNumber */
    private $referenceNumber;
    /** @var int|null fastPayId */
    private $fastPayId;

    /**
     * @param int $orderNumber max. length is 15
     * @param float $amount
     * @param int $currencyCode max. length is 3
     * @param string $gatewayKey
     * @param string $responseUrl
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     */
    public function __construct(
        int $orderNumber,
        float $amount,
        int $currencyCode,
        string $gatewayKey = null,
        string $responseUrl = null
    )
    {

        $this->setOrderNumber($orderNumber);
        $this->setAmount($amount);
        $this->setCurrency($currencyCode);
        if (is_string($gatewayKey)) {
            $gatewayKey = trim($gatewayKey);
        }
        $this->gatewayKey = $gatewayKey;
        $this->md = $gatewayKey;
        if (is_string($responseUrl)) {
            $responseUrl = trim($responseUrl);
        }
        $this->responseUrl = $responseUrl;
    }

    /**
     * @param int $orderNumber
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     */
    private function setOrderNumber(int $orderNumber)
    {
        if (strlen($orderNumber) > 15) {
            throw new Exceptions\InvalidArgumentException(
                DigestKeys::ORDERNUMBER . " maximal length is 15, got '{$orderNumber}' with length of "
                . strlen($orderNumber)
            );
        }
        $this->orderNumber = $orderNumber;
    }

    /**
     * @param float $amount
     * @return Operation
     */
    private function setAmount(float $amount)
    {
        $this->amount = $amount * 100;

        return $this;
    }

    /**
     * @param int $currencyCode
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     */
    private function setCurrency(int $currencyCode)
    {
        if (!CurrencyCodes::isCurrencyCode($currencyCode)) {
            throw new Exceptions\InvalidArgumentException(
                'Unknown ' . DigestKeys::CURRENCY . " code given, got '{$currencyCode}', expected one of "
                . implode(',', CurrencyCodes::getCurrencyCodes())
            );
        }
        $this->currency = $currencyCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return null|string
     */
    public function getResponseUrl()
    {
        return $this->responseUrl;
    }

    const MAXIMAL_LENGTH_OF_URL = 300;

    /**
     * @param string $responseUrl with maximal length of 300 characters
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\InvalidUrl
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    public function setResponseUrl(string $responseUrl)
    {
        $responseUrl = trim($responseUrl);
        if (!filter_var($responseUrl, FILTER_VALIDATE_URL)) {
            throw new Exceptions\InvalidUrl('Given ' . DigestKeys::URL . " is not valid: '{$responseUrl}'");
        }
        $this->guardMaximalLength($responseUrl, self::MAXIMAL_LENGTH_OF_URL, DigestKeys::URL);

        $this->responseUrl = $responseUrl;

        return $this;
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
     * Gives merchant data (note), if any
     *
     * @return string|string
     */
    public function getMd()
    {
        return $this->md;
    }

    const MAXIMAL_LENGTH_OF_MD = 255;

    /**
     * @param string $merchantNote with maximal length of 255 and ASCII characters in range of 0x20–0x7E
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     * @throws \Granam\GpWebPay\Exceptions\InvalidAsciiRange
     */
    public function setMd(string $merchantNote = '')
    {
        $merchantNote = trim($merchantNote);
        $this->guardMaximalLength($merchantNote, self::MAXIMAL_LENGTH_OF_MD, DigestKeys::MD . ' (merchant note)');
        $this->guardAsciiRange($merchantNote, DigestKeys::MD . ' (merchant note)');

        $this->md = $merchantNote;

        return $this;
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

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    const MAXIMAL_LENGTH_OF_DESCRIPTION = 255;

    /**
     * @param string $description with maximal length of 255 and ASCII characters in range of 0x20–0x7E
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     * @throws \Granam\GpWebPay\Exceptions\InvalidAsciiRange
     */
    public function setDescription(string $description)
    {
        $description = trim($description);
        $this->guardMaximalLength($description, self::MAXIMAL_LENGTH_OF_DESCRIPTION, DigestKeys::DESCRIPTION);
        $this->guardAsciiRange($description, DigestKeys::DESCRIPTION);

        $this->description = $description;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMerchantOrderNumber()
    {
        return $this->merchantOrderNumber;
    }

    const MAXIMAL_LENGTH_OF_MERORDERNUM = 30;

    /**
     * @param int $merchantOrderNumber with maximal length of 30 characters
     * @return $this
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    public function setMerchantOrderNumber(int $merchantOrderNumber)
    {
        $this->guardMaximalLength($merchantOrderNumber, self::MAXIMAL_LENGTH_OF_MERORDERNUM, DigestKeys::MERORDERNUM);
        $this->merchantOrderNumber = $merchantOrderNumber;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getGatewayKey()
    {
        return $this->gatewayKey;
    }

    /**
     * @param string $lang
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedLanguage
     */
    public function setLang(string $lang)
    {
        $lang = trim($lang);
        if (!LanguageCodes::isLanguageSupported($lang)) {
            throw new Exceptions\UnsupportedLanguage(
                "Given language code is not supported '{$lang}', use on of "
                . implode(',', LanguageCodes::getLanguageCodes())
            );
        }
        $this->lang = $lang;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return string
     */
    public function getUserParam1()
    {
        return $this->userParam1;
    }

    const MAXIMAL_LENGTH_OF_USERPARAM1 = 64;

    /**
     * @param string $userParam1 max. length is 255
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    public function setUserParam1(string $userParam1)
    {
        $userParam1 = trim($userParam1);
        $this->guardMaximalLength($userParam1, self::MAXIMAL_LENGTH_OF_USERPARAM1, DigestKeys::USERPARAM1);
        $this->userParam1 = $userParam1;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayMethod()
    {
        return $this->payMethod;
    }

    /**
     * @param string $payMethod supported val: CRD – payment card | MCM – MasterCard Mobile | MPS – MasterPass | BTNCS - PLATBA 24
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedPayMethod
     */
    public function setPayMethod(string $payMethod)
    {
        $payMethod = trim($payMethod);
        $payMethod = strtoupper($payMethod);
        if (PayMethodCodes::isSupportedPaymentMethod($payMethod)) {
            throw new Exceptions\UnsupportedPayMethod(
                'Given ' . DigestKeys::PAYMETHOD . " '{$payMethod}' is not supported, use one of "
                . implode(',', PayMethodCodes::getPayMethodCodes())
            );
        }

        $this->payMethod = $payMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisablePayMethod()
    {
        return $this->disablePayMethod;
    }

    /**
     * Explicitly disable use of a payment method, even if is technically possible.
     *
     * @param string $disablePayMethod supported val: CRD – payment card | MCM – MasterCard Mobile | MPS – MasterPass | BTNCS - PLATBA 24
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedPayMethod
     */
    public function setDisablePayMethod(string $disablePayMethod)
    {
        $disablePayMethod = trim($disablePayMethod);
        if (!PayMethodCodes::isSupportedPaymentMethod($disablePayMethod)) {
            throw new Exceptions\UnsupportedPayMethod(
                'Can not disable ' . DigestKeys::DISABLEPAYMETHOD . " by unknown pay method '{$disablePayMethod}',"
                . ' use one of ' . implode(',', PayMethodCodes::getPayMethodCodes())
            );
        }

        $this->disablePayMethod = $disablePayMethod;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getPayMethods()
    {
        return $this->payMethods;
    }

    /**
     * Sets allowed pay methods, therefore disable use of non-listed payment methods, even if they are technically possible.
     * If DISABLEPAYMETHOD is set as well than an intersection of both rules is used.
     *
     * @param array|string[] $payMethods supported val: CRD – payment card | MCM – MasterCard Mobile | MPS – MasterPass | BTNCS - PLATBA 24
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\UnsupportedPayMethod
     */
    public function setPayMethods(array $payMethods)
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

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    const MAXIMAL_LENGTH_OF_EMAIL = 255;

    /**
     * @param string $email with maximal length of 255
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     * @throws \Granam\GpWebPay\Exceptions\InvalidEmail
     */
    public function setEmail(string $email)
    {
        $email = trim($email);
        $this->guardMaximalLength($email, self::MAXIMAL_LENGTH_OF_EMAIL, DigestKeys::EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new Exceptions\InvalidEmail("Given email '{$email}' has invalid format");
        }

        $this->email = $email;

        return $this;
    }

    /**
     * @return string|string
     */
    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    const MAXIMAL_LENGTH_OF_REFERENCENUMBER = 20;

    /**
     * Merchant internal ID of an order
     *
     * @param string $referenceNumber with maximal length of 20
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    public function setReferenceNumber(string $referenceNumber)
    {
        $referenceNumber = trim($referenceNumber);
        $this->guardMaximalLength($referenceNumber, self::MAXIMAL_LENGTH_OF_REFERENCENUMBER, DigestKeys::REFERENCENUMBER);
        $this->referenceNumber = $referenceNumber;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getFastPayId()
    {
        return $this->fastPayId;
    }

    const MAXIMAL_LENGTH_OF_FASTPAYID = 15;

    /**
     * @param int $fastPayId with maximal length of 15
     * @return Operation
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    public function setFastPayId(int $fastPayId)
    {
        $this->guardMaximalLength($fastPayId, self::MAXIMAL_LENGTH_OF_FASTPAYID, DigestKeys::FASTPAYID);
        $this->fastPayId = $fastPayId;

        return $this;
    }

}