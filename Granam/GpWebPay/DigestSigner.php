<?php
namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

class DigestSigner extends StrictObject
{

    /** @var string */
    private $publicKeyPath;
    /** @var string */
    private $privateKeyPath;
    /** @var string */
    private $privateKeyPassword;
    /** @var resource */
    private $privateKeyResource;
    /** @var resource */
    private $publicKeyResource;

    /**
     * @param string $publicKeyPath
     * @param string $privateKeyPath
     * @param string $privateKeyPassword
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     */
    public function __construct(string $publicKeyPath, string $privateKeyPath, string $privateKeyPassword = '')
    {
        $publicKeyPath = trim($publicKeyPath);
        if (!is_readable($publicKeyPath)) {
            throw new Exceptions\PublicKeyFileCanNotBeRead(
                "Public key '{$publicKeyPath}' can not be read. Ensure that it exists and with correct rights."
            );
        }
        $privateKeyPath = trim($privateKeyPath);
        if (!is_readable($privateKeyPath)) {
            throw new Exceptions\PrivateKeyFileCanNotBeRead(
                "Private key '{$privateKeyPath} 'can not be read. Ensure that it exists and with correct rights."
            );
        }

        $this->publicKeyPath = $publicKeyPath;
        $this->privateKeyPath = $privateKeyPath;
        $this->privateKeyPassword = $privateKeyPassword;
    }

    /**
     * @param array|string[] $partsOfDigest
     * @return string Digest as encrypted content of the request for its validation on GpWebPay side
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\CanNotSignDigest
     */
    public function createSignedDigest(array $partsOfDigest)
    {
        $digestText = implode('|', $partsOfDigest);
        if (!openssl_sign($digestText, $digest, $this->getPrivateKeyResource())) {
            throw new Exceptions\CanNotSignDigest('Can not sign ' . $digestText);
        }

        return base64_encode($digest);
    }

    /**
     * @return resource
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     */
    private function getPrivateKeyResource()
    {
        if (is_resource($this->privateKeyResource)) {
            return $this->privateKeyResource;
        }
        $key = file_get_contents($this->privateKeyPath);
        if (!($this->privateKeyResource = openssl_pkey_get_private($key, $this->privateKeyPassword))) {
            $errorMessage = "'{$this->privateKeyPath}' is not valid PEM private key";
            if ($this->privateKeyPassword !== '') {
                $errorMessage = "Password for private key is incorrect (or $errorMessage)";
            }
            throw new Exceptions\PrivateKeyUsageFailed($errorMessage);
        }

        return $this->privateKeyResource;
    }

    /**
     * @param array|string[] $expectedPartsOfDigest
     * @param string $digestToVerify
     * @return bool
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\DigestCanNotBeVerified
     */
    public function verifySignedDigest(string $digestToVerify, array $expectedPartsOfDigest)
    {
        $expectedDigest = implode('|', $expectedPartsOfDigest);
        $digestToVerify = base64_decode($digestToVerify);
        if (openssl_verify($expectedDigest, $digestToVerify, $this->getPublicKeyResource()) !== 1) {
            throw new Exceptions\DigestCanNotBeVerified('Given digest does not match expected ' . $expectedDigest);
        }

        return true;
    }

    /**
     * @return resource
     * @throws \Granam\GpWebPay\Exceptions\PublicKeyUsageFailed
     */
    private function getPublicKeyResource()
    {
        if (is_resource($this->publicKeyResource)) {
            return $this->publicKeyResource;
        }
        $publicKey = file_get_contents($this->publicKeyPath);
        if (!($this->publicKeyResource = openssl_pkey_get_public($publicKey))) {
            throw new Exceptions\PublicKeyUsageFailed("'{$this->publicKeyPath}' is not valid PEM public key.");
        }

        return $this->publicKeyResource;
    }
}