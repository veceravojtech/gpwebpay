<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\ResponseDigestKeys;
use Granam\GpWebPay\Codes\ResponsePayloadKeys;
use Granam\GpWebPay\Exceptions\GpWebPayErrorResponse;
use Granam\Integer\Tools\ToInteger;
use Granam\Scalar\Tools\ToString;
use Granam\Strict\Object\StrictObject;

class CardPayResponse extends StrictObject implements PayResponse
{
    private static $expectedKeys = [
        ResponseDigestKeys::OPERATION => true,
        ResponseDigestKeys::ORDERNUMBER => true,
        ResponseDigestKeys::PRCODE => true,
        ResponseDigestKeys::SRCODE => true,
        ResponsePayloadKeys::DIGEST => true,
        ResponsePayloadKeys::DIGEST1 => true,
        ResponseDigestKeys::MERORDERNUM => false,
        ResponseDigestKeys::MD => false,
        ResponseDigestKeys::RESULTTEXT => false,
        ResponseDigestKeys::USERPARAM1 => false, // hash of the payment card number
        ResponseDigestKeys::ADDINFO => false,
    ];

    private static $integerValues = [
        ResponseDigestKeys::PRCODE,
        ResponseDigestKeys::SRCODE,
        ResponseDigestKeys::ORDERNUMBER,
    ];

    /**
     * @param array $valuesFromGetOrPost
     * @return CardPayResponse
     * @throws \Granam\GpWebPay\Exceptions\GpWebPayErrorResponse
     * @throws \Granam\GpWebPay\Exceptions\BrokenResponse
     * @throws \Granam\Integer\Tools\Exceptions\WrongParameterType
     * @throws \Granam\Integer\Tools\Exceptions\ValueLostOnCast
     * @throws \Granam\Scalar\Tools\Exceptions\WrongParameterType
     */
    public static function createFromArray(array $valuesFromGetOrPost)
    {
        $normalizedValues = [];
        foreach (self::$expectedKeys as $key => $required) {
            $valuesFromGetOrPost[$key] = $valuesFromGetOrPost[$key] ?? null;
            if ($required && $valuesFromGetOrPost[$key] === null) {
                throw new Exceptions\BrokenResponse(
                    'Values to create ' . static::class . " are missing required '{$key}'"
                );
            }
            if ($valuesFromGetOrPost[$key] === null) {
                $normalizedValues[$key] = null;
            } else if (in_array($key, self::$integerValues, true)) {
                $normalizedValues[$key] = ToInteger::toInteger($valuesFromGetOrPost[$key]);
            } else {
                $normalizedValues[$key] = ToString::toString($valuesFromGetOrPost[$key]);
            }
        }

        return new static(
            $normalizedValues[ResponseDigestKeys::OPERATION],
            $normalizedValues[ResponseDigestKeys::ORDERNUMBER],
            $normalizedValues[ResponseDigestKeys::PRCODE],
            $normalizedValues[ResponseDigestKeys::SRCODE],
            $normalizedValues[ResponsePayloadKeys::DIGEST],
            $normalizedValues[ResponsePayloadKeys::DIGEST1],
            $normalizedValues[ResponseDigestKeys::MERORDERNUM],
            $normalizedValues[ResponseDigestKeys::MD],
            $normalizedValues[ResponseDigestKeys::RESULTTEXT],
            $normalizedValues[ResponseDigestKeys::USERPARAM1],
            $normalizedValues[ResponseDigestKeys::ADDINFO]
        );
    }

    /** @var array $parametersForDigest */
    private $parametersForDigest;
    /** @var string */
    private $digest;
    /** @var string */
    private $digest1;

    /**
     * @param string $operation
     * @param int $orderNumber
     * @param int $prCode
     * @param int $srCode
     * @param string $digest
     * @param string $digest1
     * @param string|null $merOrderNum
     * @param string|null $md
     * @param string|null $resultText
     * @param string|null $userParam1
     * @param string|null $addInfo
     * @throws \Granam\GpWebPay\Exceptions\GpWebPayErrorResponse
     */
    public function __construct(
        string $operation,
        int $orderNumber,
        int $prCode,
        int $srCode,
        string $digest,
        string $digest1,
        string $merOrderNum = null,
        string $md = null,
        string $resultText = null,
        string $userParam1 = null,
        string $addInfo = null
    )
    {
        if (GpWebPayErrorResponse::isError($prCode)) {
            throw new Exceptions\GpWebPayErrorResponse($prCode, $srCode, $resultText);
        }
        // keys HAVE TO be exactly in this order to provide correct values for digest calculation
        $this->parametersForDigest[ResponseDigestKeys::OPERATION] = $operation; // string up to length of 20 (always FINALIZE_ORDER)
        $this->parametersForDigest[ResponseDigestKeys::ORDERNUMBER] = $orderNumber; // numeric up to length of 15
        if ($merOrderNum !== null) {
            $this->parametersForDigest[ResponseDigestKeys::MERORDERNUM] = $merOrderNum; // numeric up to length of 30
        }
        if ($md !== null) {
            $this->parametersForDigest[ResponseDigestKeys::MD] = $md; // string up to length of 255
        }
        $this->parametersForDigest[ResponseDigestKeys::PRCODE] = $prCode; // numeric
        $this->parametersForDigest[ResponseDigestKeys::SRCODE] = $srCode; // numeric
        if ($resultText !== null) {
            $this->parametersForDigest[ResponseDigestKeys::RESULTTEXT] = $resultText; // string up to length of 255
        }
        if ($userParam1 !== null) {
            $this->parametersForDigest[ResponseDigestKeys::USERPARAM1] = $userParam1; // string up to length of 64
        }
        if ($addInfo !== null) {
            $this->parametersForDigest[ResponseDigestKeys::ADDINFO] = $addInfo; // long string
        }
        $this->digest = $digest; // string up to length of 2000
        $this->digest1 = $digest1; // string up to length of 2000
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return GpWebPayErrorResponse::isError($this->parametersForDigest[ResponseDigestKeys::PRCODE]);
    }

    /**
     * @return array
     */
    public function getParametersForDigest(): array
    {
        return $this->parametersForDigest;
    }

    /**
     * @return string
     */
    public function getDigest(): string
    {
        return $this->digest;
    }

    /**
     * @return string
     */
    public function getDigest1(): string
    {
        return $this->digest1;
    }

    /**
     * @return int
     */
    public function getSrCode(): int
    {
        return $this->parametersForDigest[ResponseDigestKeys::SRCODE];
    }

    /**
     * @return int
     */
    public function getPrCode(): int
    {
        return $this->parametersForDigest[ResponseDigestKeys::PRCODE];
    }

    /**
     * @return string|null
     */
    public function getResultText()
    {
        return $this->parametersForDigest[ResponseDigestKeys::RESULTTEXT] ?? null;
    }
}