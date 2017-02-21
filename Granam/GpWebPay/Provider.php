<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\Strict\Object\StrictObject;

class Provider extends StrictObject
{

    /** @var SettingsInterface $settings */
    private $settings;
    /** @var DigestSigner $digestSigner */
    private $digestSigner;

    /**
     * @param SettingsInterface $settings
     * @param DigestSignerInterface $digestSigner
     */
    public function __construct(SettingsInterface $settings, DigestSignerInterface $digestSigner)
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
     * @param array $valuesFromGetOrPost
     * @return CardPayResponse
     * @throws \Granam\GpWebPay\Exceptions\BrokenResponse
     * @throws \Granam\Integer\Tools\Exceptions\WrongParameterType
     * @throws \Granam\Integer\Tools\Exceptions\ValueLostOnCast
     * @throws \Granam\Scalar\Tools\Exceptions\WrongParameterType
     */
    public function createResponse(array $valuesFromGetOrPost)
    {
        return CardPayResponse::createFromArray($valuesFromGetOrPost);
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
        $responseParams = $response->getParametersForDigest();
        $this->digestSigner->verifySignedDigest($response->getDigest(), $responseParams);
        // merchant number is not part of the response to provide additional security
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