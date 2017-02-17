<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\ResponsePayloadKeys;
use Granam\GpWebPay\Exceptions\GpWebPayResponseHasAnError;
use Granam\Strict\Object\StrictObject;

class Response extends StrictObject
{
    /** @var array $params */
    private $params;
    /** @var string */
    private $digest;
    /** @var string */
    private $digest1;
    /** @var string */
    private $gatewayKey;

    /**
     * @param string $operation
     * @param string $orderNumber
     * @param string $merOrderNum
     * @param string $md
     * @param int $prCode
     * @param int $srCode
     * @param string $resultText
     * @param string $digest
     * @param string $digest1
     * @param string $gatewayKey
     */
    public function __construct(
        string $operation,
        string $orderNumber,
        string $merOrderNum = null,
        string $md = null,
        int $prCode,
        int $srCode,
        string $resultText = null,
        string $digest,
        string $digest1,
        string $gatewayKey = null
    )
    {
        $this->params[ResponsePayloadKeys::OPERATION] = $operation;
        $this->params[ResponsePayloadKeys::ORDERNUMBER] = $orderNumber;
        $this->params[ResponsePayloadKeys::MERORDERNUM] = $merOrderNum;
        $this->params[ResponsePayloadKeys::MD] = (string)$md;
        $this->params[ResponsePayloadKeys::PRCODE] = $prCode;
        $this->params[ResponsePayloadKeys::SRCODE] = $srCode;
        $this->params[ResponsePayloadKeys::RESULTTEXT] = (string)$resultText;
        $this->digest = $digest;
        $this->digest1 = $digest1;
        $this->gatewayKey = (string)$gatewayKey;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return GpWebPayResponseHasAnError::isErrorCode($this->params[ResponsePayloadKeys::PRCODE]);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
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
        return $this->params[ResponsePayloadKeys::SRCODE];
    }

    /**
     * @return int
     */
    public function getPrCode(): int
    {
        return $this->params[ResponsePayloadKeys::PRCODE];
    }

    /**
     * @return string
     */
    public function getResultText(): string
    {
        return $this->params[ResponsePayloadKeys::RESULTTEXT];
    }

    /**
     * @return string
     */
    public function getMd(): string
    {
        $explode = explode('|', $this->params[ResponsePayloadKeys::MD], 2);
        if (isset($explode[1])) {
            return $explode[1];
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getGatewayKey(): string
    {
        return $this->gatewayKey;
    }
}