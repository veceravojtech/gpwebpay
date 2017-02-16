<?php
namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

class Request extends StrictObject
{

    /** @var  Operation $operation */
    private $operation;
    /** @var string $url */
    private $url;
    /** @var int $merchantNumber */
    private $merchantNumber;
    /** @var int $depositFlag */
    private $depositFlag;
    /** @var array $params */
    private $params;

    /**
     * @param Operation $operation
     * @param int $merchantNumber
     * @param string $depositFlag
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     */
    public function __construct(Operation $operation, int $merchantNumber, string $depositFlag)
    {
        if (!$this->url = $operation->getResponseUrl()) {
            throw new Exceptions\InvalidArgumentException('Response URL in Operation must by set!');
        }
        $this->operation = $operation;
        $this->merchantNumber = $merchantNumber;
        $this->depositFlag = $depositFlag;

        $this->populateParams();
    }

    private function populateParams()
    {
        $this->params[RequestPayloadKeys::MERCHANTNUMBER] = $this->merchantNumber;
        $this->params[RequestPayloadKeys::OPERATION] = OperationCodes::CREATE_ORDER;
        $this->params[RequestPayloadKeys::ORDERNUMBER] = $this->operation->getOrderNumber();
        $this->params[RequestPayloadKeys::AMOUNT] = $this->operation->getAmount();
        $this->params[RequestPayloadKeys::CURRENCY] = $this->operation->getCurrency();
        $this->params[RequestPayloadKeys::DEPOSITFLAG] = $this->depositFlag;
        if ($this->operation->getMerchantOrderNumber()) {
            $this->params[RequestPayloadKeys::MERORDERNUM] = $this->operation->getMerchantOrderNumber();
        }
        $this->params[RequestPayloadKeys::URL] = $this->url;

        if ($this->operation->getDescription()) {
            $this->params[RequestPayloadKeys::DESCRIPTION] = $this->operation->getDescription();
        }
        if ($this->operation->getMd()) {
            $this->params[RequestPayloadKeys::MD] = $this->operation->getMd();
        }
        if ($this->operation->getLang()) {
            $this->params[RequestPayloadKeys::LANG] = $this->operation->getLang();
        }
        if ($this->operation->getUserParam1()) {
            $this->params[RequestPayloadKeys::USERPARAM1] = $this->operation->getUserParam1();
        }
        if ($this->operation->getPayMethod()) {
            $this->params[RequestPayloadKeys::PAYMETHOD] = $this->operation->getPayMethod();
        }
        if ($this->operation->getDisablePayMethod()) {
            $this->params[RequestPayloadKeys::DISABLEPAYMETHOD] = $this->operation->getDisablePayMethod();
        }
        if ($this->operation->getPayMethods()) {
            $this->params[RequestPayloadKeys::PAYMETHODS] = $this->operation->getPayMethods();
        }
        if ($this->operation->getEmail()) {
            $this->params[RequestPayloadKeys::EMAIL] = $this->operation->getEmail();
        }
        if ($this->operation->getReferenceNumber()) {
            $this->params[RequestPayloadKeys::REFERENCENUMBER] = $this->operation->getReferenceNumber();
        }
        if ($this->operation->getFastPayId()) {
            $this->params[RequestPayloadKeys::FASTPAYID] = $this->operation->getFastPayId();
        }
    }

    /**
     * @param string $digest
     * @internal
     */
    public function setDigest(string $digest)
    {
        $this->params[RequestPayloadKeys::DIGEST] = $digest;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getDigestParams()
    {
        return array_intersect_key($this->params, array_flip(RequestDigestKeys::getDigestKeys()));
    }

}