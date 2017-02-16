<?php
namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

class Settings extends StrictObject
{
    /** @var string */
    private $privateKey;
    /** @var string */
    private $privateKeyPassword;
    /** @var string */
    private $publicKey;
    /** @var string */
    private $url;
    /** @var string */
    private $merchantNumber;
    /** @var int */
    private $depositFlag;
    /** @var string */
    private $defaultGatewayKey;

    /**
     * @param string $privateKey
     * @param string $privateKeyPassword
     * @param string $publicKey
     * @param string $url
     * @param string $merchantNumber
     * @param int $depositFlag
     * @param string $gatewayKey
     */
    public function __construct(
        string $privateKey,
        string $privateKeyPassword,
        string $publicKey,
        string $url,
        string $merchantNumber,
        int $depositFlag,
        string $gatewayKey
    )
    {
        $this->privateKey = $privateKey;
        $this->privateKeyPassword = $privateKeyPassword;
        $this->publicKey = $publicKey;
        $this->url = $url;
        $this->merchantNumber = $merchantNumber;
        $this->depositFlag = $depositFlag;
        $this->defaultGatewayKey = $gatewayKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
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
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
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
    public function getDefaultGatewayKey(): string
    {
        return $this->defaultGatewayKey;
    }
}