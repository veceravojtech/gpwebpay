<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\RequestDigestKeys;
use Granam\Strict\Object\StrictObject;

class Settings extends StrictObject
{
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
    /** @var int */
    private $depositFlag;
    /** @var string */
    private $gatewayKey;

    /**
     * @param string $privateKeyFile
     * @param string $privateKeyPassword
     * @param string $publicKeyFile
     * @param string $responseUrl
     * @param string $merchantNumber
     * @param int $depositFlag
     * @param string $gatewayKey
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\InvalidUrl
     * @throws \Granam\GpWebPay\Exceptions\ValueTooLong
     */
    public function __construct(
        string $privateKeyFile,
        string $privateKeyPassword,
        string $publicKeyFile,
        string $responseUrl,
        string $merchantNumber,
        int $depositFlag,
        string $gatewayKey
    )
    {
        $this->setPrivateKeyFile($privateKeyFile);
        $this->setPrivateKeyPassword($privateKeyPassword);
        $this->setPublicKeyFile($publicKeyFile);
        $this->setResponseUrl($responseUrl);
        $this->merchantNumber = $merchantNumber;
        $this->depositFlag = $depositFlag;
        $this->gatewayKey = trim($gatewayKey);
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
     * @return int
     */
    public function getDepositFlag(): int
    {
        return $this->depositFlag;
    }

    /**
     * @return string
     */
    public function getGatewayKey(): string
    {
        return $this->gatewayKey;
    }
}