<?php
namespace Granam\GpWebPay;

interface SettingsInterface
{
    /**
     * @return string
     */
    public function getRequestBaseUrl(): string;

    /**
     * @return string
     */
    public function getPrivateKeyFile(): string;

    /**
     * @return string
     */
    public function getPrivateKeyPassword(): string;

    /**
     * @return string
     */
    public function getPublicKeyFile(): string;

    /**
     * @return string
     */
    public function getResponseUrl(): string;

    /**
     * @return string
     */
    public function getMerchantNumber(): string;

    /**
     * @return string
     */
    public function getGatewayKey(): string;
}