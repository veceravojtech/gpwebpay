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
        string $merOrderNum,
        string $md,
        int $prCode,
        int $srCode,
        string $resultText,
        string $digest,
        string $digest1,
        string $gatewayKey
    )
    {
        $this->params[ResponsePayloadKeys::OPERATION] = $operation;
        $this->params[ResponsePayloadKeys::ORDERNUMBER] = $orderNumber;
        if ($merOrderNum !== null) {
            $this->params[ResponsePayloadKeys::MERORDERNUM] = $merOrderNum;
        }
        if ($md !== null) {
            $this->params[ResponsePayloadKeys::MD] = $md;
        }
        $this->params[ResponsePayloadKeys::PRCODE] = $prCode;
        $this->params[ResponsePayloadKeys::SRCODE] = $srCode;
        $this->params[ResponsePayloadKeys::RESULTTEXT] = $resultText;
        $this->digest = $digest;
        $this->digest1 = $digest1;
        $this->gatewayKey = $gatewayKey;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getDigest()
    {
        return $this->digest;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return GpWebPayResponseHasAnError::isErrorCode($this->params[ResponsePayloadKeys::PRCODE]);
    }

    /**
     * @return string
     */
    public function getDigest1()
    {
        return $this->digest1;
    }

    /**
     * @return string|null
     */
    public function getMerOrderNumber()
    {
        if (isset($this->params[ResponsePayloadKeys::MERORDERNUM])) {
            return $this->params[ResponsePayloadKeys::MERORDERNUM];
        }

        return null;
    }

    /**
     * @return string| null
     */
    public function getMd()
    {
        $explode = explode('|', $this->params[ResponsePayloadKeys::MD], 2);
        if (isset($explode[1])) {
            return $explode[1];
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getGatewayKey()
    {
        return $this->gatewayKey;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->params[ResponsePayloadKeys::ORDERNUMBER];
    }

    /**
     * @return int
     */
    public function getSrCode()
    {
        return $this->params[ResponsePayloadKeys::SRCODE];
    }

    /**
     * @return int
     */
    public function getPrCode()
    {
        return $this->params[ResponsePayloadKeys::PRCODE];
    }

    /**
     * @return string|null
     */
    public function getResultText()
    {
        return $this->params[ResponsePayloadKeys::RESULTTEXT];
    }
}