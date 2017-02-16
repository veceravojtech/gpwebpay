<?php
namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

class Signer extends StrictObject
{

    /** @var string */
    private $privateKeyPath;
    /** @var resource */
    private $privateKeyResource;
    /** @var string */
    private $privateKeyPassword;
    /** @var string */
    private $publicKeyPath;
    /** @var resource */
    private $publicKeyResource;

    /**
     * @param string $publicKeyPath
     * @param string $privateKeyPath
     * @param string $privateKeyPassword
     * @throws \Granam\GpWebPay\Exceptions\SignerException
     */
    public function __construct(string $publicKeyPath, string $privateKeyPath, string $privateKeyPassword = '')
    {
        $privateKeyPath = trim($privateKeyPath);
        if (!is_readable($privateKeyPath)) {
            throw new Exceptions\SignerException(
                "Private key '{$privateKeyPath} 'can not be read. Ensure that it exists and with correct rights."
            );
        }

        $publicKeyPath = trim($publicKeyPath);
        if (!is_readable($publicKeyPath)) {
            throw new Exceptions\SignerException(
                "Public key '{$publicKeyPath}' can not be read. Ensure that it exists and with correct rights."
            );
        }

        $this->privateKeyPath = $privateKeyPath;
        $this->privateKeyPassword = $privateKeyPassword;
        $this->publicKeyPath = $publicKeyPath;
    }

    /**
     * @return resource
     * @throws \Granam\GpWebPay\Exceptions\SignerException
     */
    private function getPrivateKeyResource()
    {
        if (is_resource($this->privateKeyResource)) {
            return $this->privateKeyResource;
        }
        $key = file_get_contents($this->privateKeyPath);
        if (!($this->privateKeyResource = openssl_pkey_get_private($key, $this->privateKeyPassword))) {
            throw new Exceptions\SignerException(
                "'{$this->privateKeyPath}' is not valid PEM private key or the password is incorrect."
            );
        }

        return $this->privateKeyResource;
    }

    public function __destruct()
    {
        if (is_resource($this->privateKeyResource)) {
            fclose($this->privateKeyResource);
        }
        if (is_resource($this->publicKeyResource)) {
            fclose($this->publicKeyResource);
        }
    }

    /**
     * @param array|string[] $partsOfDigest
     * @return string Digest as encrypted content of the request for its validation on GpWebPay side
     * @throws \Granam\GpWebPay\Exceptions\SignerException
     */
    public function sign(array $partsOfDigest)
    {
        $digestText = implode('|', $partsOfDigest);
        if (!openssl_sign($digestText, $digest, $this->getPrivateKeyResource())) {
            throw new Exceptions\SignerException('Could not sign ' . $digestText);
        }

        return base64_encode($digest);
    }

    /**
     * @param array|string[] $expectedPartsOfDigest
     * @param string $digest
     * @return bool
     * @throws Exceptions\SignerException
     */
    public function verify(array $expectedPartsOfDigest, string $digest)
    {
        $expectedDigest = implode('|', $expectedPartsOfDigest);
        $digest = base64_decode($digest);
        if (openssl_verify($expectedDigest, $digest, $this->getPublicKeyResource()) !== 1) {
            throw new Exceptions\SignerException('Digest does not match expected ' . $expectedDigest);
        }

        return true;
    }

    /**
     * @return resource
     * @throws \Granam\GpWebPay\Exceptions\SignerException
     */
    private function getPublicKeyResource()
    {
        if (is_resource($this->publicKeyResource)) {
            return $this->publicKeyResource;
        }
        if (!($fp = fopen($this->publicKeyPath, 'rb'))) {
            throw new Exceptions\SignerException("Could not open '{$this->publicKeyPath}' for reading.");
        }
        $publicKey = fread($fp, filesize($this->publicKeyPath));
        fclose($fp);
        if (!($this->publicKeyResource = openssl_pkey_get_public($publicKey))) {
            throw new Exceptions\SignerException("'{$this->publicKeyPath}' is not valid PEM public key.");
        }

        return $this->publicKeyResource;
    }
}