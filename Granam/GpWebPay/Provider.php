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
    /** @var RequestParameters $request */
    private $request;
    /** @var DigestSigner $digestSigner */
    private $digestSigner;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param Operation $operation
     * @param DigestSigner $digestSigner
     * @return Provider
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\CanNotSignDigest
     */
    public function createRequest(Operation $operation, DigestSigner $digestSigner)
    {
        $this->request = new RequestParameters(
            $operation,
            $this->settings->getMerchantNumber(),
            $this->settings->getDepositFlag(),
            $digestSigner
        );

        $this->digestSigner = $digestSigner;

        return $this;
    }

    /**
     * @return RequestParameters
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return DigestSigner
     */
    public function getDigestSigner()
    {
        return $this->digestSigner;
    }

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        $paymentUrl = $this->settings->getUrl() . '?' . http_build_query($this->request->getParameters());

        return $paymentUrl;
    }

    /**
     * @param array $params
     * @return Response
     * @throws \Granam\Scalar\Tools\Exceptions\WrongParameterType
     * @throws \Granam\Integer\Tools\Exceptions\WrongParameterType
     */
    public function createResponse(array $params)
    {
        $operation = $params[ResponsePayloadKeys::OPERATION] ?? '';
        $orderNumber = $params[ResponsePayloadKeys::ORDERNUMBER] ?? '';
        $merOrderNum = $params[ResponsePayloadKeys::MERORDERNUM] ?? '';
        $md = $params[ResponsePayloadKeys::MD] ?? '';
        $prCode = $params[ResponsePayloadKeys::PRCODE] ?? '';
        $srCode = $params[ResponsePayloadKeys::SRCODE] ?? '';
        $resultText = $params[ResponsePayloadKeys::RESULTTEXT] ?? '';
        $digest = $params[ResponsePayloadKeys::DIGEST] ?? '';
        $digest1 = $params[ResponsePayloadKeys::DIGEST1] ?? '';
        $key = explode('|', $md, 2);
        if (empty($key[0])) {
            $gatewayKey = $this->settings->getDefaultGatewayKey();
        } else {
            $gatewayKey = $key[0];
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Response(
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
     * @param Response $response
     * @return bool
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\DigestCanNotBeVerified
     * @throws \Granam\GpWebPay\Exceptions\GpWebPayResponseHasAnError
     */
    public function verifyPaymentResponse(Response $response)
    {
        // verify digest & digest1
        $this->digestSigner = new DigestSigner(
            $this->settings->getPrivateKey(),
            $this->settings->getPrivateKeyPassword(),
            $this->settings->getPublicKey()
        );

        $responseParams = $response->getParams();
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