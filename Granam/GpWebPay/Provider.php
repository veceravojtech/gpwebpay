<?php
namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

class Provider extends StrictObject
{

    /** @var Settings $settings */
    private $settings;
    /** @var Request $request */
    private $request;
    /** @var Signer $signer */
    private $signer;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param Operation $operation
     * @return Provider
     */
    public function createRequest(Operation $operation)
    {
        $this->request = new Request(
            $operation,
            $this->settings->getMerchantNumber(),
            $this->settings->getDepositFlag()
        );

        $this->signer = new Signer(
            $this->settings->getPrivateKey(),
            $this->settings->getPrivateKeyPassword(),
            $this->settings->getPublicKey()
        );

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Signer
     */
    public function getSigner()
    {
        return $this->signer;
    }

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        $this->request->setDigest($this->signer->sign($this->request->getDigestParams()));
        $paymentUrl = $this->settings->getUrl() . '?' . http_build_query($this->request->getParams());

        return $paymentUrl;
    }

    /**
     * @param $params
     * @return Response
     */
    public function createResponse($params)
    {
        $operation = isset ($params [ResponsePayloadKeys::OPERATION]) ? $params [ResponsePayloadKeys::OPERATION] : '';
        $ordernumber = isset ($params [ResponsePayloadKeys::ORDERNUMBER]) ? $params [ResponsePayloadKeys::ORDERNUMBER] : '';
        $merordernum = isset ($params [ResponsePayloadKeys::MERORDERNUM]) ? $params [ResponsePayloadKeys::MERORDERNUM] : null;
        $md = isset ($params [ResponsePayloadKeys::MD]) ? $params[ResponsePayloadKeys::MD] : null;
        $prcode = isset ($params [ResponsePayloadKeys::PRCODE]) ? $params [ResponsePayloadKeys::PRCODE] : '';
        $srcode = isset ($params [ResponsePayloadKeys::SRCODE]) ? $params [ResponsePayloadKeys::SRCODE] : '';
        $resulttext = isset ($params [ResponsePayloadKeys::RESULTTEXT]) ? $params [ResponsePayloadKeys::RESULTTEXT] : '';
        $digest = isset ($params [ResponsePayloadKeys::DIGEST]) ? $params [ResponsePayloadKeys::DIGEST] : '';
        $digest1 = isset ($params [ResponsePayloadKeys::DIGEST1]) ? $params [ResponsePayloadKeys::DIGEST1] : '';

        $key = explode('|', $md, 2);

        if (empty($key[0])) {
            $gatewayKey = $this->settings->getDefaultGatewayKey();
        } else {
            $gatewayKey = $key[0];
        }

        return new Response($operation, $ordernumber, $merordernum, $md, $prcode, $srcode, $resulttext, $digest,
            $digest1, $gatewayKey);
    }

    /**
     * @param Response $response
     * @return bool
     * @throws \Granam\GpWebPay\Exceptions\GPWebPayResultException
     */
    public function verifyPaymentResponse(Response $response)
    {
        // verify digest & digest1
        $this->signer = new Signer(
            $this->settings->getPrivateKey(),
            $this->settings->getPrivateKeyPassword(),
            $this->settings->getPublicKey()
        );

        $responseParams = $response->getParams();
        $this->signer->verify($responseParams, $response->getDigest());
        $responseParams[RequestDigestKeys::MERCHANTNUMBER] = $this->settings->getMerchantNumber();
        $this->signer->verify($responseParams, $response->getDigest1());
        // verify PRCODE and SRCODE
        if (false !== $response->hasError()) {
            throw new Exceptions\GPWebPayResultException(
                'Response has an error.',
                $response->getPrCode(),
                $response->getSrCode(),
                $response->getResultText()
            );
        }

        return true;
    }
}