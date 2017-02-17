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
     * @param RequestValues $requestValues
     * @param Settings $settings
     * @param DigestSigner $digestSigner
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\CanNotSignDigest
     */
    public function __construct(RequestValues $requestValues, Settings $settings, DigestSigner $digestSigner)
    {
        $this->settings = $settings;

        $this->parameters[RequestPayloadKeys::MERCHANTNUMBER] = $settings->getMerchantNumber();
        $this->parameters[RequestPayloadKeys::OPERATION] = OperationCodes::CREATE_ORDER;
        $this->parameters[RequestPayloadKeys::ORDERNUMBER] = $requestValues->getOrderNumber();
        $this->parameters[RequestPayloadKeys::AMOUNT] = $requestValues->getAmount();
        $this->parameters[RequestPayloadKeys::CURRENCY] = $requestValues->getCurrencyCode();
        $this->parameters[RequestPayloadKeys::DEPOSITFLAG] = $settings->getDepositFlag();
        if ($requestValues->getMerchantOrderNumber()) {
            $this->parameters[RequestPayloadKeys::MERORDERNUM] = $requestValues->getMerchantOrderNumber();
        }
        $this->parameters[RequestPayloadKeys::URL] = $settings->getResponseUrl();
        if ($requestValues->getDescription()) {
            $this->parameters[RequestPayloadKeys::DESCRIPTION] = $requestValues->getDescription();
        }
        if ($requestValues->getMd()) {
            $this->parameters[RequestPayloadKeys::MD] = $requestValues->getMd();
        }
        if ($requestValues->getLang()) {
            $this->parameters[RequestPayloadKeys::LANG] = $requestValues->getLang();
        }
        if ($requestValues->getUserParam1()) {
            $this->parameters[RequestPayloadKeys::USERPARAM1] = $requestValues->getUserParam1();
        }
        if ($requestValues->getPayMethod()) {
            $this->parameters[RequestPayloadKeys::PAYMETHOD] = $requestValues->getPayMethod();
        }
        if ($requestValues->getDisabledPayMethod()) {
            $this->parameters[RequestPayloadKeys::DISABLEPAYMETHOD] = $requestValues->getDisabledPayMethod();
        }
        if ($requestValues->getPayMethods()) {
            $this->parameters[RequestPayloadKeys::PAYMETHODS] = $requestValues->getPayMethods();
        }
        if ($requestValues->getEmail()) {
            $this->parameters[RequestPayloadKeys::EMAIL] = $requestValues->getEmail();
        }
        if ($requestValues->getReferenceNumber()) {
            $this->parameters[RequestPayloadKeys::REFERENCENUMBER] = $requestValues->getReferenceNumber();
        }
        if ($requestValues->getFastPayId()) {
            $this->parameters[RequestPayloadKeys::FASTPAYID] = $requestValues->getFastPayId();
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