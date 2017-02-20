<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\Strict\Object\StrictObject;

class Settings extends StrictObject
{
    const PRODUCTION_REQUEST_URL = 'https://3dsecure.gpwebpay.com/pgw/order.do';
    const TEST_REQUEST_URL = 'https://test.3dsecure.gpwebpay.com/pgw/order.do';

    /**
     * @param string $privateKeyFile
     * @param string $privateKeyPassword
     * @param string $publicKeyFile
     * @param string $responseUrl
     * @param string $merchantNumber
     * @param string $gatewayKey
     * @return Settings
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\InvalidUrl
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    public static function createForProduction(
        string $privateKeyFile,
        string $privateKeyPassword,
        string $publicKeyFile,
        string $responseUrl,
        string $merchantNumber,
        string $gatewayKey
    )
    {
        return new static(
            self::PRODUCTION_REQUEST_URL,
            $privateKeyFile,
            $privateKeyPassword,
            $publicKeyFile,
            $responseUrl,
            $merchantNumber,
            $gatewayKey
        );
    }

    /**
     * @param string $privateKeyFile
     * @param string $privateKeyPassword
     * @param string $publicKeyFile
     * @param string $responseUrl
     * @param string $merchantNumber
     * @param string $gatewayKey
     * @return Settings
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\InvalidUrl
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    public static function createForTest(
        string $privateKeyFile,
        string $privateKeyPassword,
        string $publicKeyFile,
        string $responseUrl,
        string $merchantNumber,
        string $gatewayKey
    )
    {
        return new static(
            self::TEST_REQUEST_URL,
            $privateKeyFile,
            $privateKeyPassword,
            $publicKeyFile,
            $responseUrl,
            $merchantNumber,
            $gatewayKey
        );
    }

    /** @var string */
    private $requestUrl;
    /** @var string */
    private $privateKeyFile;
    /** @var string */
    private $privateKeyPassword;
    /** @var string */
    private $publicKeyFile;
    /** @var string */
    private $responseUrl;
    /** @var string */
    private $merchantNumber;
    /** @var string */
    private $gatewayKey;

    /**
     * @param string $requestUrl
     * @param string $privateKeyFile
     * @param string $privateKeyPassword
     * @param string $publicKeyFile
     * @param string $responseUrl
     * @param string $merchantNumber
     * @param string $gatewayKey
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\InvalidUrl
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    public function __construct(
        string $requestUrl,
        string $privateKeyFile,
        string $privateKeyPassword,
        string $publicKeyFile,
        string $responseUrl,
        string $merchantNumber,
        string $gatewayKey
    )
    {
        $this->setRequestUrl($requestUrl);
        $this->setPrivateKeyFile($privateKeyFile);
        $this->setPrivateKeyPassword($privateKeyPassword);
        $this->setPublicKeyFile($publicKeyFile);
        $this->setResponseUrl($responseUrl);
        $this->merchantNumber = $merchantNumber;
        $this->gatewayKey = trim($gatewayKey);
    }

    /**
     * @param string $requestUrl
     * @throws \Granam\GpWebPay\Exceptions\InvalidUrl
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    private function setRequestUrl(string $requestUrl)
    {
        $requestUrl = trim($requestUrl);
        if (!filter_var($requestUrl, FILTER_VALIDATE_URL)) {
            throw new Exceptions\InvalidUrl("Given URL for request is not valid: '{$requestUrl}'");
        }

        $this->requestUrl = $requestUrl;
    }

    /**
     * @param string $privateKeyFile
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     */
    private function setPrivateKeyFile(string $privateKeyFile)
    {
        $privateKeyFile = trim($privateKeyFile);
        if (!is_readable($privateKeyFile)) {
            throw new Exceptions\PrivateKeyFileCanNotBeRead(
                "Private key '{$privateKeyFile} 'can not be read. Ensure that it exists and with correct rights."
            );
        }
        $this->privateKeyFile = $privateKeyFile;
    }

    /**
     * @param string $privateKeyPassword
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     */
    private function setPrivateKeyPassword(string $privateKeyPassword)
    {
        if (!openssl_pkey_get_private(file_get_contents($this->privateKeyFile), $privateKeyPassword)) {
            $errorMessage = "'{$this->privateKeyFile}' is not valid PEM private key";
            if ($privateKeyPassword !== '') {
                $errorMessage = "Password for private key is incorrect (or $errorMessage)";
            }
            throw new Exceptions\PrivateKeyUsageFailed($errorMessage);
        }
        $this->privateKeyPassword = $privateKeyPassword;
    }

    /**
     * @param string $publicKeyFile
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     */
    private function setPublicKeyFile(string $publicKeyFile)
    {
        $publicKeyFile = trim($publicKeyFile);
        if (!is_readable($publicKeyFile)) {
            throw new Exceptions\PublicKeyFileCanNotBeRead(
                "Public key '{$publicKeyFile}' can not be read. Ensure that it exists and with correct rights."
            );
        }
        $this->publicKeyFile = $publicKeyFile;
    }

    const MAXIMAL_LENGTH_OF_URL = 300;

    /**
     * @param string $responseUrl with maximal length of 300 characters
     * @throws \Granam\GpWebPay\Exceptions\InvalidUrl
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    private function setResponseUrl(string $responseUrl)
    {
        $responseUrl = trim($responseUrl);
        if (!filter_var($responseUrl, FILTER_VALIDATE_URL)) {
            throw new Exceptions\InvalidUrl('Given ' . RequestDigestKeys::URL . " is not valid: '{$responseUrl}'");
        }
        if (strlen($responseUrl) > self::MAXIMAL_LENGTH_OF_URL) {
            throw new Exceptions\ValueTooLong(
                "Maximal length of '" . RequestDigestKeys::URL . '\' is ' . self::MAXIMAL_LENGTH_OF_URL
                . ', got one with length of ' . strlen($responseUrl) . " and value '{$responseUrl}'"
            );
        }

        $this->responseUrl = $responseUrl;
    }

    /**
     * @return string
     */
    public function getRequestUrl(): string
    {
        return $this->requestUrl;
    }

    /**
     * @return string
     */
    public function getPrivateKeyFile(): string
    {
        return $this->privateKeyFile;
    }

    /**
     * @return string
     */
    public function getPrivateKeyPassword(): string
    {
        return $this->privateKeyPassword;
    }

    /**
     * @return string
     */
    public function getPublicKeyFile(): string
    {
        return $this->publicKeyFile;
    }

    /**
     * @return string
     */
    public function getResponseUrl(): string
    {
        return $this->responseUrl;
    }

    /**
     * @return string
     */
    public function getMerchantNumber(): string
    {
        return $this->merchantNumber;
    }

    /**
     * @return string
     */
    public function getGatewayKey(): string
    {
        return $this->gatewayKey;
    }
}