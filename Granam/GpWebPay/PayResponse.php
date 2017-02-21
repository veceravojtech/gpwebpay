<?php
namespace Granam\GpWebPay;

interface PayResponse
{
    /**
     * @return bool
     */
    public function hasError(): bool;

    /**
     * @return array
     */
    public function getParametersForDigest(): array;

    /**
     * @return string
     */
    public function getDigest(): string;

    /**
     * @return string
     */
    public function getDigest1(): string;

    /**
     * @return int
     */
    public function getSrCode(): int;

    /**
     * @return int
     */
    public function getPrCode(): int;

    /**
     * @return string|null
     */
    public function getResultText();
}