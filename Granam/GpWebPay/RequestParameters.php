<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\OperationCodes;
use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\Strict\Object\StrictObject;

class RequestParameters extends StrictObject
{
    /** @var array $parameters */
    private $parameters;

    /**
     * @param Operation $operation
     * @param int $merchantNumber
     * @param string $depositFlag
     * @param DigestSigner $digestSigner
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\CanNotSignDigest
     */
    public function __construct(Operation $operation, int $merchantNumber, string $depositFlag, DigestSigner $digestSigner)
    {
        $this->populateParameters($operation, $merchantNumber, $depositFlag, $digestSigner);
    }

    /**
     * @param Operation $operation
     * @param int $merchantNumber
     * @param string $depositFlag
     * @param DigestSigner $digestSign
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\CanNotSignDigest
     */
    private function populateParameters(Operation $operation, int $merchantNumber, string $depositFlag, DigestSigner $digestSigner)
    {
        $this->parameters[RequestPayloadKeys::MERCHANTNUMBER] = $merchantNumber;
        $this->parameters[RequestPayloadKeys::OPERATION] = OperationCodes::CREATE_ORDER;
        $this->parameters[RequestPayloadKeys::ORDERNUMBER] = $operation->getOrderNumber();
        $this->parameters[RequestPayloadKeys::AMOUNT] = $operation->getAmount();
        $this->parameters[RequestPayloadKeys::CURRENCY] = $operation->getCurrency();
        $this->parameters[RequestPayloadKeys::DEPOSITFLAG] = $depositFlag;
        if ($operation->getMerchantOrderNumber()) {
            $this->parameters[RequestPayloadKeys::MERORDERNUM] = $operation->getMerchantOrderNumber();
        }
        // TODO response URL from settings instead
        if (!$operation->getResponseUrl()) {
            throw new Exceptions\InvalidArgumentException('Response URL in Operation must by set!');
        }
        $this->parameters[RequestPayloadKeys::URL] = $operation->getResponseUrl();

        if ($operation->getDescription()) {
            $this->parameters[RequestPayloadKeys::DESCRIPTION] = $operation->getDescription();
        }
        if ($operation->getMd()) {
            $this->parameters[RequestPayloadKeys::MD] = $operation->getMd();
        }
        if ($operation->getLang()) {
            $this->parameters[RequestPayloadKeys::LANG] = $operation->getLang();
        }
        if ($operation->getUserParam1()) {
            $this->parameters[RequestPayloadKeys::USERPARAM1] = $operation->getUserParam1();
        }
        if ($operation->getPayMethod()) {
            $this->parameters[RequestPayloadKeys::PAYMETHOD] = $operation->getPayMethod();
        }
        if ($operation->getDisablePayMethod()) {
            $this->parameters[RequestPayloadKeys::DISABLEPAYMETHOD] = $operation->getDisablePayMethod();
        }
        if ($operation->getPayMethods()) {
            $this->parameters[RequestPayloadKeys::PAYMETHODS] = $operation->getPayMethods();
        }
        if ($operation->getEmail()) {
            $this->parameters[RequestPayloadKeys::EMAIL] = $operation->getEmail();
        }
        if ($operation->getReferenceNumber()) {
            $this->parameters[RequestPayloadKeys::REFERENCENUMBER] = $operation->getReferenceNumber();
        }
        if ($operation->getFastPayId()) {
            $this->parameters[RequestPayloadKeys::FASTPAYID] = $operation->getFastPayId();
        }
        // has to be at the very end after all other params populated
        $this->parameters[RequestPayloadKeys::DIGEST] = $digestSigner->createSignedDigest(
            $this->filterDigestParameters($this->parameters)
        );
    }

    /**
     * @param array
     * @return array
     */
    private function filterDigestParameters(array $params)
    {
        return array_intersect_key($params, array_flip(RequestDigestKeys::getDigestKeys()));
    }

    /**
     * Gives parameters for a request including signed digest
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

}