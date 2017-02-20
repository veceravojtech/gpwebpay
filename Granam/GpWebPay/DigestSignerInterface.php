<?php
namespace Granam\GpWebPay;

interface DigestSignerInterface
{
    /**
     * @param array $partsOfDigest
     * @return string
     */
    public function createSignedDigest(array $partsOfDigest): string;

    /**
     * @param string $digestToVerify
     * @param array $expectedPartsOfDigest
     * @return bool
     */
    public function verifySignedDigest(string $digestToVerify, array $expectedPartsOfDigest): bool;
}