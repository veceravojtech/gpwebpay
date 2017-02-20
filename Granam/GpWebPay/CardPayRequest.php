<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\OperationCodes;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\Strict\Object\StrictObject;

class CardPayRequest extends StrictObject implements \IteratorAggregate
{
    /** @var string */
    private $requestUrl;
    /** @var array */
    private $parametersForRequest;

    /**
     * @param CardPayRequestValues $cardPayRequestValues
     * @param SettingsInterface $settings
     * @param DigestSignerInterface $digestSigner
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\CanNotSignDigest
     */
    public function __construct(
        CardPayRequestValues $cardPayRequestValues,
        SettingsInterface $settings,
        DigestSignerInterface $digestSigner
    )
    {
        $parametersForDigest = $this->buildParametersForDigest($cardPayRequestValues, $settings);
        $this->parametersForRequest = $this->buildParametersForRequest(
            $parametersForDigest,
            $digestSigner,
            $cardPayRequestValues->getLang()
        );
        $this->requestUrl = $settings->getBaseUrlForRequest() . '?' . http_build_query($this->parametersForRequest);
    }

    /**
     * @param CardPayRequestValues $requestValues
     * @param SettingsInterface $settings
     * @return array
     */
    private function buildParametersForDigest(CardPayRequestValues $requestValues, SettingsInterface $settings)
    {
        // parameters HAVE TO be in this order, see GP_webpay_HTTP_EN.pdf / GP_webpay_HTTP.pdf
        $parametersWithoutDigest[RequestPayloadKeys::MERCHANTNUMBER] = $settings->getMerchantNumber();
        $parametersWithoutDigest[RequestPayloadKeys::OPERATION] = OperationCodes::CREATE_ORDER; // the only operation currently available
        $parametersWithoutDigest[RequestPayloadKeys::ORDERNUMBER] = $requestValues->getOrderNumber(); // HAS TO be unique
        $parametersWithoutDigest[RequestPayloadKeys::AMOUNT] = $requestValues->getAmount();
        $parametersWithoutDigest[RequestPayloadKeys::CURRENCY] = $requestValues->getCurrency();
        $parametersWithoutDigest[RequestPayloadKeys::DEPOSITFLAG] = $requestValues->getDepositFlag();
        if ($requestValues->getMerOrderNum()) {
            $parametersWithoutDigest[RequestPayloadKeys::MERORDERNUM] = $requestValues->getMerOrderNum();
        }
        $parametersWithoutDigest[RequestPayloadKeys::URL] = $settings->getUrlForResponse();
        if ($requestValues->getDescription()) {
            $parametersWithoutDigest[RequestPayloadKeys::DESCRIPTION] = $requestValues->getDescription();
        }
        if ($requestValues->getMd()) {
            $parametersWithoutDigest[RequestPayloadKeys::MD] = $requestValues->getMd();
        }
        if ($requestValues->getPayMethod()) {
            $parametersWithoutDigest[RequestPayloadKeys::PAYMETHOD] = $requestValues->getPayMethod();
        }
        if ($requestValues->getDisablePayMethod()) {
            $parametersWithoutDigest[RequestPayloadKeys::DISABLEPAYMETHOD] = $requestValues->getDisablePayMethod();
        }
        if ($requestValues->getPayMethods()) {
            $parametersWithoutDigest[RequestPayloadKeys::PAYMETHODS] = $requestValues->getPayMethods();
        }
        if ($requestValues->getEmail()) {
            $parametersWithoutDigest[RequestPayloadKeys::EMAIL] = $requestValues->getEmail();
        }
        if ($requestValues->getReferenceNumber()) {
            $parametersWithoutDigest[RequestPayloadKeys::REFERENCENUMBER] = $requestValues->getReferenceNumber();
        }
        if ($requestValues->getAddInfo()) {
            $parametersWithoutDigest[RequestPayloadKeys::ADDINFO] = $requestValues->getAddInfo();
        }
        if ($requestValues->getFastPayId()) {
            $parametersWithoutDigest[RequestPayloadKeys::FASTPAYID] = $requestValues->getFastPayId();
        }

        return $parametersWithoutDigest;
    }

    /**
     * @param array $parametersForDigest
     * @param DigestSignerInterface $digestSigner
     * @param string|null $lang
     * @return array
     */
    private function buildParametersForRequest(array $parametersForDigest, DigestSignerInterface $digestSigner, string $lang = null)
    {
        $parametersForRequest = $parametersForDigest;
        // digest HAS TO be calculated after parameters population
        $parametersForRequest[RequestPayloadKeys::DIGEST] = $digestSigner->createSignedDigest($parametersForDigest);
        if ($lang) { // lang IS NOT part of digest
            $parametersForRequest[RequestPayloadKeys::LANG] = $lang;
        }
        return $parametersForRequest;
    }

    /**
     * To send a request via GET method you can use this URL
     *
     * @return string
     */
    public function getRequestUrl(): string
    {
        return $this->requestUrl;
    }

    /**
     * To build request by your own.
     *
     * @return array
     */
    public function getParametersForRequest(): array
    {
        return $this->parametersForRequest;
    }

    /**
     * To easy create a POST request by using values one by one for hidden inputs.
     *
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->getParametersForRequest());
    }

}