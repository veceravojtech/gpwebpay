<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\ResponsePayloadKeys;
use Granam\GpWebPay\Exceptions\GpWebPayResponseHasAnError;
use Granam\Integer\Tools\ToInteger;
use Granam\Scalar\Tools\ToString;
use Granam\Strict\Object\StrictObject;

class CardPayResponse extends StrictObject
{
    /**
     * @param array $valuesFromGetOrPost
     * @return CardPayResponse
     * @throws \Granam\GpWebPay\Exceptions\BrokenResponse
     * @throws \Granam\Integer\Tools\Exceptions\WrongParameterType
     * @throws \Granam\Integer\Tools\Exceptions\ValueLostOnCast
     * @throws \Granam\Scalar\Tools\Exceptions\WrongParameterType
     */
    public static function createFromArray(array $valuesFromGetOrPost)
    {
        $keys = [
            ResponsePayloadKeys::OPERATION => true,
            ResponsePayloadKeys::ORDERNUMBER => true,
            ResponsePayloadKeys::MERORDERNUM => false,
            ResponsePayloadKeys::MD => false,
            ResponsePayloadKeys::PRCODE => true,
            ResponsePayloadKeys::SRCODE => true,
            ResponsePayloadKeys::RESULTTEXT => false,
            ResponsePayloadKeys::USERPARAM1 => false,
            ResponsePayloadKeys::ADDINFO => false,
            ResponsePayloadKeys::DIGEST => true,
            ResponsePayloadKeys::DIGEST1 => true,
        ];
        $normalizedValues = [];
        foreach ($keys as $key => $required) {
            if (!array_key_exists($key, $valuesFromGetOrPost)) {
                if (!$required) {
                    $normalizedValues[$key] = null;
                } else {
                    throw new Exceptions\BrokenResponse(
                        'Values to create ' . static::class . " are missing required '{$key}'"
                    );
                }
            } elseif ($key === ResponsePayloadKeys::PRCODE || $key === ResponsePayloadKeys::SRCODE) {
                $normalizedValues[$key] = ToInteger::toInteger($valuesFromGetOrPost[$key]);
            } else {
                $normalizedValues[$key] = ToString::toString($valuesFromGetOrPost[$key]);
            }
        }

        return new static(
            $normalizedValues[ResponsePayloadKeys::OPERATION],
            $normalizedValues[ResponsePayloadKeys::ORDERNUMBER],
            $normalizedValues[ResponsePayloadKeys::MERORDERNUM],
            $normalizedValues[ResponsePayloadKeys::MD],
            $normalizedValues[ResponsePayloadKeys::PRCODE],
            $normalizedValues[ResponsePayloadKeys::SRCODE],
            $normalizedValues[ResponsePayloadKeys::RESULTTEXT],
            $normalizedValues[ResponsePayloadKeys::USERPARAM1],
            $normalizedValues[ResponsePayloadKeys::ADDINFO],
            $normalizedValues[ResponsePayloadKeys::DIGEST],
            $normalizedValues[ResponsePayloadKeys::DIGEST1]
        );
    }

    /** @var array $parametersWithoutDigest */
    private $parametersWithoutDigest;
    /** @var string */
    private $digest;
    /** @var string */
    private $digest1;

    /**
     * @param string $operation
     * @param string $orderNumber
     * @param string $merOrderNum
     * @param string $md
     * @param int $prCode
     * @param int $srCode
     * @param string $resultText
     * @param string $userParam1
     * @param string $addInfo
     * @param string $digest
     * @param string $digest1
     */
    public function __construct(
        string $operation,
        string $orderNumber,
        string $merOrderNum = null,
        string $md = null,
        int $prCode,
        int $srCode,
        string $resultText = null,
        string $userParam1 = null,
        string $addInfo = null,
        string $digest,
        string $digest1
    )
    {
        $this->parametersWithoutDigest[ResponsePayloadKeys::OPERATION] = $operation; // string up to length of 20 (always FINALIZE_ORDER)
        $this->parametersWithoutDigest[ResponsePayloadKeys::ORDERNUMBER] = $orderNumber; // numeric up to length of 15
        if ($merOrderNum !== null) {
            $this->parametersWithoutDigest[ResponsePayloadKeys::MERORDERNUM] = $merOrderNum; // numeric up to length of 30
        }
        if ($md !== null) {
            $this->parametersWithoutDigest[ResponsePayloadKeys::MD] = $md; // string up to length of 255
        }
        $this->parametersWithoutDigest[ResponsePayloadKeys::PRCODE] = $prCode; // numeric
        $this->parametersWithoutDigest[ResponsePayloadKeys::SRCODE] = $srCode; // numeric
        if ($resultText !== null) {
            $this->parametersWithoutDigest[ResponsePayloadKeys::RESULTTEXT] = $resultText; // string up to length of 255
        }
        if ($userParam1 !== null) {
            $this->parametersWithoutDigest[ResponsePayloadKeys::USERPARAM1] = $userParam1; // string up to length of 64
        }
        if ($addInfo !== null) {
            $this->parametersWithoutDigest[ResponsePayloadKeys::ADDINFO] = $addInfo; // long string
        }
        $this->digest = $digest; // string up to length of 2000
        $this->digest1 = $digest1; // string up to length of 2000
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return GpWebPayResponseHasAnError::isErrorCode($this->parametersWithoutDigest[ResponsePayloadKeys::PRCODE]);
    }

    /**
     * @return array
     */
    public function getParametersWithoutDigest(): array
    {
        return $this->parametersWithoutDigest;
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
        return $this->parametersWithoutDigest[ResponsePayloadKeys::SRCODE];
    }

    /**
     * @return int
     */
    public function getPrCode(): int
    {
        return $this->parametersWithoutDigest[ResponsePayloadKeys::PRCODE];
    }

    /**
     * @return string
     */
    public function getResultText(): string
    {
        return $this->parametersWithoutDigest[ResponsePayloadKeys::RESULTTEXT];
    }
}