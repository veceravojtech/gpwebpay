<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\OperationCodes;
use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\Strict\Object\StrictObject;

class Request extends StrictObject
{
    /** @var array $parameters */
    private $parameters;
    /** @var Settings */
    private $settings;

    /**
     * @param Operation $operation
     * @param Settings $settings
     * @param DigestSigner $digestSigner
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\CanNotSignDigest
     */
    public function __construct(Operation $operation, Settings $settings, DigestSigner $digestSigner)
    {
        $this->settings = $settings;

        $this->parameters[RequestPayloadKeys::MERCHANTNUMBER] = $settings->getMerchantNumber();
        $this->parameters[RequestPayloadKeys::OPERATION] = OperationCodes::CREATE_ORDER;
        $this->parameters[RequestPayloadKeys::ORDERNUMBER] = $operation->getOrderNumber();
        $this->parameters[RequestPayloadKeys::AMOUNT] = $operation->getAmount();
        $this->parameters[RequestPayloadKeys::CURRENCY] = $operation->getCurrency();
        $this->parameters[RequestPayloadKeys::DEPOSITFLAG] = $settings->getDepositFlag();
        if ($operation->getMerchantOrderNumber()) {
            $this->parameters[RequestPayloadKeys::MERORDERNUM] = $operation->getMerchantOrderNumber();
        }
        $this->parameters[RequestPayloadKeys::URL] = $settings->getResponseUrl();
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
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->settings->getResponseUrl() . '?' . http_build_query($this->parameters);
    }

}