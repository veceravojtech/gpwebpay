<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\OperationCodes;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\Strict\Object\StrictObject;

class Request extends StrictObject
{
    /** @var array $parametersWithoutDigest */
    private $parametersWithoutDigest;
    /** @var Settings */
    private $settings;
    /** @var string */
    private $digest;

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

        // parameters HAVE TO be in this order, see GP_webpay_HTTP_EN.pdf / GP_webpay_HTTP.pdf
        $this->parametersWithoutDigest[RequestPayloadKeys::MERCHANTNUMBER] = $settings->getMerchantNumber();
        $this->parametersWithoutDigest[RequestPayloadKeys::OPERATION] = OperationCodes::CREATE_ORDER; // the only operation currently available
        $this->parametersWithoutDigest[RequestPayloadKeys::ORDERNUMBER] = $requestValues->getOrderNumber();
        $this->parametersWithoutDigest[RequestPayloadKeys::AMOUNT] = $requestValues->getAmount();
        $this->parametersWithoutDigest[RequestPayloadKeys::CURRENCY] = $requestValues->getCurrencyCode();
        $this->parametersWithoutDigest[RequestPayloadKeys::DEPOSITFLAG] = $settings->getDepositFlag();
        if ($requestValues->getMerchantOrderNumber()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::MERORDERNUM] = $requestValues->getMerchantOrderNumber();
        }
        $this->parametersWithoutDigest[RequestPayloadKeys::URL] = $settings->getResponseUrl();
        if ($requestValues->getDescription()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::DESCRIPTION] = $requestValues->getDescription();
        }
        if ($requestValues->getMd()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::MD] = $requestValues->getMd();
        }
        if ($requestValues->getUserParam1()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::USERPARAM1] = $requestValues->getUserParam1();
        }
        if ($requestValues->getLang()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::LANG] = $requestValues->getLang();
        }
        if ($requestValues->getPayMethod()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::PAYMETHOD] = $requestValues->getPayMethod();
        }
        if ($requestValues->getDisabledPayMethod()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::DISABLEPAYMETHOD] = $requestValues->getDisabledPayMethod();
        }
        if ($requestValues->getPayMethods()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::PAYMETHODS] = $requestValues->getPayMethods();
        }
        if ($requestValues->getEmail()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::EMAIL] = $requestValues->getEmail();
        }
        if ($requestValues->getReferenceNumber()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::REFERENCENUMBER] = $requestValues->getReferenceNumber();
        }
        if ($requestValues->getFastPayId()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::FASTPAYID] = $requestValues->getFastPayId();
        }
        // HAS TO be at the very end after all other parameters already populated
        $this->digest = $digestSigner->createSignedDigest($this->parametersWithoutDigest);
    }

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        $parameters = $this->parametersWithoutDigest;
        $parameters[RequestPayloadKeys::DIGEST] = $this->digest;

        return $this->settings->getResponseUrl() . '?' . http_build_query($parameters);
    }

}