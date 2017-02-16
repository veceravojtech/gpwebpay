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
     * @var array $digestParamsKeys
     */
    // TODO differs from DigestKeys
    private $digestParamsKeys = [
        DigestKeys::MERCHANTNUMBER,
        DigestKeys::OPERATION,
        DigestKeys::ORDERNUMBER,
        DigestKeys::AMOUNT,
        DigestKeys::CURRENCY,
        DigestKeys::DEPOSITFLAG,
        DigestKeys::MERORDERNUM,
        DigestKeys::URL,
        DigestKeys::DESCRIPTION,
        DigestKeys::MD,
        DigestKeys::USERPARAM1,
        DigestKeys::FASTPAYID,
        DigestKeys::PAYMETHOD,
        DigestKeys::DISABLEPAYMETHOD,
        DigestKeys::PAYMETHODS,
        DigestKeys::EMAIL,
        DigestKeys::REFERENCENUMBER,
        DigestKeys::ADDINFO,
    ];

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
        $this->params[DigestKeys::MERCHANTNUMBER] = $this->merchantNumber;
        $this->params[DigestKeys::OPERATION] = OperationCodes::CREATE_ORDER;
        $this->params[DigestKeys::ORDERNUMBER] = $this->operation->getOrderNumber();
        $this->params[DigestKeys::AMOUNT] = $this->operation->getAmount();
        $this->params[DigestKeys::CURRENCY] = $this->operation->getCurrency();
        $this->params[DigestKeys::DEPOSITFLAG] = $this->depositFlag;
        if ($this->operation->getMerchantOrderNumber()) {
            $this->params[DigestKeys::MERORDERNUM] = $this->operation->getMerchantOrderNumber();
        }
        $this->params[DigestKeys::URL] = $this->url;

        if ($this->operation->getDescription()) {
            $this->params[DigestKeys::DESCRIPTION] = $this->operation->getDescription();
        }
        if ($this->operation->getMd()) {
            $this->params[DigestKeys::MD] = $this->operation->getMd();
        }
        if ($this->operation->getLang()) {
            $this->params[PayloadKeys::LANG] = $this->operation->getLang();
        }
        if ($this->operation->getUserParam1()) {
            $this->params[DigestKeys::USERPARAM1] = $this->operation->getUserParam1();
        }
        if ($this->operation->getPayMethod()) {
            $this->params[DigestKeys::PAYMETHOD] = $this->operation->getPayMethod();
        }
        if ($this->operation->getDisablePayMethod()) {
            $this->params[DigestKeys::DISABLEPAYMETHOD] = $this->operation->getDisablePayMethod();
        }
        if ($this->operation->getPayMethods()) {
            $this->params[DigestKeys::PAYMETHODS] = $this->operation->getPayMethods();
        }
        if ($this->operation->getEmail()) {
            $this->params[DigestKeys::EMAIL] = $this->operation->getEmail();
        }
        if ($this->operation->getReferenceNumber()) {
            $this->params[DigestKeys::REFERENCENUMBER] = $this->operation->getReferenceNumber();
        }
        if ($this->operation->getFastPayId()) {
            $this->params[DigestKeys::FASTPAYID] = $this->operation->getFastPayId();
        }
    }

    /**
     * @param string $digest
     * @internal
     */
    public function setDigest(string $digest)
    {
        $this->params[DigestKeys::DIGEST] = $digest;
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
        return array_intersect_key($this->params, array_flip($this->digestParamsKeys));
    }

}