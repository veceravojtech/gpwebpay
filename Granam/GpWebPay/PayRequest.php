<?php
namespace Granam\GpWebPay;

interface PayRequest extends \IteratorAggregate
{
    /**
     * @return string
     */
    public function getRequestUrl(): string;

    /**
     * @return array
     */
    public function getParametersForRequest(): array;
}