<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\GpWebPay\Codes\ResponsePayloadKeys;
use Granam\Strict\Object\StrictObject;

class Provider extends StrictObject implements CardPayProvider
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
    public function createCardPayRequest(CardPayRequestValues $requestValues): CardPayRequest
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
     * @throws \Granam\GpWebPay\Exceptions\GpWebPayErrorResponse
     */
    public function createCardPayResponse(array $valuesFromGetOrPost): CardPayResponse
    {
        return CardPayResponse::createFromArray($valuesFromGetOrPost);
    }

    /**
     * @param PayResponse $response
     * @return bool
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\DigestCanNotBeVerified
     * @throws \Granam\GpWebPay\Exceptions\GpWebPayErrorResponse
     * @throws \Granam\GpWebPay\Exceptions\GpWebPayErrorByCustomerResponse
     */
    public function verifyPayResponse(PayResponse $response): bool
    {
        // verify digest & digest1
        $parametersForDigest = $response->getParametersForDigest();
        if (!$this->digestSigner->verifySignedDigest($response->getDigest(), $parametersForDigest)) {
            throw new Exceptions\DigestCanNotBeVerified(
                'Given \'' . ResponsePayloadKeys::DIGEST . '\' does not match expected one calculated from values '
                . var_export($parametersForDigest, true)
            );
        }
        // merchant number is not part of the response to provide additional security
        $parametersForDigest[RequestPayloadKeys::MERCHANTNUMBER] = $this->settings->getMerchantNumber();
        if (!$this->digestSigner->verifySignedDigest($response->getDigest1(), $parametersForDigest)) {
            throw new Exceptions\DigestCanNotBeVerified(
                'Given \'' . ResponsePayloadKeys::DIGEST1 . '\' does not match expected one calculated from values '
                . var_export($parametersForDigest, true)
            );
        }
        if ($response->hasError()) { // verify PRCODE
            if (Exceptions\GpWebPayErrorByCustomerResponse::isErrorCausedByCustomer(
                $response->getPrCode(),
                $response->getSrCode())
            ) {
                throw new Exceptions\GpWebPayErrorByCustomerResponse(
                    $response->getPrCode(),
                    $response->getSrCode(),
                    $response->getResultText());
            }
            throw new Exceptions\GpWebPayErrorResponse($response->getPrCode(),
                $response->getSrCode(),
                $response->getResultText()
            );
        }

        return true;
    }
}