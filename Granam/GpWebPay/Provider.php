<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\GpWebPay\Codes\ResponsePayloadKeys;
use Granam\Integer\Tools\ToInteger;
use Granam\Scalar\Tools\ToString;
use Granam\Strict\Object\StrictObject;

class Provider extends StrictObject
{

    /** @var Settings $settings */
    private $settings;
    /** @var DigestSigner $digestSigner */
    private $digestSigner;

    /**
     * @param Settings $settings
     * @param DigestSigner $digestSigner
     */
    public function __construct(Settings $settings, DigestSigner $digestSigner)
    {
        $this->settings = $settings;
        $this->digestSigner = $digestSigner;
    }

    /**
     * @param CardPayRequestValues $requestValues
     * @return CardPayRequest
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\CanNotSignDigest
     */
    public function createRequest(CardPayRequestValues $requestValues)
    {
        return new CardPayRequest($requestValues, $this->settings, $this->digestSigner);
    }

    /**
     * @param array $params
     * @return CardPayResponse
     * @throws \Granam\Scalar\Tools\Exceptions\WrongParameterType
     * @throws \Granam\Integer\Tools\Exceptions\WrongParameterType
     */
    public function createResponse(array $params)
    {
        $operation = $params[ResponsePayloadKeys::OPERATION];
        $orderNumber = $params[ResponsePayloadKeys::ORDERNUMBER];
        $merOrderNum = $params[ResponsePayloadKeys::MERORDERNUM] ?? '';
        $md = $params[ResponsePayloadKeys::MD] ?? '';
        $prCode = $params[ResponsePayloadKeys::PRCODE];
        $srCode = $params[ResponsePayloadKeys::SRCODE] ?? 0;
        $resultText = $params[ResponsePayloadKeys::RESULTTEXT] ?? '';
        $digest = $params[ResponsePayloadKeys::DIGEST];
        $digest1 = $params[ResponsePayloadKeys::DIGEST1];
        $key = explode('|', $md, 2);
        if (empty($key[0])) {
            $gatewayKey = $this->settings->getGatewayKey();
        } else {
            $gatewayKey = $key[0];
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new CardPayResponse(
            ToString::toString($operation),
            ToString::toString($orderNumber),
            ToString::toString($merOrderNum),
            ToString::toString($md),
            ToInteger::toInteger($prCode),
            ToInteger::toInteger($srCode),
            ToString::toString($resultText),
            ToString::toString($digest),
            ToString::toString($digest1),
            ToString::toString($gatewayKey)
        );
    }

    /**
     * @param CardPayResponse $response
     * @return bool
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\DigestCanNotBeVerified
     * @throws \Granam\GpWebPay\Exceptions\GpWebPayResponseHasAnError
     */
    public function verifyResponse(CardPayResponse $response)
    {
        // verify digest & digest1
        $responseParams = $response->getParametersWithoutDigest();
        $this->digestSigner->verifySignedDigest($response->getDigest(), $responseParams);
        $responseParams[RequestPayloadKeys::MERCHANTNUMBER] = $this->settings->getMerchantNumber();
        $this->digestSigner->verifySignedDigest($response->getDigest1(), $responseParams);
        if ($response->hasError()) { // verify PRCODE
            throw new Exceptions\GpWebPayResponseHasAnError(
                $response->getPrCode(),
                $response->getSrCode(),
                $response->getResultText()
            );
        }

        return true;
    }
}