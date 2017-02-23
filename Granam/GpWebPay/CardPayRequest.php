<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\OperationCodes;
use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\Strict\Object\StrictObject;

class CardPayRequest extends StrictObject implements \IteratorAggregate, PayRequest
{
    /** @var string */
    private $requestUrlForPost;
    /** @var string */
    private $requestUrlForGet;
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
        $this->requestUrlForPost = $settings->getBaseUrlForRequest();
        $this->requestUrlForGet = $settings->getBaseUrlForRequest() . '?' . http_build_query($this->parametersForRequest);
    }

    /**
     * @param CardPayRequestValues $requestValues
     * @param SettingsInterface $settings
     * @return array
     */
    private function buildParametersForDigest(CardPayRequestValues $requestValues, SettingsInterface $settings)
    {
        // parameters HAVE TO be in this order, see GP_webpay_HTTP_EN.pdf / GP_webpay_HTTP.pdf
        $parametersWithoutDigest[RequestDigestKeys::MERCHANTNUMBER] = $settings->getMerchantNumber();
        $parametersWithoutDigest[RequestDigestKeys::OPERATION] = OperationCodes::CREATE_ORDER; // the only operation currently available
        $parametersWithoutDigest[RequestDigestKeys::ORDERNUMBER] = $requestValues->getOrderNumber(); // HAS TO be unique
        $parametersWithoutDigest[RequestDigestKeys::AMOUNT] = $requestValues->getAmount();
        $parametersWithoutDigest[RequestDigestKeys::CURRENCY] = $requestValues->getCurrency();
        $parametersWithoutDigest[RequestDigestKeys::DEPOSITFLAG] = $requestValues->getDepositFlag();
        if ($requestValues->getMerOrderNum() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::MERORDERNUM] = $requestValues->getMerOrderNum();
        }
        $parametersWithoutDigest[RequestDigestKeys::URL] = $settings->getUrlForResponse();
        if ($requestValues->getDescription() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::DESCRIPTION] = $requestValues->getDescription();
        }
        if ($requestValues->getMd() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::MD] = $requestValues->getMd();
        }
        if ($requestValues->getPayMethod() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::PAYMETHOD] = $requestValues->getPayMethod();
        }
        if ($requestValues->getDisablePayMethod() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::DISABLEPAYMETHOD] = $requestValues->getDisablePayMethod();
        }
        if ($requestValues->getPayMethods() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::PAYMETHODS] = $requestValues->getPayMethods();
        }
        if ($requestValues->getEmail() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::EMAIL] = $requestValues->getEmail();
        }
        if ($requestValues->getReferenceNumber() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::REFERENCENUMBER] = $requestValues->getReferenceNumber();
        }
        if ($requestValues->getAddInfo() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::ADDINFO] = $requestValues->getAddInfo();
        }
        if ($requestValues->getFastPayId() !== null) {
            $parametersWithoutDigest[RequestDigestKeys::FASTPAYID] = $requestValues->getFastPayId();
        }

        return $parametersWithoutDigest;
    }

    /**
     * @param array $parametersForDigest
     * @param DigestSignerInterface $digestSigner
     * @param string|null $lang
     * @return array
     */
    private function buildParametersForRequest(
        array $parametersForDigest,
        DigestSignerInterface $digestSigner,
        string $lang = null
    )
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
     * @return string
     */
    public function getRequestUrlForPost(): string
    {
        return $this->requestUrlForPost;
    }

    /**
     * To send a request via GET method you can use this URL
     *
     * @return string
     */
    public function getRequestUrlForGet(): string
    {
        return $this->requestUrlForGet;
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